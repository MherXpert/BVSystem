<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="CSS/verification_code.css">
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <h2 class="verification-title">VERIFICATION CODE</h2>
            
            <div class="verification-message">
                <p>Verification code was sent to the Admin</p>
            </div>
            
            <form action="../public/verifysuccessful.php" method="POST">
                <div class="form-group">
                    <label for="verification_code"class="form-label">ENTER CODE TO VERIFY USER</label>
                    <input type="text" class="form-control" id="verifiedcode" name="verifiedcode" placeholder="Enter verification code..."required>
                </div>
                    <button type="submit" class="btn-verify">Verify</button>
                    <button type="button" class="btn-back">Back</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>