<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$message = "";
$id = intval($_GET['id']);

// Fetch current data
$sql = "SELECT * FROM vaccinations WHERE vaccination_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $age_limit_months = intval($_POST["age_limit_months"]);

    if (!empty($name) && !empty($description) && $age_limit_months > 0) {
        $sql = "UPDATE vaccinations SET name = ?, description = ?, age_limit_months = ? WHERE vaccination_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssii", $name, $description, $age_limit_months, $id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Vaccination updated successfully.";
        } else {
            $message = "Update failed.";
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
    <title>Edit Vaccination</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h2 class="mb-4">Edit Vaccination</h2>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Vaccination Name:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($data['name']) ?>" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" required class="form-control"><?= htmlspecialchars($data['description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Age Limit (in months):</label>
                    <input type="number" name="age_limit_months" value="<?= $data['age_limit_months'] ?>" required class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a class="btn btn-secondary ml-2" href="view_vaccinations.php">Back</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
