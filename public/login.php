<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Login</title>
</head>
<body class="bg-light">
    <div class="container">
        <br>
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
                                <input type="email" id="email_address" name="email" class="form-control shadow-sm" autocomplete="off" placeholder="Enter Email..." required autofocus id="email_address">
                            </div>

                            <div class="form-group mt-4 row g-1">
                                <label for="">Password</label>
                                <input type="password" name="password" class="form-control shadow-sm" autocomplete="off" placeholder="Enter Password..." required>
                            </div>
                            
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>