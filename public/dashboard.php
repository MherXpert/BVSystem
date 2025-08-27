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
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>
<body>
    <div class="container g-0 mt-5">
        <div class="row">
            <div class="col-md-6 mx-auto text-center mt-5 lead fw-bold">
            <h1>Welcome, <?php echo $_SESSION['nameuser']; ?>.</h1>
            <br><br>
            <a href="show_data.php">SCAN QR CODE</a>
            <br>
            <a href="update_database.php" class="text-muted">UPDATE DATABASE</a>
            <br>
            <a href="print_id.php" class="text-muted">PRINT ID</a>
            </div>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
