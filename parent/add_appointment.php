<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$parent_id = $_SESSION["id"];
$message = "";

// Fetch children of this parent
$children_query = "SELECT child_id, first_name, last_name FROM children WHERE parent_id = $parent_id";
$children_result = mysqli_query($conn, $children_query);

// Fetch all vaccinations
$vaccination_query = "SELECT vaccination_id, name FROM vaccinations";
$vaccination_result = mysqli_query($conn, $vaccination_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kid_id = $_POST['kid_id'];
    $vaccination_id = $_POST['vaccination_id'];
    $visit_date = $_POST['visit_date'];
    $visit_time = $_POST['visit_time'];

    $insert = "INSERT INTO visit_bookings (user_id, kid_id, vaccination_id, worker_id, visit_date, visit_time, status, created_at)
               VALUES (?, ?, ?, NULL, ?, ?, 'Pending', NOW())";
    $stmt = mysqli_prepare($conn, $insert);
    mysqli_stmt_bind_param($stmt, "iiiss", $parent_id, $kid_id, $vaccination_id, $visit_date, $visit_time);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Appointment successfully booked and marked as pending.";
    } else {
        $message = "Error booking appointment: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Vaccination Appointment</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h2>Book Vaccination Appointment</h2>

            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>Select Child:</label>
                    <select name="kid_id" class="form-control" required>
                        <option value="">-- Select Child --</option>
                        <?php while ($child = mysqli_fetch_assoc($children_result)): ?>
                            <option value="<?= $child['child_id'] ?>">
                                <?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Select Vaccination:</label>
                    <select name="vaccination_id" class="form-control" required>
                        <option value="">-- Select Vaccination --</option>
                        <?php while ($vaccine = mysqli_fetch_assoc($vaccination_result)): ?>
                            <option value="<?= $vaccine['vaccination_id'] ?>">
                                <?= htmlspecialchars($vaccine['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Preferred Visit Date:</label>
                    <input type="date" name="visit_date" class="form-control" required min="<?= date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Preferred Visit Time:</label>
                    <input type="time" name="visit_time" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">Book Appointment</button>
                <a href="view_appointments.php" class="btn btn-secondary ml-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
