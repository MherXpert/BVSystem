<?php 
// DATABASE CONNECTION
// Ensure your db.php sets the charset correctly upon connection
include("../app/db.php"); 

// --- CONSTANTS AND SETTINGS ---
define("COLUMN_HEADER_ROW_INDEX", "1");
define("BULK_INSERT_SIZE", 1000);

// Set higher limits for large file processing
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);
set_time_limit(0);

function checkIfExists($hh_id, $entry_id) {
    global $conn;
    $check_if_exists = "SELECT 1 FROM `barcode_data_2` WHERE `hh_id` = ? AND `entry_id` = ? LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $check_if_exists);
    mysqli_stmt_bind_param($stmt, "ss", $hh_id, $entry_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    
    return $exists;
}

// --- HELPER FUNCTIONS ---
// (Your helper functions like Run, checkIfExists, processBatch, updateRecords can remain here if you use them, but the main logic below doesn't use them directly)

// --- FILE UPLOAD AND PROCESSING LOGIC ---
if (isset($_FILES['report']) && $_FILES['report']['error'] == UPLOAD_ERR_OK) 
{
    // 1. Set PHP's internal encoding to UTF-8
    mb_internal_encoding('UTF-8');

    // 2. Set response header to UTF-8
    header('Content-Type: text/html; charset=utf-8');

    // 3. Ensure database connection is consistently UTF-8
    // This should ideally be in your db.php, but it's good to be explicit here too.
    if ($conn->get_charset()->charset !== 'utf8mb4') {
        $conn->set_charset("utf8mb4");
    }
    
    // 4. Validate and sanitize uploaded file content
    $file_path = $_FILES['report']['tmp_name'];
    $fileContent = file_get_contents($file_path);
    
    // Check for Byte Order Mark (BOM) and remove it, as it can cause issues
    if (substr($fileContent, 0, 3) === "\xEF\xBB\xBF") {
        $fileContent = substr($fileContent, 3);
        file_put_contents($file_path, $fileContent); // Save back the content without BOM
    }
    
    // Convert to UTF-8 if it's not already
    if (!mb_check_encoding($fileContent, 'UTF-8')) {
        // 'auto' can be unreliable; common encodings from Excel are Windows-1252 or ISO-8859-1
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'Windows-1252'); 
        file_put_contents($file_path, $fileContent);
    }

    // --- CSV HEADER VALIDATION ---
    $expected_headers = [
        "id", "province", "municipality", "first_name", "middle_name", 
        "last_name", "name_extension", "relation_to_hh_head", "sex", 
        "birthday", "age", "member_status", "barangay", "client_status", 
        "hh_id", "entry_id"
    ];
    
    $file = new SplFileObject($file_path);
    $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD);
    
    $file->seek(COLUMN_HEADER_ROW_INDEX - 1);
    $csvHeaders = array_map('trim', $file->current()); // Trim whitespace from headers
    
    // A more robust check for headers
    if (count($csvHeaders) !== count($expected_headers) || !empty(array_diff($expected_headers, $csvHeaders))) {
        echo "<h2>CSV Header Mismatch!</h2>";
        echo "<p>Please ensure the CSV columns are in the exact expected order and named correctly.</p>";
        echo "<b>Expected:</b> " . htmlspecialchars(implode(', ', $expected_headers)) . "<br>";
        echo "<b>Found:</b> " . htmlspecialchars(implode(', ', $csvHeaders)) . "<br>";
        exit;
    }

    // --- DATABASE PREPARED STATEMENTS ---
    $insertStmt = $conn->prepare("INSERT INTO barcode_data_2 (province, municipality, first_name, middle_name, last_name, name_extension, relation_to_hh_head, sex, birthday, age, member_status, barangay, client_status, hh_id, entry_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $updateStmt = $conn->prepare("UPDATE barcode_data_2 SET province = ?, municipality = ?, first_name = ?, middle_name = ?, last_name = ?, name_extension = ?, relation_to_hh_head = ?, sex = ?, birthday = ?, age = ?, member_status = ?, barangay = ?, client_status = ? WHERE hh_id = ? AND entry_id = ?");
    
    if (!$insertStmt || !$updateStmt) {
        die("Error preparing statements: " . $conn->error);
    }
    
    $insertStmt->bind_param("sssssssssssssss", $province, $municipality, $first_name, $middle_name, $last_name, $name_extension, $relation_to_hh_head, $sex, $birthday, $age, $member_status, $barangay, $client_status, $hh_id, $entry_id);
    $updateStmt->bind_param("sssssssssssssss", $province, $municipality, $first_name, $middle_name, $last_name, $name_extension, $relation_to_hh_head, $sex, $birthday, $age, $member_status, $barangay, $client_status, $hh_id, $entry_id);
    
    // --- TRANSACTION PROCESSING ---
    $processed = 0;
    $file->seek(COLUMN_HEADER_ROW_INDEX); // Skip header
    $conn->begin_transaction();
    
    try {
        while (!$file->eof()) {
            $row = $file->fgetcsv(); // Use fgetcsv for better memory handling
            
            if (empty($row) || $row[0] === null) continue; // Skip truly empty rows

            // Assign values to bound parameters (indexes match CSV columns)
            $province = $row[1];
            $municipality = $row[2];
            $first_name = $row[3];
            $middle_name = $row[4];
            $last_name = $row[5];
            $name_extension = $row[6];
            $relation_to_hh_head = $row[7];
            $sex = $row[8];
            $birthday_input = $row[9];
                    try {
                            // First, try to create DateTime object directly
                            $date = new DateTime($birthday_input);
                            $birthday = $date->format('Y-m-d');
                        } catch (Exception $e) {
                            // If initial parse fails, try to clean and reformat the date
                            $cleaned_date = preg_replace('/[^0-9\/\-\.]/', '', $birthday_input); // Remove non-date characters
                            
                            // Try common date format fixes
                            $formats_to_try = [
                                'm/d/Y', 'd/m/Y', 'Y-m-d', 'm-d-Y', 'd-m-Y',
                                'm.d.Y', 'd.m.Y', 'Y.m.d', 'Y/m/d'
                            ];
                            
                            foreach ($formats_to_try as $format) {
                                $date = DateTime::createFromFormat($format, $cleaned_date);
                                if ($date !== false) {
                                    $birthday = $date->format('Y-m-d');
                                    break;
                                }
                            }
                            
                            // If all attempts fail, use default
                            if (!isset($birthday)) {
                                $birthday = '0000-00-00'; // Or null
                                error_log("Invalid date format for record (after correction attempts): " . print_r($row, true));
                            }
                    }
            $age = $row[10];
            $member_status = $row[11];
            $barangay = $row[12];
            $client_status = $row[13];
            $hh_id = $row[14];
            $entry_id = $row[15];


            // Check if record exists and execute the appropriate statement
            if (checkIfExists($hh_id, $entry_id)) {
                $updateStmt->execute();
            } else {
                $insertStmt->execute();
            }
            
            $processed++;
            
            // Commit in batches for performance and feedback
            if ($processed % BULK_INSERT_SIZE == 0) {
                $conn->commit();
                echo "Processed $processed records...<br>";
                ob_flush();
                flush();
                $conn->begin_transaction();
            }
        }
        
        $conn->commit(); // Commit any remaining records
        echo "<h3>Success!</h3> <p>Processed a total of $processed records.</p>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "An error occurred: " . htmlspecialchars($e->getMessage());
    }
    
    $insertStmt->close();
    $updateStmt->close();
    $conn->close();

} else 
{
echo'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CSV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-center">
                        <h4>Upload Beneficiary Data CSV</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="report" class="form-label">Select CSV File</label>
                                <input class="form-control" type="file" name="report" id="report" accept=".csv" required>
                            </div>
                            <div class="d-grid gap-2 col-6 mx-auto">
                                    <button class="btn btn-outline-success mt-2" type="submit">Upload file</button> 
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <a href="admin_dashboard.php" class="btn btn-warning">Back</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
}
?>