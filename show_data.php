<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "barcode");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables
$res_row = 0;
$results = [];

// Fetch data if hh_id is provided
if (isset($_GET['hh_id'])) {
    $hh_id = trim($_GET['hh_id']);
    $query = "SELECT * FROM barcode_data_2 WHERE hh_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $hh_id);
    mysqli_stmt_execute($stmt);
    $query_run = mysqli_stmt_get_result($stmt);

    if ($query_run) {
        $res_row = mysqli_num_rows($query_run);
        $results = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiary Verification System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#btnclear').click(function() {
                $('input[name="hh_id"]').val('');
            });
        });
    </script>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow mt-5">
                    <div class="card-header text-center text-white bg-dark">
                        <h2>BENEFICIARY VERIFICATION SYSTEM</h2>
                    </div>
                    <div class="card-body">
                        <!-- Search Form -->
                        <form action="" method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                <input type="text" name="hh_id" value="<?= isset($_GET['hh_id']) ? htmlspecialchars($_GET['hh_id']) : '' ?>" class="form-control" placeholder="Enter Household ID" autofocus id="hh_id_input">
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const inputField = document.getElementById('hh_id_input');
                                            inputField.focus(); // Ensure focus is set
                                            inputField.value = ''; // Clear the input field on page load
                                        });
                                    </script>
                                </div>
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-outline-primary">Show Data</button>
                                </div>
                            </div>
                        </form>

                        <!-- Address Field -->
                        <div class="container mt-3">
                        <div class="row">
                            <div class="col-2">
                                <p class="fw-bold mt-2">PROVINCE:</p>
                                <p class="fw-bold mt-3">MUNICIPALITY:</p>
                            </div>
                            <div class="col-2">
                                <input type="text" class="form-control fw-bold" readonly value="<?= isset($results[0]['province']) ? htmlspecialchars($results[0]['province']) : '' ?>">
                                <input type="text" class="form-control fw-bold mt-1" readonly value="<?= isset($results[0]['municipality']) ? htmlspecialchars($results[0]['municipality']) : '' ?>">
                            </div>
                        </div>
                            </div>

                        <!-- Results Table -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <table class="table table-bordered text-center">
                                    <thead class="table-info">
                                        <tr>
                                            <th>FIRST NAME</th>
                                            <th>MIDDLE NAME</th>
                                            <th>LAST NAME</th>
                                            <th>NAME EXTENSION</th>
                                            <th>RELATION TO HOUSEHOLD HEAD</th>
                                            <th>BIRTHDAY (yy/mm/dd)</th>
                                            <th>SEX</th>
                                            <th>AGE</th>
                                            <th>MEMBER STATUS</th>
                                            <th>BARANGAY</th>
                                            <th>CLIENT STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($res_row > 0): ?>
                                            <?php foreach ($results as $row): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['first_name']) ?></td>
                                                    <td><?= htmlspecialchars($row['middle_name']) ?></td>
                                                    <td><?= htmlspecialchars($row['last_name']) ?></td>
                                                    <td><?= htmlspecialchars($row['name_extension']) ?></td>
                                                    <td><?= htmlspecialchars($row['Relation_to_hh_head']) ?></td>
                                                    <td><?= htmlspecialchars($row['birthday']) ?></td>
                                                    <td><?= htmlspecialchars($row['sex']) ?></td>
                                                    <td><?= htmlspecialchars($row['age']) ?></td>
                                                    <td><?= htmlspecialchars($row['member_status']) ?></td>
                                                    <td><?= htmlspecialchars($row['barangay']) ?></td>
                                                    <td><?= htmlspecialchars($row['client_status']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">NO DATA FOUND.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout Button and Modal -->
        <div class="container mt-3">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</button>
        </div>

        <!-- Logout Confirmation Modal -->
        <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to log out?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <a href="log_out.php" class="btn btn-danger">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>