<?php
//VERIFICATION OF EMAIL CODE

session_start();
require_once '../app/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verification_code = $_POST['verifiedcode'];

    // Check if the verification code exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE verification_code = ?");
    $stmt->bind_param("s", $verification_code); // Bind the parameter
    $stmt->execute();

    // Get the result as an associative array
    $result = $stmt->get_result();
    $nameuser = $result->fetch_assoc();

    if ($nameuser) {
        // Matching user found
        $stmt->close();

        // Mark the user as verified
        $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
        $update_stmt->bind_param("i", $nameuser['id']);
        $update_stmt->execute();

        $_SESSION['message'] = "Email verified successfully! You can now login.";

        // Use JavaScript to show an alert and redirect
        echo "<script type='text/javascript'>
            alert('" . $_SESSION['message'] . "');
            window.location.href = 'login.php';
        </script>";
        exit();

    } else {
        // No matching user found
        $_SESSION['error'] = "Invalid verification code.";
        // Use JavaScript to show an alert and redirect
        echo "<script type='text/javascript'>
            alert('" . $_SESSION['error'] . "');
            window.location.href = 'verification_code.php';
        </script>";
        exit();
    }
}
?>