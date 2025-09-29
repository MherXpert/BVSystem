<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "barcode") or die("Connection failed: " . mysqli_connect_error());

// Fetch data if hh_id is provided
$row = null;
if (!empty($_GET['hh_id'])) {
    $hh_id = trim($_GET['hh_id']);
    $stmt = $conn->prepare("SELECT * FROM barcode_data_2 WHERE hh_id = ?");
    $stmt->bind_param("s", $hh_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Household</title>
    <link rel="stylesheet" href="CSS/print_id.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        .id-card {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 20px;
            background-color: white;
        }
        @media print {
            body * { visibility: hidden; }
            #id-card, #id-card * { visibility: visible; }
            #id-card { position: absolute; left: 0; top: 0; }
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
            bottom: 115px;
            left: 223px;
            font-size: 13px;
        }

        .household-name {
            position: absolute;
            top: 81px;
            left: 169px;
            font-size: 13px;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .entry-id {
            position: absolute;
            bottom: 99px;
            right: 73px;
            font-size: 13px;
        }

        .address-h {
            position: absolute;
            top: 114px;
            left: 130px;
            font-size: 13px;
            max-width: 250px;
        }

        .barangay {
            position: absolute;
            top: 97px;
            left: 250px;
            font-size: 13px;
        }
        
        .qr-code-container {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 72px;
            height: 72px;
            background: transparent;
            padding: 3px;
            border-radius: 5px;
        }
        
        .search-container {
            max-width: 600px;
            margin: 0 auto 30px;
        }
        
        .print-btn-container {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code-container{
            position: absolute;
            top: 194px;
            right: 226px;
            image-rendering: pixelated; /* Standard property */
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1 class="text-center mb-4">Search Household ID</h1>
                
                <div class="search-container">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-2">
                                <div class="col-md-8">
                                    <input type="text" name="hh_id" value="<?= htmlspecialchars($_GET['hh_id'] ?? '') ?>" 
                                        class="form-control" placeholder="Enter Household ID" autofocus>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if (!empty($_GET['hh_id'])): ?>
                    <?php if ($row): ?>
                        <div class="print-btn-container">
                            <button onclick="printID()" class="btn btn-success">Print ID Card</button>
                            <a href="admin_dashboard.php" class="btn btn-warning">Back to Dashboard</a>
                        </div>
                        
                        <!-- ID Card Container -->
                        <div id="id-card" class="id-card-container">
                            <div class="container-test">
                                <img src="Pictures/pantawid_id_format.jpg" alt="ID Card Background">
                                
                                <!-- QR Code Container -->
                                <div class="qr-code-container">
                                    <div id="qrcode"></div>
                                </div>
                                
                                <div class="household-id">
                                    <span><?= htmlspecialchars($row['hh_id']) ?></span>
                                </div>
                                <div class="household-name">
                                    <span><?= htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['name_extension']) ?></span>
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
                        
                        <!-- JavaScript to generate QR code -->
                        <div class=>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Generate QR code data
                                    const qrData = `<?= $row['hh_id'] ?>`;

                                            // Generate QR code
                                            new QRCode(document.getElementById('qrcode'), {
                                                text: qrData,
                                                width: 70,
                                                height: 70,
                                                colorDark: "#000000",
                                                colorLight: "#ffffff",
                                                correctLevel: QRCode.CorrectLevel.H
                                            });
                                        });
                                    </script>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger text-center">Household ID not found.</div>
                            <div class="text-center">
                            <a href="admin_dashboard.php" class="btn btn-warning">Back to Dashboard</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center">
                        <a href="admin_dashboard.php" class="btn btn-warning">Back to Dashboard</a>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>

    <script>
        function printID() {
            window.print();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>