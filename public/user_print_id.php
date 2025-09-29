
<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "barcode") or die("Connection failed: " . mysqli_connect_error());

// Fetch data for multiple hh_ids
$rows = [];
$search_ids = [];

if (!empty($_GET['hh_ids'])) {
    $hh_ids_input = trim($_GET['hh_ids']);
    $search_ids = array_map('trim', explode(',', $hh_ids_input));
    $search_ids = array_slice($search_ids, 0, 5); // Limit to 5 IDs
    
    if (!empty($search_ids)) {
        // Create placeholders for prepared statement
        $placeholders = str_repeat('?,', count($search_ids) - 1) . '?';
        $stmt = $conn->prepare("SELECT * FROM barcode_data_2 WHERE hh_id IN ($placeholders)");
        
        // Bind parameters dynamically
        $types = str_repeat('s', count($search_ids));
        $stmt->bind_param($types, ...$search_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Multiple Household IDs</title>
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

        .entry-id {
            position: absolute;
            bottom: 88px;
            right: 50px;
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
        }

        .instructions {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <h1 class="text-center mb-4">Search Multiple Household IDs</h1>
                
                <div class="search-container no-print">
                    <div class="instructions">
                        <strong>Instructions:</strong> Enter up to 5 Household IDs separated by commas. Example: <code>HH001, HH002, HH003</code>
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

                <?php if (!empty($_GET['hh_ids'])): ?>
                    <div class="print-btn-container no-print">
                        <button onclick="printAllIDs()" class="btn btn-success">Print All ID Cards</button>
                        <a href="user_dashboard.php" class="btn btn-warning">Back to Dashboard</a>
                    </div>
                    
                    <!-- Results Summary -->
                    <div class="alert alert-info no-print">
                        <strong>Search Results:</strong> 
                        Found <?= count($rows) ?> out of <?= count($search_ids) ?> requested Household IDs.
                    </div>
                    
                    <!-- ID Cards Grid -->
                    <div class="print-section">
                        <div class="cards-grid">
                            <?php 
                            $found_ids = array_column($rows, 'hh_id');
                            $not_found_ids = array_diff($search_ids, $found_ids);
                            
                            // Display found records
                            foreach ($rows as $index => $row): ?>
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
                                            <div class="entry-id">
                                                <span><?= htmlspecialchars($row['entry_id']) ?></span>
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
                        </div>
                    </div>
                    
                    <!-- JavaScript to generate QR codes -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            <?php foreach ($rows as $index => $row): ?>
                                // Generate QR code for each found record
                                new QRCode(document.getElementById('qrcode-<?= $index ?>'), {
                                    text: '<?= $row['hh_id'] ?>',
                                    width: 70,
                                    height: 70,
                                    colorDark: "#000000",
                                    colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.H
                                });
                            <?php endforeach; ?>
                        });
                    </script>
                    
                <?php else: ?>
                    <div class="text-center no-print">
                        <a href="user_dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>

    <script>
        function printAllIDs() {
            window.print();
        }
        
        // Auto-focus on input and select all text
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.querySelector('input[name="hh_ids"]');
            if (input) {
                input.focus();
                input.select();
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>