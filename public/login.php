<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/login.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Login</title>
    <style>
        .footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-top: 190px;
            border-top: 1px solid #dee2e6;
        }
        .footer-content {
            text-align: center;
            color: #6c757d;
            font-size: 0.6rem;
        }
        .developer-info {
            font-weight: italic;
            color: #495057;
            text-muted: true;
        }
        .position-info {
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <div class="background-image"></div> 
    <div class="container position-relative z-index-1"> <br>
        <div class="row">
            <img src="Pictures/bagong pilipinas.png" style="max-width:8%;" alt="logo">
            <img src="Pictures/dswd logo.png" style="max-width:200px;" alt="logo">
            <img src="Pictures/pantawid logo.png" style="max-width:8%;" alt="logo">
        </div>
    </div>

    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-md-6 align-items-start text-black">
                <!-- Left Section -->
                <h1>Welcome to DSWD Davao</h1>
                <h1>BVS: Beneficiary Verification System</h1>
                <br>
                <h5>For 4P's Beneficiary</h5>
                <br>
                <h6 class="text-muted"> Beneficiary Verification System ensures accurate identification, 
                <br> validation, and tracking of Pantawid Pamilyang Pilipino Program beneficiaries. 
                <h6 class="text-muted"> Optimize workflow and reduce manual intervention by implementing automated verification.</h6>
            </div>

            <!-- Right Section -->
            <div class="col-md-5 mt-2 mx-2">
                <div class="card shadow">
                    <div class="card-header bg-dark">
                        <h5 class="row justify-content-center text-white">BENEFICIARY VERIFICATION SYSTEM</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="server.php">
                            <div class="form-group row g-3">
                                <label for="">Email Address</label>
                                <input type="email" id="email_address" name="email" class="form-control shadow-sm" placeholder="Enter Email..." required autofocus id="email_address">
                            </div>

                            <div class="form-group mt-4 row g-1">
                                <label for="">Password</label>
                                <div class="position-relative">
                                    <input type="password" name="password" class="form-control shadow-sm" autocomplete="off" placeholder="Enter Password..." id="UserPass" required>
                                    <span toggle="#UserPass" class="fa fa-fw fa-eye field-icon toggle-password position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></span>
                                </div>
                            </div>

                            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                            <script>
                                $(document).ready(function() {
                                    $(".toggle-password").click(function() {
                                        $(this).toggleClass("fa-eye fa-eye-slash");
                                        var input = $($(this).attr("toggle"));
                                        if (input.attr("type") == "password") {
                                            input.attr("type", "text");
                                        } else {
                                            input.attr("type", "password");
                                        }
                                    });
                                });
                            </script>
                            
                            <div class="form-group">
                                <br><button class="btn btn-outline-primary py-1"> Log-in</button>
                            </div>
                        </form>
                    </div>
                    <p class="row mx-1"><a href="register.php">Create account</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="developer-info">
                    Mher Jason C. Cabreros, CpE
                </div>
                <div class="position-info">
                    Computer Maintenance Technologist II, PPPPMD DSWD FO XI
                </div>
                <div class="rights-reserved mt-2">
                    &copy; 2024 Beneficiary Verification System. All Rights Reserved.
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>