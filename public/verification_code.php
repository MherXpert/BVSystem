<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #3cebffff 0%, #445affff 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .verification-container {
            width: 100%;
            max-width: 450px;
        }
        
        .verification-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px 30px;
            text-align: center;
        }
        
        .verification-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 24px;
            letter-spacing: 1px;
        }
        
        .verification-message {
            background-color: #e8f4ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            color: #2575fc;
            font-size: 14px;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
            color: #444;
            text-align: left;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #2575fc;
            box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.25);
        }
        
        .btn-verify {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
        }
        
        .btn-back {
            background-color: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 15px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-back:hover:not(:disabled) {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.2);
        }
        
        .btn-back:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .code-validation {
            margin-top: 5px;
            font-size: 14px;
            text-align: left;
            min-height: 20px;
        }
        
        .valid-feedback {
            color: #28a745;
            display: none;
        }
        
        .invalid-feedback {
            color: #dc3545;
            display: none;
        }
        
        .input-valid {
            border-color: #28a745 !important;
        }
        
        .input-invalid {
            border-color: #dc3545 !important;
        }
        
        .input-valid + .valid-feedback {
            display: block;
        }
        
        .input-invalid + .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <h2 class="verification-title">VERIFICATION CODE</h2>
            
            <div class="verification-message">
                <i class="fas fa-shield-alt me-2"></i>
                <p class="mb-0">Verification code was sent to the Admin</p>
            </div>
            
            <form action="../public/verifysuccessful.php" method="POST" id="verificationForm">
                <div class="form-group">
                    <label for="verifiedcode" class="form-label">ENTER CODE TO VERIFY USER</label>
                    <input type="text" class="form-control" id="verifiedcode" name="verifiedcode" 
                           placeholder="Enter 6-digit verification code..." maxlength="6" required>
                    <div class="valid-feedback">
                        <i class="fas fa-check-circle me-1" ></i> Valid 6-digit code
                    </div>
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-circle me-1" autocomplete="off"></i> Please enter a valid 6-digit code
                    </div>
                </div>
                <button type="submit" class="btn-verify">Verify</button>
                <a href="register.php" class="btn btn-back" id="backButton" disabled>Back</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codeInput = document.getElementById('verifiedcode');
            const backButton = document.getElementById('backButton');
            const form = document.getElementById('verificationForm');
            
            // Initially disable the back button
            backButton.style.pointerEvents = 'none';
            backButton.setAttribute('disabled', 'disabled');
            
            // Validate input on each keystroke
            codeInput.addEventListener('input', function() {
                validateCode();
            });
            
            // Validate on form submission
            form.addEventListener('submit', function(e) {
                if (!validateCode()) {
                    e.preventDefault();
                }
            });
            
            function validateCode() {
                const code = codeInput.value.trim();
                const isSixDigits = /^\d{6}$/.test(code);

                if (isSixDigits) {
                    codeInput.classList.remove('input-invalid');
                    codeInput.classList.add('input-valid');
                    // Enable the back button
                    backButton.style.pointerEvents = 'auto';
                    backButton.removeAttribute('disabled');
                    return true;
                } else {
                    codeInput.classList.remove('input-valid');
                    codeInput.classList.add('input-invalid');
                    // Disable the back button
                    backButton.style.pointerEvents = 'none';
                    backButton.setAttribute('disabled', 'disabled');
                    return false;
                }
            }
        });
    </script>
</body>
</html>