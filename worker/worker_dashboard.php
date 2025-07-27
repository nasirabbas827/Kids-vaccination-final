<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$worker_id = $_SESSION["id"];
$email = htmlspecialchars($_SESSION["email"]);

// Handle form submission to add vaccination record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_record'])) {
    $kid_id = $_POST['kid_id'];
    $vaccination_id = $_POST['vaccination_id'];
    $booking_id = $_POST['booking_id'];
    $date_given = date('Y-m-d');

    $insert = "INSERT INTO vaccination_records (kid_id, vaccination_id, worker_id, date_given) 
               VALUES ('$kid_id', '$vaccination_id', '$worker_id', '$date_given')";
    mysqli_query($conn, $insert);

    // Update booking status
    mysqli_query($conn, "UPDATE visit_bookings SET status = 'Completed' WHERE booking_id = '$booking_id'");

    $feedback = "Vaccination completed successfully.";
}

// Fetch children
$children = mysqli_query($conn, "SELECT * FROM children ORDER BY created_at DESC");

// Fetch vaccinations
$vaccinations = mysqli_query($conn, "SELECT * FROM vaccinations ORDER BY name");

// Fetch visit bookings assigned to this worker
$bookings = mysqli_query($conn, "
    SELECT vb.*, c.first_name AS child_name, v.name AS vaccination_name
    FROM visit_bookings vb
    JOIN children c ON vb.kid_id = c.child_id
    JOIN vaccinations v ON vb.vaccination_id = v.vaccination_id
    WHERE vb.worker_id = '$worker_id'
    ORDER BY vb.created_at DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Worker Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">Welcome, <?= $email ?></h2>

    <?php if (!empty($feedback)): ?>
        <div class="alert alert-success"><?= $feedback ?></div>
    <?php endif; ?>


    <h4>My Assigned Visit Bookings</h4>
    <?php if (mysqli_num_rows($bookings) > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Child Name</th>
                    <th>Vaccination</th>
                    <th>Visit Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($bookings)): ?>
                    <tr>
                        <td><a href="child_details.php?child_id=<?= $row['kid_id'] ?>" class="text-primary"><?= htmlspecialchars($row['child_name']) ?></a></td>
                        <td><?= htmlspecialchars($row['vaccination_name']) ?></td>
                        <td><?= htmlspecialchars($row['visit_date']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <?php if ($row['status'] !== 'Completed'): ?>
                                <form method="post" class="form-inline">
                                    <input type="hidden" name="kid_id" value="<?= $row['kid_id'] ?>">
                                    <input type="hidden" name="vaccination_id" value="<?= $row['vaccination_id'] ?>">
                                    <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                                    <button type="submit" name="submit_record" class="btn btn-sm btn-success">Add Record</button>
                                </form>
                            <?php else: ?>
                                <span class="text-success">Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No assigned visit bookings found.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
