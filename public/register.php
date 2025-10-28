<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/register.css">
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <h2 class="register-title">Register User</h2>
            
            <form action="../app/send_to_email.php" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" name="nameuser" placeholder="Username..." required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="Email..." required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" placeholder="Password..." autocomplete="off" required>
                    
                </div>
                
                <button type="submit" class="btn-register">Register</button>
                <a href="login.php" class="btn btn-back">Back</a>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>