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
                            <button onclick="window.print()" class="btn btn-success">Print ID</button>
                        </div>
                        <div id="id-card" class="id-card">
                            <div class="text-center mb-3">
                                <h5>Republic of the Philippines</h5>
                                <p>Department of Social Welfare and Development Field Office XI</p>
                            </div>
                            
                            <div class="mb-3">
                                <p><strong>Name:</strong> <?= htmlspecialchars($row['first_name']) ?></p>
                                <p><strong>Address:</strong> <?= htmlspecialchars($row['municipality']) ?></p>
                                <p><strong>Household ID:</strong> <?= htmlspecialchars($row['hh_id']) ?></p>
                                <p><strong>Set Group:</strong> <?= htmlspecialchars($row['entry_id']) ?></p>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <div class="text-center">
                                    <p class="border-top pt-5">signature/thumbmark</p>
                                    <p>Rev T. Gatchalian</p>
                                    <p>DSWD Secretary</p>
                                </div>
                                <div>
                                    <p><strong>Issued:</strong> <?= date('m/d/Y') ?></p>
                                    <p><strong>Valid until:</strong> <?= date('m/d/Y', strtotime('+3 years')) ?></p>
                                </div>
                            </div>
                            
                            <p class="text-center mt-3 text-muted small">
                                In case of loss, please return to the nearest 4Ps office.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">Household ID not found</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>