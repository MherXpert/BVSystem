<?php 
// DATABASE CONNECTION
include("db.php");
define("COLUMN_HEADER_ROW_INDEX","1"); //exact order of column from CSV
define("BULK_INSERT_SIZE", 1000); // Number of records to insert in each batch

// Set higher limits for large file processing
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 0); // 0 means no time limit
set_time_limit(0);

function Run($query) {
    global $conn;
    return mysqli_query($conn, $query);
}

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

function processBatch($batch) {
    global $conn;
    
    if (empty($batch)) return;
    
    // Prepare the base query
    $query = "INSERT INTO `barcode_data_2` (`province`, `municipality`, `first_name`, `middle_name`, `last_name`, `name_extension`, `relation_to_hh_head`, `sex`, `birthday`, `age`, `member_status`, `barangay`, `client_status`, `hh_id`, `entry_id`) VALUES ";
    
    // Add placeholders for each record
    $placeholders = rtrim(str_repeat('(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?), ', count($batch)), ', ');
    $query .= $placeholders;
    
    // Flatten the batch array for binding
    $values = [];
    foreach ($batch as $record) {
        $values = array_merge($values, array_values($record));
    }
    
    // Prepare and execute the statement
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn));
        return false;
    }
    
    // Dynamically bind parameters
    $types = str_repeat('s', count($values)); // All parameters are treated as strings
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        error_log("Batch insert failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_close($stmt);
    return $result;
}

function updateRecords($updates) {
    global $conn;
    
    if (empty($updates)) return;
    
    // Start transaction for better performance
    mysqli_begin_transaction($conn);
    
    try {
        // Prepare the update statement
        $stmt = mysqli_prepare($conn, "UPDATE `barcode_data_2` SET 
            `province`=?, `municipality`=?, `first_name`=?, `middle_name`=?, `last_name`=?, 
            `name_extension`=?, `relation_to_hh_head`=?, `sex`=?, `birthday`=?, `age`=?, 
            `member_status`=?, `barangay`=?, `client_status`=? 
            WHERE `hh_id`=? AND `entry_id`=?");
        
        foreach ($updates as $record) {
            mysqli_stmt_bind_param($stmt, "sssssssssssssss", 
                $record['province'], $record['municipality'], $record['first_name'], 
                $record['middle_name'], $record['last_name'], $record['name_extension'], 
                $record['relation_to_hh_head'], $record['sex'], $record['birthday'], 
                $record['age'], $record['member_status'], $record['barangay'], 
                $record['client_status'], $record['hh_id'], $record['entry_id']);
            
            mysqli_stmt_execute($stmt);
        }
        
        mysqli_commit($conn);
        mysqli_stmt_close($stmt);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Update failed: " . $e->getMessage());
        return false;
    }
}


// handling special characters
if (isset($_FILES['report'])) 
{
    // 1. Set UTF-8 encoding at multiple levels
    header('Content-Type: text/html; charset=utf-8');
    
    // 2. Ensure database connection uses UTF-8
    mysqli_set_charset($conn, "utf8mb4");
    mysqli_query($conn, "SET NAMES 'utf8mb4'");
    mysqli_query($conn, "SET CHARACTER SET utf8mb4");
    mysqli_query($conn, "SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");
    
    $row_no = COLUMN_HEADER_ROW_INDEX;
    $dec_row_no = $row_no - 1;
    $header_array = array
    (
        "id", "province", "municipality", "first_name", "middle_name", 
        "last_name", "name_extension", "relation_to_hh_head", "sex", 
        "birthday", "age", "member_status", "barangay", "client_status", 
        "hh_id", "entry_id"
    );

    // 4. Handle the file with proper encoding
    $fileContent = file_get_contents($_FILES['report']['tmp_name']);
    if (!mb_check_encoding($fileContent, 'UTF-8')) {
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'auto');
        file_put_contents($_FILES['report']['tmp_name'], $fileContent);
    }
    
    // Open the file for reading
    $file = new SplFileObject($_FILES['report']['tmp_name']);
    $file->setFlags(SplFileObject::READ_CSV);
    
    // Get the header row and validate
    $file->seek($dec_row_no);
    $csvHeaders = $file->current();
    
    $diff = array_diff($csvHeaders, $header_array);
    
    if (count($diff) > 1 || count($csvHeaders) != 16) {
        echo "<h2>Check these Columns: </h2>";
        echo "<h4>Column numbers</h4>";            
        foreach($diff as $key=>$value) {
            echo $key + 1 . " ";
        }
        echo "<h4>Column Names</h4>";  
        foreach($diff as $diffs) {
            echo "<p>" . htmlspecialchars($diffs, ENT_QUOTES, 'UTF-8') . "</p>";
        }
        exit;
    }
    
    // Prepare statements for inserts and updates
    $insertStmt = $conn->prepare("INSERT INTO barcode_data_2 
        (province, municipality, first_name, middle_name, last_name, 
        name_extension, relation_to_hh_head, sex, birthday, age, 
        member_status, barangay, client_status, hh_id, entry_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $updateStmt = $conn->prepare("UPDATE barcode_data_2 SET 
        province = ?, municipality = ?, first_name = ?, middle_name = ?, 
        last_name = ?, name_extension = ?, relation_to_hh_head = ?, 
        sex = ?, birthday = ?, age = ?, member_status = ?, barangay = ?, 
        client_status = ? 
        WHERE hh_id = ? AND entry_id = ?");
    
    // Bind parameters for both statements
    $insertStmt->bind_param("sssssssssssssss", 
        $province, $municipality, $first_name, $middle_name, $last_name, 
        $name_extension, $relation_to_hh_head, $sex, $birthday, $age, 
        $member_status, $barangay, $client_status, $hh_id, $entry_id);
    
    $updateStmt->bind_param("sssssssssssssss", 
        $province, $municipality, $first_name, $middle_name, $last_name, 
        $name_extension, $relation_to_hh_head, $sex, $birthday, $age, 
        $member_status, $barangay, $client_status, $hh_id, $entry_id);
    
    // Prepare batches for transaction processing
    $processed = 0;
    
    // Skip header row
    $file->seek($row_no);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        while (!$file->eof()) {
            $row = $file->current();
            
            // Skip empty rows
            if (empty($row) || count($row) < 16) {
                $file->next();
                continue;
            }
            
            // Assign values to bound parameters
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
            
            if (checkIfExists($hh_id, $entry_id)) {
                $updateStmt->execute();
            } else {
                $insertStmt->execute();
            }
            
            $processed++;
            if ($processed % 10000 == 0) {
                // Commit current batch and start new transaction
                $conn->commit();
                $conn->begin_transaction();
                
                // Flush output buffer to show progress
                ob_flush();
                flush();
                echo "Processed $processed records...\n";
            }
            
            $file->next();
        }
        
        // Commit any remaining records
        $conn->commit();
        
        echo "success. Processed $processed records.";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
    
    // Close statements
    $insertStmt->close();
    $updateStmt->close();
}else {
    // Display the upload form (unchanged from your original code)
    echo'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CSV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./resources/css/bootstrap-min.css">
    <link rel="stylesheet" href="./resources/css/datatables-min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col"></div>
            <div class="col-lg-10">
                <form method="post" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <label for="report" class="col-sm-3 col-form-label">Upload CSV File</label>
                        <div class="col-sm-9">
                            <input class="form-control" type="file" name="report" id="report" required>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button class="btn btn-primary mt-2" type="submit">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
            <div class="col"></div>
        </div>
    </div>
    <script src="./resources/js/jquery-363.js"></script>
    <script src="./resources/js/bootstrap-bundle-min.js"></script>
    <script src="./resources/js/jquery-validate-min.js"></script>
    <script src="./resources/js/additional-methods-min.js"></script>
    <script src="./resources/js/bootstrap-notify-min.js"></script>
    <script src="./resources/js/notify-script.js"></script>
    <script src="./resources/js/datatables-min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>';
}