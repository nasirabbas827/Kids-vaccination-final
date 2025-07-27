<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $age_limit_months = intval($_POST["age_limit_months"]);

    if (!empty($name) && !empty($description) && $age_limit_months > 0) {
        $sql = "INSERT INTO vaccinations (name, description, age_limit_months) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $age_limit_months);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Vaccination added successfully.";
        } else {
            $message = "Failed to add vaccination.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Vaccination</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h2 class="mb-4">Add Vaccination</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Vaccination Name:</label>
                    <input type="text" name="name" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" required class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label>Age Limit (in months):</label>
                    <input type="number" name="age_limit_months" required class="form-control">
                </div>
                <button type="submit" class="btn btn-success">Add Vaccination</button>
                <a class="btn btn-secondary ml-2" href="view_vaccinations.php">View Vaccinations</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
