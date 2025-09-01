<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['nameuser'])) {
    header("Location: login.php");
    exit();
}

// Show login success message if exists
if (isset($_SESSION['login_message'])) 
{
    echo "<script>alert('".$_SESSION['login_message']."');</script>";
    unset($_SESSION['login_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/dashboard.css">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>
<body>
    <div class="dashboard-container">
        <header class="welcome-header text-center py-2 mb-4">
            <div class="container">
                <div class="user-avatar mb-3">
                    <i class="fas fa-user"></i>
                </div>
                <h1>Welcome, <?php echo $_SESSION['nameuser']; ?>.</h1>
                <p class="lead">What would you like to do today?</p>
            </div>
        </header>

        <div class="main-content">
            <div class="container">
                <!-- Centered row using justify-content-center -->
                <div class="row justify-content-center">
                    <!-- Scan QR Code Card -->
                    <div class="col-md-5">
                        <div class="action-card text-center p-4">
                            <div class="action-icon">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <h3>SCAN QR CODE</h3>
                            <p class="text-muted">Scan QR codes to process information and view records</p>
                            <a href="show_data.php" class="btn btn-outline-primary">SCAN QR CODE</a>
                        </div>
                    </div>
                    
                    <!-- Print ID Card -->
                    <div class="col-md-5">
                        <div class="action-card text-center p-4">
                            <div class="action-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h3>PRINT ID</h3>
                            <p class="text-muted">Generate and print identification cards</p>
                            <a href="print_id.php" class="btn btn-outline-primary">Print ID</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Logout Button and Modal -->
        <div class="logout-container mt-auto">
            <div class ="container">
                <div class="d-flex justify-content-start">
                    <button type="button" class="btn btn-logout" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</button>
                </div>
            </div>
           
        </div>

        <!-- Logout Confirmation Modal -->
        <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to log out?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <a href="log_out.php" class="btn btn-danger">Logout</a>

                    </div>
                </div>
            </div>  


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
