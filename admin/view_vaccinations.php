<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $sql = "DELETE FROM vaccinations WHERE vaccination_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Vaccination deleted successfully.";
    } else {
        $message = "Failed to delete vaccination.";
    }
    mysqli_stmt_close($stmt);
}

$result = mysqli_query($conn, "SELECT * FROM vaccinations");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Vaccinations</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Vaccinations</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <a href="add_vaccination.php" class="btn btn-success mb-3">Add New Vaccination</a>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Age Limit (Months)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['vaccination_id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $row['age_limit_months'] ?></td>
                <td>
                    <a href="edit_vaccination.php?id=<?= $row['vaccination_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="?delete_id=<?= $row['vaccination_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this vaccination?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
