<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <title>Register</title>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                  <div class="card-header">
                    <h1 class="row justify-content-center">VERIFICATION CODE</h1>
                    <h6 class="row justify-content-center"> verification code was sent to the Admin</h6>
                  </div>
                     
                
                    <div class="card-body">

                        <form action="verifysuccessful.php" method="POST">
                            <div class="form-group mb-3">
                                <label for="verification_code">ENTER CODE TO VERIFY USER</label>
                                <input type="text" class="form-control" id="verifiedcode" name="verifiedcode" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">VERIFY</button>
                            </div>
                        </form>
                    </div>
                  </div>
                </div>
            </div>
        </div>
           
        </form>
    </div>
</body>
</html>