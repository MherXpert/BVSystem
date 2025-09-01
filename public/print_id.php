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
    <style>
        .id-card {
            width: 100%;
            max-width: 500px;
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
    </style>
</head>
  
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="text-center mb-4">Search Household</h1>
                
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

                <?php if (!empty($_GET['hh_id'])): ?>
                    <?php if ($row): ?>
                        <div class="text-center mb-3">
                          
                            <button onclick="printID()" class="btn btn-success">Print ID</button>
                        </div>
                        
                        <!-- ID Card Container -->
                        <div id="id-card" class="id-card-container">
                            <div class="id-card">
                                <div class="id-header">
                                    <div class="id-title">Republic of the Philippines</div>
                                    <div class="id-subtitle">Department of Social Welfare and Development Field Office XI</div>
                                </div>
                                
                                <div class="id-divider"></div>
                                
                                <div class="id-details">
                                    <div class="id-row">
                                        <span class="id-label">Name:</span>
                                        <span class="id-value"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></span>
                                    </div>
                                    <div class="id-row">
                                        <span class="id-label">Household address:</span>
                                        <span class="id-value"><?= htmlspecialchars($row['municipality'] . ', ' . $row['province']) ?></span>
                                    </div>
                                    <div class="id-row">
                                        <span class="id-label">Household ID #:</span>
                                        <span class="id-value"><?= htmlspecialchars($row['hh_id']) ?></span>
                                    </div>
                                    <div class="id-row">
                                        <span class="id-label">Household Set Group:</span>
                                        <span class="id-value"><?= htmlspecialchars($row['entry_id']) ?></span>
                                    </div>
                                </div>
                                
                                <div class="id-divider"></div>
                                
                                <div class="id-signature">
                                    <div class="id-signature-line">signature/thumbmark</div>
                                    <div class="id-secretary">Rev T. Gatchalian</div>
                                    <div class="id-secretary-title">DSWD Secretary</div>
                                </div>
                                
                                <div class="id-divider"></div>
                                
                                <div class="id-validity">
                                    <div class="id-row">
                                        <span class="id-label">Issued on:</span>
                                        <span class="id-value"><?= date('m/d/Y') ?></span>
                                    </div>
                                    <div class="id-row">
                                        <span class="id-label">Valid until:</span>
                                        <span class="id-value"><?= date('m/d/Y', strtotime('+1 year')) ?></span>
                                    </div>
                                </div>
                                
                                <div class="id-note">
                                    In case of loss, please return to the nearest 4Ps office.
                                </div>
                            </div>

                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">Household ID not found</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <a href="dashboard.php" class="btn btn-warning">Back</a>
    <script>
        function printID() {
            window.print();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>