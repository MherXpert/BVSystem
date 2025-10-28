<?php
include("../app/db.php"); 

// --- CONSTANTS AND SETTINGS ---
define("COLUMN_HEADER_ROW_INDEX", "1");
define("BULK_INSERT_SIZE", 1000);

// Set higher limits for large file processing
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0);
set_time_limit(0);

// Function to format hh_id from scientific notation
function formatHhId($hh_id) {
    if (empty($hh_id)) return $hh_id;
    
    // If it's in scientific notation or a float, convert it
    if (is_numeric($hh_id) && (strpos((string)$hh_id, 'E') !== false || strpos((string)$hh_id, 'e') !== false || strpos((string)$hh_id, '.') !== false)) {
        // Convert to string without scientific notation
        $number = number_format((float)$hh_id, 0, '', '');
        
        // Ensure it has exactly 18 digits (pad with zeros if needed)
        $number = str_pad($number, 18, '0', STR_PAD_LEFT);
        
        // Format as: 9 digits - 1 digit - 8 digits
        if (strlen($number) === 18) {
            return substr($number, 0, 9) . '-' . substr($number, 9, 1) . '-' . substr($number, 10, 8);
        }
        
        return $number;
    }
    
    // If it's already a string with the correct format, return as is
    if (is_string($hh_id) && preg_match('/^\d{9}-\d-\d{8}$/', $hh_id)) {
        return $hh_id;
    }
    
    // If it's a string without dashes but with 18 digits, format it
    if (is_string($hh_id) && preg_match('/^\d{18}$/', $hh_id)) {
        return substr($hh_id, 0, 9) . '-' . substr($hh_id, 9, 1) . '-' . substr($hh_id, 10, 8);
    }
    
    return $hh_id;
}

// UPDATED: Check if record exists using entry_id (which should be unique)
function checkIfExists($entry_id)
{
    global $conn;
    
    if (empty($entry_id)) return false;
    
    $check_if_exists = "SELECT 1 FROM `barcode_data_2` WHERE `entry_id` = ? LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $check_if_exists);
    mysqli_stmt_bind_param($stmt, "s", $entry_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    
    return $exists;
}

// --- FILE UPLOAD AND PROCESSING LOGIC ---
if (isset($_FILES['report']) && $_FILES['report']['error'] == UPLOAD_ERR_OK) 
{
    // 1. Set PHP's internal encoding to UTF-8
    mb_internal_encoding('UTF-8');

    // 2. Set response header to UTF-8
    header('Content-Type: text/html; charset=utf-8');

    // 3. Ensure database connection is consistently UTF-8
    if ($conn->get_charset()->charset !== 'utf8mb4') {
        $conn->set_charset("utf8mb4");
    }
    
    // 4. Validate and sanitize uploaded file content
    $file_path = $_FILES['report']['tmp_name'];
    $fileContent = file_get_contents($file_path);
    
    // Check for Byte Order Mark (BOM) and remove it
    if (substr($fileContent, 0, 3) === "\xEF\xBB\xBF") {
        $fileContent = substr($fileContent, 3);
        file_put_contents($file_path, $fileContent);
    }
    
    // Convert to UTF-8 if it's not already
    if (!mb_check_encoding($fileContent, 'UTF-8')) {
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'Windows-1252'); 
        file_put_contents($file_path, $fileContent);
    }

    // --- CSV HEADER VALIDATION ---
    $expected_headers = [
        "id", "province", "municipality", "first_name", "middle_name", 
        "last_name", "name_extension", "relation_to_hh_head", "sex", 
        "birthday", "age", "member_status", "barangay", "client_status", 
        "hh_id", "hh_grantee", "hh_set_group", "entry_id"
    ];
    
    // Use fopen/fgetcsv instead of SplFileObject for better reliability
    $file = fopen($file_path, 'r');
    if (!$file) {
        die("Cannot open uploaded file.");
    }
    
    // Read and validate headers
    $headerRow = 0;
    $csvHeaders = [];
    while ($headerRow < COLUMN_HEADER_ROW_INDEX) {
        $csvHeaders = fgetcsv($file);
        $headerRow++;
        if ($csvHeaders === false) break;
    }
    
    $csvHeaders = array_map('trim', $csvHeaders);
    
    // Header validation - now expecting 18 columns (added entry_id)
    if (count($csvHeaders) !== count($expected_headers) || !empty(array_diff($expected_headers, $csvHeaders))) 
    {
        fclose($file);
        echo "<h2>CSV Header Mismatch!</h2>";
        echo "<p>Please ensure the CSV columns are in the exact expected order and named correctly.</p>";
        echo "<b>Expected:</b> " . htmlspecialchars(implode(', ', $expected_headers)) . "<br>";
        echo "<b>Found:</b> " . htmlspecialchars(implode(', ', $csvHeaders)) . "<br>";
        exit;
    }   

    // --- DATABASE PREPARED STATEMENTS ---
    // UPDATED: INSERT statement - using entry_id as unique identifier
    $insertStmt = $conn->prepare("INSERT INTO barcode_data_2 (province, municipality, first_name, middle_name, last_name, name_extension, relation_to_hh_head, sex, birthday, age, member_status, barangay, client_status, hh_id, hh_grantee, hh_set_group, entry_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // UPDATED: UPDATE statement - using entry_id as the WHERE condition
    $updateStmt = $conn->prepare("UPDATE barcode_data_2 SET province = ?, municipality = ?, first_name = ?, middle_name = ?, last_name = ?, name_extension = ?, relation_to_hh_head = ?, sex = ?, birthday = ?, age = ?, member_status = ?, barangay = ?, client_status = ?, hh_id = ?, hh_grantee = ?, hh_set_group = ? WHERE entry_id = ?");
    
    if (!$insertStmt || !$updateStmt) {
        fclose($file);
        die("Error preparing statements: " . $conn->error);
    }
    
    // Bind parameters for INSERT (17 parameters)
    $insertStmt->bind_param("sssssssssssssssss", $province, $municipality, $first_name, $middle_name, $last_name, $name_extension, $relation_to_hh_head, $sex, $birthday, $age, $member_status, $barangay, $client_status, $hh_id, $hh_grantee, $hh_set_group, $entry_id);
    
    // UPDATED: Bind parameters for UPDATE (17 parameters - entry_id is last for WHERE clause)
    $updateStmt->bind_param("sssssssssssssssss", $province, $municipality, $first_name, $middle_name, $last_name, $name_extension, $relation_to_hh_head, $sex, $birthday, $age, $member_status, $barangay, $client_status, $hh_id, $hh_grantee, $hh_set_group, $entry_id);
    
    // --- TRANSACTION PROCESSING ---
    $processed = 0;
    $batchCount = 0;
    $inserted = 0;
    $updated = 0;
    $skipped = 0;
    
    // Track household statistics for debugging
    $householdStats = [];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Read CSV line by line using fgetcsv (more reliable)
        while (($row = fgetcsv($file)) !== false) 
        {
            // Skip empty rows - now expecting 18 columns (added entry_id)
            if ($row === null || count($row) < 18 || empty($row[0])) {
                continue;
            }
            
            // Skip header row if it appears again
            if (isset($row[0]) && $row[0] === 'id') {
                continue;
            }

            // Assign values to bound parameters
            $province = $row[1] ?? '';
            $municipality = $row[2] ?? '';
            $first_name = $row[3] ?? '';
            $middle_name = $row[4] ?? '';
            $last_name = $row[5] ?? '';
            $name_extension = $row[6] ?? '';
            $relation_to_hh_head = $row[7] ?? '';
            $sex = $row[8] ?? '';
            $birthday_input = $row[9] ?? '';
            
            // Date parsing
            $birthday = '0000-00-00';
            if (!empty($birthday_input)) {
                try {
                    $cleaned_date = preg_replace('/[^0-9\/\-\.]/', '', $birthday_input);
                    $formats_to_try = ['Y-m-d', 'm/d/Y', 'd/m/Y', 'm-d-Y', 'd-m-Y'];
                    
                    foreach ($formats_to_try as $format) {
                        $date = DateTime::createFromFormat($format, $cleaned_date);
                        if ($date !== false) {
                            $birthday = $date->format('Y-m-d');
                            break;
                        }
                    }
                } catch (Exception $e) {
                    // Keep default date on error
                }
            }
            
            $age = $row[10] ?? '';
            $member_status = $row[11] ?? '';
            $barangay = $row[12] ?? '';
            $client_status = $row[13] ?? '';
            $hh_id_raw = $row[14] ?? '';
            $hh_grantee = $row[15] ?? '';
            $hh_set_group = $row[16] ?? '';
            $entry_id = $row[17] ?? '';  // Get entry_id from CSV

            // Format hh_id
            $hh_id = formatHhId($hh_id_raw);

            // Skip if essential fields are empty
            if (empty($hh_id) || empty($entry_id)) {
                error_log("Skipping row - missing hh_id or entry_id: hh_id='$hh_id_raw', entry_id='$entry_id'");
                $skipped++;
                continue;
            }

            // Track household statistics for debugging
            if (!isset($householdStats[$hh_id])) {
                $householdStats[$hh_id] = 0;
            }
            $householdStats[$hh_id]++;

            // DEBUG: Log processing details
            if ($processed < 10) {
                error_log("Processing: HH_ID=$hh_id, Entry_ID=$entry_id, Name=$first_name $last_name, Grantee=$hh_grantee");
            }

            // UPDATED: Check if record exists using entry_id (which should be unique)
            $exists = checkIfExists($entry_id);
            
            if ($exists) {
                // Update existing record using entry_id
                $result = $updateStmt->execute();
                if ($result) {
                    $updated++;
                    error_log("UPDATED: Entry_ID=$entry_id, HH_ID=$hh_id");
                } else {
                    error_log("Update failed for entry_id: $entry_id - " . $updateStmt->error);
                }
            } else {
                // Insert new record with entry_id
                $result = $insertStmt->execute();
                if ($result) {
                    $inserted++;
                    error_log("INSERTED: Entry_ID=$entry_id, HH_ID=$hh_id");
                } else {
                    error_log("Insert failed for entry_id: $entry_id - " . $insertStmt->error);
                }
            }
            
            $processed++;
            $batchCount++;
            
            // Debug output every 50 records
            if ($processed % 50 === 0) {
                error_log("Processed $processed records (Inserted: $inserted, Updated: $updated, Skipped: $skipped)");
                echo "Processed $processed records...<br>";
                ob_flush();
                flush();
            }
            
            // Commit in batches for better performance
            if ($batchCount >= BULK_INSERT_SIZE) {
                $conn->commit();
                echo "Committed batch of $batchCount records...<br>";
                ob_flush();
                flush();
                $conn->begin_transaction();
                $batchCount = 0;
            }
        }
        
        // Commit any remaining records
        $conn->commit();
        fclose($file);
        
        // Final statistics
        echo "<h3>Processing Complete!</h3>";
        echo "<div class='alert alert-info'>";
        echo "<p><strong>Total records processed:</strong> $processed</p>";
        echo "<p><strong>Records inserted:</strong> $inserted</p>";
        echo "<p><strong>Records updated:</strong> $updated</p>";
        echo "<p><strong>Records skipped:</strong> $skipped</p>";
        echo "<p><strong>Unique households processed:</strong> " . count($householdStats) . "</p>";
        echo "</div>";
        
        // Show household distribution for debugging
        if (count($householdStats) > 0) {
            echo "<h4>Household Member Distribution:</h4>";
            echo "<div style='max-height: 300px; overflow-y: auto;'>";
            foreach ($householdStats as $hh_id => $count) {
                echo "<p>Household <strong>$hh_id</strong>: $count members</p>";
            }
            echo "</div>";
        }
        
        echo "<script>setTimeout(function(){ window.location.href = 'admin_dashboard.php'; }, 10000);</script>";
        
    } catch (Exception $e) {
        $conn->rollback();
        fclose($file);
        echo "<div class='alert alert-danger'>";
        echo "<h3>An error occurred during processing:</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>Processed $processed records before error.</p>";
        echo "</div>";
        error_log("Processing error: " . $e->getMessage());
    }
    
    $insertStmt->close();
    $updateStmt->close();
    $conn->close();

} else 
{
    // Display upload form
    echo '
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
                                    <div class="form-text">
                                        <strong>CSV must contain these columns:</strong> id, province, municipality, first_name, middle_name, last_name, name_extension, relation_to_hh_head, sex, birthday, age, member_status, barangay, client_status, hh_id, hh_grantee, hh_set_group, entry_id
                                    </div>
                                </div>
                                <div class="d-grid gap-2 col-6 mx-auto">
                                    <button class="btn btn-outline-success mt-2" type="submit">Upload file</button>
                                    <a href="admin_dashboard.php" class="btn btn-outline-primary">Back</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>';
}
?>