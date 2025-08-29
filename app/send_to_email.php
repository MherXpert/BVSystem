<?php
session_start(); // Start the session
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $nameuser = $_POST['nameuser'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $verification_code = rand(100000, 999999);

    // Check if email already exists
    $check_email_query = "SELECT email FROM users WHERE email ='$email' LIMIT 1";
    $check_email_query_run = mysqli_query($conn, $check_email_query);

    if (mysqli_num_rows($check_email_query_run) > 0) 
    {
        //$_SESSION['status'] = "Email Address already exists.";
        echo "<script>alert('Email ID already exists.');window.location.href = '../public/register.php';</script>"; // Redirect to the registration form. alerts if the email already exist.
        exit();
    } 
    else 
    {
        // Insert user data into the database
        $sql = "INSERT INTO users (nameuser, email, password, verification_code) VALUES ('$nameuser', '$email', '$password', '$verification_code')";

        if ($conn->query($sql) === TRUE) 
        {
            // Send email to admin
            $admin_email = "mjdswd09@gmail.com"; // Administrative email
            $subject = "NEW USER REGISTRATION";
            $message = "EMAIL ADDRESS: $email \nUSERNAME: $nameuser\nVERIFICATION CODE = $verification_code";
            mail($admin_email, $subject, $message);

            include '../public/verification_code.php';
        } 
        else 
        {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>