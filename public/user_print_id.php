<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "barcode") or die("Connection failed: " . mysqli_connect_error());

// Pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$records_per_page = 20; // Number of records to show per page
$offset = ($page - 1) * $records_per_page;

// Check if we're showing all grantees
$show_all_grantees = isset($_GET['show_all_grantees']) && $_GET['show_all_grantees'] == '1';
$show_table_view = isset($_GET['view']) && $_GET['view'] == 'table';

// Fetch data based on the request type
$rows = [];
$search_ids = [];
$all_grantees = [];
$total_records = 0;

if ($show_all_grantees) {
    // Get total count for pagination
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM barcode_data_2 WHERE hh_grantee = 'YES'");
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_row = $count_result->fetch_assoc();
    $total_records = $total_row['total'];
    $count_stmt->close();
    
    // Fetch paginated records - assuming there's an 'id' column in the database
    $stmt = $conn->prepare("SELECT * FROM barcode_data_2 WHERE hh_grantee = 'YES' ORDER BY id ASC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $records_per_page, $offset);
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
        $stmt = $conn->prepare("SELECT * FROM barcode_data_2 WHERE hh_id IN ($placeholders) AND hh_grantee = 'YES' ORDER BY id ASC");
        
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
        $stmt_all = $conn->prepare("SELECT * FROM barcode_data_2 WHERE hh_id IN ($placeholders) ORDER BY id ASC");
        $stmt_all->bind_param($types, ...$search_ids);
        $stmt_all->execute();
        $result_all = $stmt_all->get_result();
        
        while ($row_all = $result_all->fetch_assoc()) {
            $all_records[] = $row_all;
        }
        $stmt_all->close();
        
        $total_records = count($rows);
    }
}
$conn->close();

// Calculate total pages
$total_pages = ceil($total_records / $records_per_page);

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
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Your existing CSS styles remain the same */
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
            image-rendering: pixelated;
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
            right: 80px;
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
            left: 218px;
            font-size: 12px;
        }
        
        .qr-code-container {
            position: absolute;
            top: 173px;
            right: 201px;
            width: 66px !important;
            height: 66px !important;
            background: transparent;
            padding: 3px;
            border-radius: 5px;
            image-rendering: pixelated;
            image-rendering: crisp-edges;
            -ms-interpolation-mode: nearest-neighbor;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: -moz-crisp-edges;
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

        /* Table View Styles */
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 20px 0;
        }

        .table th {
            background-color: #343a40;
            color: white;
            font-weight: 600;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge-grantee {
            background-color: #28a745;
        }

        .export-btn {
            margin: 10px 5px;
        }
        
        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        
        .pagination-info {
            text-align: center;
            margin-bottom: 10px;
            color: #6c757d;
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
               /* page-break-after: always; */
                margin: 0;
                page-break-after: none;
                display: inline-block;
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

        .view-toggle-buttons {
            margin-bottom: 20px;
        }
        
        /* Loading indicator */
        .loading-indicator {
            text-align: center;
            padding: 20px;
            display: none;
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
                            <?php if ($show_table_view): ?>
                                <!--<a href="?show_all_grantees=1&view=cards&page=<?= $page ?>" class="btn btn-info">-->
                                    <!--<i class="fas fa-id-card"></i> Show ID Cards-->
                                </a>
                            <?php else: ?>
                                <button onclick="printAllIDs()" class="btn btn-success">
                                    <i class="fas fa-print "></i> Print Search ID Card
                                </button>
                                <?php if ($is_all_grantees_mode): ?>
                                    <a href="?show_all_grantees=1&view=table&page=<?= $page ?>" class="btn btn-primary">
                                        <i class="fas fa-table"></i> Show Table View
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($is_all_grantees_mode): ?>
                                <a href="?show_all_grantees=0" class="btn btn-warning">
                                    <i class="fas fa-search"></i> Back to Search
                                </a>
                            <?php else: ?>
                                <a href="?show_all_grantees=1&view=table" class="btn btn-info">
                                    <i class="fas fa-list"></i> Show All Grantees (Table)
                                </a>
                                <a href="user_dashboard.php" class="btn btn-outline-primary">
                                    <i class="fas fa-chalkboard"></i> Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Results Summary -->
                    <div class="alert alert-info no-print">
                        <?php if ($is_all_grantees_mode): ?>
                            <strong>All Grantee Records:</strong> 
                            Displaying <?= count($display_data) ?> records (page <?= $page ?> of <?= $total_pages ?>) out of <?= $total_records ?> total grantee records.
                            <?php if ($show_table_view): ?>
                                in table view.
                            <?php else: ?>
                                in ID card view.
                            <?php endif; ?>
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
                                <h3><?= $total_records ?></h3>
                                <small>Total Grantees</small>
                            </div>
                            <div class="col-md-3">
                                <h3><?= $page ?></h3>
                                <small>Current Page</small>
                            </div>
                            <div class="col-md-3">
                                <h3><?= $total_pages ?></h3>
                                <small>Total Pages</small>
                            </div>
                            <div class="col-md-3">
                                <h3><?= count($display_data) ?></h3>
                                <small>This Page</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Display based on view type -->
                    <?php if ($show_table_view && $is_all_grantees_mode): ?>
                        <!-- TABLE VIEW -->
                        <div class="table-container">
                            <div class="table-responsive">
                                <table id="granteesTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Full Name</th>
                                            <th>Household ID</th>
                                            <th>Household Set Group</th>
                                            <th>Address</th>
                                            <th>Barangay</th>
                                            <th>Municipality</th>
                                            <th>Province</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($display_data as $index => $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id'] ?? 'N/A') ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' ' . ($row['name_extension'] ?? '')) ?></strong>
                                                <?php if (!empty($row['middle_name'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($row['middle_name']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($row['hh_id']) ?></code>
                                            </td>
                                            <td><?= htmlspecialchars($row['hh_set_group']) ?></td>
                                            <td>
                                                <?php 
                                                $address = [];
                                                if (!empty($row['barangay'])) $address[] = $row['barangay'];
                                                if (!empty($row['municipality'])) $address[] = $row['municipality'];
                                                if (!empty($row['province'])) $address[] = $row['province'];
                                                echo htmlspecialchars(implode(', ', $address));
                                                ?>
                                            </td>
                                            <td><?= htmlspecialchars($row['barangay']) ?></td>
                                            <td><?= htmlspecialchars($row['municipality']) ?></td>
                                            <td><?= htmlspecialchars($row['province']) ?></td>
                                            <td>
                                                <span class="badge bg-success">GRANTEE</span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination for table view -->
                            <?php if ($is_all_grantees_mode && $total_pages > 1): ?>
                            <div class="pagination-container no-print">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <!-- Previous Page Link -->
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?show_all_grantees=1&view=table&page=<?= $page - 1 ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link" aria-hidden="true">&laquo;</span>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <!-- Page Numbers -->
                                        <?php 
                                        // Show page numbers with ellipsis for many pages
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);
                                        
                                        if ($start_page > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="?show_all_grantees=1&view=table&page=1">1</a></li>';
                                            if ($start_page > 2) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                        }
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++): 
                                        ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?show_all_grantees=1&view=table&page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php
                                        if ($end_page < $total_pages) {
                                            if ($end_page < $total_pages - 1) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="?show_all_grantees=1&view=table&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                        }
                                        ?>
                                        
                                        <!-- Next Page Link -->
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?show_all_grantees=1&view=table&page=<?= $page + 1 ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link" aria-hidden="true">&raquo;</span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                            <div class="pagination-info no-print">
                                Page <?= $page ?> of <?= $total_pages ?> | Showing <?= count($display_data) ?> of <?= $total_records ?> records
                            </div>
                            <?php endif; ?>
                        </div>
                    
                    <?php else: ?>
                        <!-- ID CARDS VIEW -->
                        <div class="print-section">
                            <!-- Header for All Grantees Print -->
                            <?php if ($is_all_grantees_mode): ?>
                            <div class="all-grantees-header">
                                <h2>All Grantee Records - Page <?= $page ?> of <?= $total_pages ?></h2>
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
                            
                            <!-- Pagination for ID Cards view -->
                            <?php if ($is_all_grantees_mode && $total_pages > 1): ?>
                            <div class="pagination-container no-print">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <!-- Previous Page Link -->
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?show_all_grantees=1&view=cards&page=<?= $page - 1 ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link" aria-hidden="true">&laquo;</span>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <!-- Page Numbers -->
                                        <?php 
                                        // Show page numbers with ellipsis for many pages
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);
                                        
                                        if ($start_page > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="?show_all_grantees=1&view=cards&page=1">1</a></li>';
                                            if ($start_page > 2) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                        }
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++): 
                                        ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?show_all_grantees=1&view=cards&page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php
                                        if ($end_page < $total_pages) {
                                            if ($end_page < $total_pages - 1) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="?show_all_grantees=1&view=cards&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                        }
                                        ?>
                                        
                                        <!-- Next Page Link -->
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?show_all_grantees=1&view=cards&page=<?= $page + 1 ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link" aria-hidden="true">&raquo;</span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                            <div class="pagination-info no-print">
                                Page <?= $page ?> of <?= $total_pages ?> | Showing <?= count($display_data) ?> of <?= $total_records ?> records
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Pass PHP data to JavaScript -->
                    <script>
                        const displayData = <?= json_encode($js_display_data) ?>;
                    </script>
                    
                <?php else: ?>
                    <div class="text-center no-print">
                        <div class="action-buttons mb-4">
                            <a href="?show_all_grantees=1&view=table" class="btn btn-info btn-lg">
                                <i class="fas fa-table"></i> Show All Grantees (Table View)
                            </a>
                            <a href="user_dashboard.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-chalkboard"></i> Dashboard
                            </a>
                        </div>
                        <p class="text-muted">Or search for specific household IDs above</p>
                    </div>
                <?php endif; ?>
            
            </div>
        </div>
    </div>

    <!-- DataTables JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- External JavaScript file -->
    <script src="js/user_print_id.js"></script>
    
    <!-- Table functionality -->
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            if ($('#granteesTable').length) {
                $('#granteesTable').DataTable({
                    "pageLength": 25,
                    "responsive": true,
                    "order": [[0, 'asc']], // Sort by ID column in ascending order
                    "language": {
                        "search": "Search grantees:",
                        "lengthMenu": "Show _MENU_ entries",
                        "info": "Showing _START_ to _END_ of _TOTAL_ grantees"
                    }
                });
            }
        });

        function printAllIDs() {
            window.print();
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>