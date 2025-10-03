<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "barcode") or die("Connection failed: " . mysqli_connect_error());

// Check if we're showing all grantees
$show_all_grantees = isset($_GET['show_all_grantees']) && $_GET['show_all_grantees'] == '1';

// Fetch data based on the request type
$rows = [];
$search_ids = [];
$all_grantees = [];

if ($show_all_grantees) {
    // Fetch ALL records where hh_grantee = 'YES'
    $stmt = $conn->prepare("SELECT * FROM barcode_data_2 WHERE hh_grantee = 'YES' ORDER BY hh_id, last_name, first_name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $all_grantees[] = $row;
    }
    $stmt->close();
    
} elseif (!empty($_GET['hh_ids'])) {
    $hh_ids_input = trim($_GET['hh_ids']);
    $search_ids = array_map('trim', explode(',', $hh_ids_input));
    $search_ids = array_slice($search_ids, 0, 5); // Limit to 5 IDs
    
    if (!empty($search_ids)) {
        // Create placeholders for prepared statement
        $placeholders = str_repeat('?,', count($search_ids) - 1) . '?';
        
        // Modified query to only select records where hh_grantee = 'YES'
        $stmt = $conn->prepare("SELECT * FROM barcode_data_2 WHERE hh_id IN ($placeholders) AND hh_grantee = 'YES'");
        
        // Bind parameters dynamically
        $types = str_repeat('s', count($search_ids));
        $stmt->bind_param($types, ...$search_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        
        // Get ALL records for the search IDs to show proper counts
        $all_records = [];
        $stmt_all = $conn->prepare("SELECT * FROM barcode_data_2 WHERE hh_id IN ($placeholders)");
        $stmt_all->bind_param($types, ...$search_ids);
        $stmt_all->execute();
        $result_all = $stmt_all->get_result();
        
        while ($row_all = $result_all->fetch_assoc()) {
            $all_records[] = $row_all;
        }
        $stmt_all->close();
    }
}
$conn->close();

// Determine which dataset to use for display
$display_data = $show_all_grantees ? $all_grantees : $rows;
$is_all_grantees_mode = $show_all_grantees;

// Prepare data for JavaScript
$js_display_data = [];
foreach ($display_data as $index => $row) {
    $js_display_data[] = [
        'index' => $index,
        'hh_id' => $row['hh_id']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_all_grantees_mode ? 'All Grantee Records' : 'Search Multiple Household IDs' ?></title>
    <link rel="stylesheet" href="CSS/print_id.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        .id-card {
            width: 100%;
            max-width: 400px;
            margin: 1px auto;
            border: 1px solid #ccc;
            padding: 20px;
            background-color: white;
            page-break-inside: avoid;
        }
        
        .id-card-container {
            max-width: 400px; 
            height: 300px; 
            overflow: hidden;
            display: flex;
            align-items: center; 
            justify-content: center;
            margin: 0 auto;
            background-color: #f8f9fa;
        }

        .container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .container-test {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            position: relative;
            text-align: center;
            color: black;
            border: 2px solid #000;
        }

        .household-id {
            position: absolute;
            bottom: 102px;
            left: 200px;
            font-size: 13px;
        }

        .household-name {
            position: absolute;
            top: 70px;
            left: 160px;
            font-size: 13px;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hh_set_group {
            position: absolute;
            bottom: 87px;
            right: 65px;
            font-size: 13px;
        }

        .address-h {
            position: absolute;
            top: 100px;
            left: 130px;
            font-size: 13px;
            max-width: 250px;
        }

        .barangay {
            position: absolute;
            top: 87px;
            left: 230px;
            font-size: 13px;
        }
        
        .qr-code-container {
            position: absolute;
            top: 174px;
            right: 202px;
            width: 65px;
            height: 65px;
            background: transparent;
            padding: 3px;
            border-radius: 5px;
            image-rendering: pixelated;
        }
        
        .search-container {
            max-width: 800px;
            margin: 0 auto 30px;
        }
        
        .print-btn-container {
            text-align: center;
            margin: 20px 0;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .not-found-card {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }
        
        .no-grantee-card {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media print {
            body * { 
                visibility: hidden; 
            }
            .print-section, .print-section * { 
                visibility: visible; 
            }
            .print-section { 
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .cards-grid {
                display: block;
            }
            .id-card {
                page-break-after: always;
                margin: 0;
            }
            .all-grantees-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
                font-size: 18px;
                font-weight: bold;
            }
        }

        .instructions {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        
        .grantee-badge {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7em;
            margin-left: 5px;
        }
        
        .all-grantees-header {
            display: none;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <h1 class="text-center mb-4">
                    <?= $is_all_grantees_mode ? 'All Grantee Records' : 'Search Multiple Household IDs' ?>
                </h1>
                
                <?php if (!$is_all_grantees_mode): ?>
                <div class="search-container no-print">
                    <div class="instructions">
                        <strong>Instructions:</strong> Enter up to 5 Household IDs separated by commas. Example: <code>HH001, HH002, HH003</code>
                        <br><strong>Note:</strong> Only records marked as "YES" in hh_grantee column will be displayed and printed.
                    </div>
                    
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-2">
                                <div class="col-md-8">
                                    <input type="text" name="hh_ids" 
                                        value="<?= htmlspecialchars($_GET['hh_ids'] ?? '') ?>" 
                                        class="form-control" 
                                        placeholder="Enter Household IDs (comma separated, max 5)" 
                                        autofocus>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">Search IDs</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($is_all_grantees_mode || !empty($_GET['hh_ids'])): ?>
                    <div class="print-btn-container no-print">
                        <div class="action-buttons">
                            <button onclick="printAllIDs()" class="btn btn-success">
                                <i class="fas fa-print"></i> Print All ID Cards
                            </button>
                            <?php if ($is_all_grantees_mode): ?>
                                <a href="?show_all_grantees=0" class="btn btn-warning">
                                    <i class="fas fa-search"></i> Back to Search
                                </a>
                            <?php else: ?>
                                <a href="?show_all_grantees=1" class="btn btn-info">
                                    <i class="fas fa-list"></i> Show All Grantees
                                </a>
                                <a href="user_dashboard.php" class="btn btn-outline-primary">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Results Summary -->
                    <div class="alert alert-info no-print">
                        <?php if ($is_all_grantees_mode): ?>
                            <strong>All Grantee Records:</strong> 
                            Displaying all <?= count($display_data) ?> records marked as grantees (hh_grantee = 'YES').
                        <?php else: ?>
                            <strong>Search Results:</strong> 
                            Found <?= count($rows) ?> grantee records out of <?= count($all_records ?? []) ?> total records for <?= count($search_ids) ?> requested Household IDs.
                            <?php if (count($rows) < count($all_records ?? [])): ?>
                                <br><small><?= count($all_records ?? []) - count($rows) ?> records are not marked as grantees (hh_grantee ≠ 'YES')</small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Statistics Card for All Grantees -->
                    <?php if ($is_all_grantees_mode && count($display_data) > 0): ?>
                    <div class="stats-card no-print">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h3><?= count($display_data) ?></h3>
                                <small>Total Grantees</small>
                            </div>
                            <div class="col-md-3">
                                <h3><?= count(array_unique(array_column($display_data, 'hh_id'))) ?></h3>
                                <small>Unique Households</small>
                            </div>
                            <div class="col-md-3">
                                <h3><?= count(array_unique(array_column($display_data, 'municipality'))) ?></h3>
                                <small>Municipalities</small>
                            </div>
                            <div class="col-md-3">
                                <h3><?= count(array_unique(array_column($display_data, 'barangay'))) ?></h3>
                                <small>Barangays</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- ID Cards Grid -->
                    <div class="print-section">
                        <!-- Header for All Grantees Print -->
                        <?php if ($is_all_grantees_mode): ?>
                        <div class="all-grantees-header">
                            <h2>All Grantee Records - Total: <?= count($display_data) ?></h2>
                            <p>Generated on: <?= date('F j, Y g:i A') ?></p>
                            <hr>
                        </div>
                        <?php endif; ?>
                        
                        <div class="cards-grid">
                            <?php if (count($display_data) > 0): ?>
                                <?php foreach ($display_data as $index => $row): ?>
                                    <div class="id-card" id="id-card-<?= $index ?>">
                                        <div class="id-card-container">
                                            <div class="container-test">
                                                <img src="Pictures/pantawid_id_format.jpg" alt="ID Card Background">
                                                
                                                <!-- QR Code Container -->
                                                <div class="qr-code-container" id="qrcode-<?= $index ?>"></div>
                                                
                                                <div class="household-id">
                                                    <span><?= htmlspecialchars($row['hh_id']) ?></span>
                                                </div>
                                                <div class="household-name">
                                                    <span><?= htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' ' . ($row['name_extension'] ?? '')) ?></span>
                                                </div>
                                                <div class="hh_set_group">
                                                    <span><?= htmlspecialchars($row['hh_set_group']) ?></span>
                                                </div>
                                                <div class="barangay">
                                                    <span><?= htmlspecialchars($row['barangay'] . ', ') ?></span>
                                                </div>
                                                <div class="address-h">
                                                    <span><?= htmlspecialchars($row['municipality'] . ', ' . $row['province']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center">
                                    <div class="alert alert-warning">
                                        <h5>No Records Found</h5>
                                        <p>No grantee records found with hh_grantee = 'YES'</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!$is_all_grantees_mode): ?>
                                <!-- Display IDs with no grantee records -->
                                <?php 
                                $found_ids = array_column($rows, 'hh_id');
                                $all_found_ids = array_column($all_records ?? [], 'hh_id');
                                $not_found_ids = array_diff($search_ids, $all_found_ids);
                                $no_grantee_ids = array_diff($search_ids, $not_found_ids, $found_ids);
                                ?>
                                
                                <?php foreach ($no_grantee_ids as $no_grantee_id): ?>
                                    <div class="id-card">
                                        <div class="no-grantee-card">
                                            <h5>No Grantee Found</h5>
                                            <p><strong>Household ID:</strong> <?= htmlspecialchars($no_grantee_id) ?></p>
                                            <small class="text-muted">This household ID exists but has no records marked as grantee (hh_grantee = 'YES')</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Display not found IDs -->
                                <?php foreach ($not_found_ids as $not_found_id): ?>
                                    <div class="id-card">
                                        <div class="not-found-card">
                                            <h5>Household ID Not Found</h5>
                                            <p><strong>ID:</strong> <?= htmlspecialchars($not_found_id) ?></p>
                                            <small class="text-muted">This ID was not found in the database</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Pass PHP data to JavaScript -->
                    <script>
                        const displayData = <?= json_encode($js_display_data) ?>;
                    </script>
                    
                <?php else: ?>
                    <div class="text-center no-print">
                        <div class="action-buttons mb-4">
                            <a href="?show_all_grantees=1" class="btn btn-info btn-lg">
                                <i class="fas fa-list"></i> Show All Grantee Records
                            </a>
                            <a href="admin_dashboard.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-tachometer-alt"></i> Back to Dashboard
                            </a>
                        </div>
                        <p class="text-muted">Or search for specific household IDs above</p>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>

    <!-- External JavaScript file -->
    <script src="js/admin_print_id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>