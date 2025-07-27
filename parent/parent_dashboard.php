<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION["id"];
$email = htmlspecialchars($_SESSION["email"]);

// Fetch children of this parent
$children = mysqli_query($conn, "SELECT * FROM children WHERE parent_id = $user_id ORDER BY created_at DESC");

// Track vaccinated children
$vaccinated_children = [];

if (mysqli_num_rows($children) > 0) {
    $child_ids = [];
    while ($child = mysqli_fetch_assoc($children)) {
        $child_ids[] = $child['child_id'];
    }

    $ids_str = implode(',', array_map('intval', $child_ids));
    $vacc_query = mysqli_query($conn, "SELECT DISTINCT kid_id FROM vaccination_records WHERE kid_id IN ($ids_str)");
    
    while ($row = mysqli_fetch_assoc($vacc_query)) {
        $vaccinated_children[$row['kid_id']] = true;
    }

    // Reset pointer
    mysqli_data_seek($children, 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Parent Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">Welcome, <?= $email ?></h2>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <a href="register_child.php" class="btn btn-success btn-block">Register Child</a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="view_children.php" class="btn btn-primary btn-block">View Children</a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="view_vaccinations.php" class="btn btn-info btn-block">View Vaccinations</a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="add_appointment.php" class="btn btn-warning btn-block">Schedule an Appointment</a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="view_appointments.php" class="btn btn-warning btn-block">View Appointments</a>
        </div>
    </div>

    <h4>Your Children</h4>

    <?php if (mysqli_num_rows($children) > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>DOB</th>
                    <th>Gender</th>
                    <th>City</th>
                    <th>Vaccination Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($child = mysqli_fetch_assoc($children)): ?>
                    <tr>
                        <td><?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></td>
                        <td><?= htmlspecialchars($child['date_of_birth']) ?></td>
                        <td><?= htmlspecialchars($child['gender']) ?></td>
                        <td><?= htmlspecialchars($child['city']) ?></td>
                        <td>
                            <?= isset($vaccinated_children[$child['child_id']]) ? 'Vaccinated' : 'Not Vaccinated' ?>
                        </td>
                        <td>
    <?php if (isset($vaccinated_children[$child['child_id']])): ?>
        <a href="generate_certificate.php?kid_id=<?= $child['child_id'] ?>" class="btn btn-success btn-sm">Generate Certificate</a>
    <?php else: ?>
        <span class="text-muted">No vaccination record</span>
    <?php endif; ?>
</td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have not registered any children yet.</p>
    <?php endif; ?>

</div>

</body>
</html>
