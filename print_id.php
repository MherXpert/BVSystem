<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantawid ID System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Search Household</h1>
        
        <div class="card shadow">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" name="household_id" class="form-control" 
                                   placeholder="Enter Household ID" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>
                </form>
                
                <?php
                if (isset($_GET['hh_id'])) {
                    $household_id = $conn->real_escape_string($_GET['hh_id']);
                    $query = "SELECT * FROM barcode_data_2 WHERE hh_id = ?";
                    $result = $conn->query($sql);
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        ?>
                        <div class="mt-4">
                            <button onclick="printID()" class="btn btn-success mb-3">Print ID</button>
                            <div id="id-card" class="id-card">
                                <div class="id-header">
                                    <h5>Republic of the Philippines</h5>
                                    <p>Department of Social Welfare and Development Field Office XI</p>
                                </div>
                                
                                <div class="id-body">
                                    <p><strong>Name:</strong> <?php echo $row['name']; ?></p>
                                    <p><strong>Household address:</strong> <?php echo $row['address']; ?></p>
                                    <p><strong>Household ID #:</strong> <?php echo $row['household_id']; ?></p>
                                    <p><strong>Household Set Group:</strong> <?php echo $row['set_group']; ?></p>
                                </div>
                                
                                <div class="id-footer">
                                    <div class="signature">
                                        <p>signature/thumbmark</p>
                                        <p>Rev T. Gatchalian</p>
                                        <p>DSWD Secretary</p>
                                    </div>
                                    
                                    <div class="dates">
                                        <p><strong>Issued on:</strong> <?php echo date('m/d/Y', strtotime($row['issued_date'])); ?></p>
                                        <p><strong>Valid until:</strong> <?php echo date('m/d/Y', strtotime($row['valid_until'])); ?></p>
                                    </div>
                                </div>
                                
                                <div class="id-note">
                                    <p>In case of loss, please return to the nearest 4Ps office.</p>
                                </div>
                            </div>
                        </div>
                        <?php
                    } else {
                        echo '<div class="alert alert-danger mt-3">Household ID not found</div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <script>
    function printID() {
        var printContents = document.getElementById('id-card').innerHTML;
        var originalContents = document.body.innerHTML;
        
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>