<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Register</title>
</head>
<body>
    <div class="container mt-5 text-muted ">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                  <div class="card-header bg-dark">
                    <h5 class="row justify-content-center text-white">Register User</h5>
                  </div>
                     
                
                    <div class="container card-body">

                        <form action="send_to_email.php" method="POST">
                            <div class="form-group mb-3">
                            <label for="username">Username</label>
                            <input type="text" name="nameuser" class="form-control"placeholder="Username..." required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input type="Email" name="email" class="form-control"placeholder="Email..." required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Password..." required>
                            </div>
                            <div class="form-group mb-2 mt-2">
                                <button type="submit" name ="register" class="btn btn-primary">Register</button>
                                <a href="login.php" class="btn btn-outline-danger">Back</a>
                            </div>
                        </form>
                    </div>
                  </div>
                </div>
            </div>
        </div>
           
        </form>
    </div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
