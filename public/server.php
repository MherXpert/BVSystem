<?php
// LOG IN CONNECTION
session_start();
require_once '../app/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- Admin/User Login Logic ---
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) 
    {
        $row = $result->fetch_assoc();

        // Check if the user is verified
        if ($row['is_verified'] == 1) 
        {
            // Verify the password
            if (password_verify($password, $row['password'])) 
            {
                $_SESSION['nameuser'] = $row['nameuser'];
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                
                // Redirect based on role
                if ($row['role'] === 'admin' || $row['role'] === 'superadmin') {
                    echo "<script type='text/javascript'>
                            alert('Successfully logged in as Admin.');
                            window.location.href = 'admin_dashboard.php';
                        </script>";
                } else {
                    echo "<script type='text/javascript'>
                            alert('Successfully logged in.');
                            window.location.href = 'user_dashboard.php';
                        </script>";
                }
                exit();
            } else {
                echo "<script type='text/javascript'>
                        alert('Invalid password');
                        window.location.href = 'login.php';
                    </script>";
            }
        } else {
            echo "<script type='text/javascript'>
                    alert('Your account is not verified. Please verify your email first.');
                    window.location.href = 'login.php';
                </script>";
        }
    } else {
        echo "<script type='text/javascript'>
                alert('No user found with this email.');
                window.location.href = 'login.php';
            </script>";
    }
}
?>