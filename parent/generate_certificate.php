<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$user_id = $_SESSION["id"];

$record_kid_id = isset($_GET['kid_id']) ? intval($_GET['kid_id']) : 0;

if ($record_kid_id <= 0) {
    die("Invalid or missing kid_id.");
}

// Validate that the kid belongs to the logged-in parent
$child_query = "
    SELECT c.*, u.username AS parent_name, u.email, u.phone, u.age, u.bio, u.profile_picture 
    FROM children c
    INNER JOIN users u ON c.parent_id = u.id
    WHERE c.child_id = ? AND c.parent_id = ?
";
$stmt = $conn->prepare($child_query);
$stmt->bind_param("ii", $record_kid_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$child = $result->fetch_assoc();

if (!$child) {
    die("Child not found or does not belong to the current parent.");
}

// Fetch vaccination records
$vac_query = "
    SELECT vr.*, v.name AS vaccination_name, u.username AS worker_name 
    FROM vaccination_records vr
    INNER JOIN vaccinations v ON vr.vaccination_id = v.vaccination_id
    LEFT JOIN users u ON vr.worker_id = u.id
    WHERE vr.kid_id = ?
    ORDER BY vr.date_given ASC
";
$vac_stmt = $conn->prepare($vac_query);
$vac_stmt->bind_param("i", $record_kid_id);
$vac_stmt->execute();
$vac_records = $vac_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vaccination Certificate</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .certificate-container {
            border: 2px solid #333;
            padding: 30px;
            margin-top: 50px;
            background: #fdfdfd;
        }
        .section-title {
            border-bottom: 2px solid #007bff;
            margin-bottom: 20px;
            padding-bottom: 5px;
            font-weight: bold;
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
<div class="container certificate-container">
    <h2 class="text-center">Vaccination Certificate</h2>

    <div class="mt-4">
        <div class="section-title">Parent Details</div>
        <p><strong>Name:</strong> <?= htmlspecialchars($child['parent_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($child['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($child['phone']) ?></p>
        <p><strong>Age:</strong> <?= htmlspecialchars($child['age']) ?></p>
        <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($child['bio'])) ?></p>
        <?php if (!empty($child['profile_picture'])): ?>
            <img src="<?= htmlspecialchars($child['profile_picture']) ?>" width="150" class="img-thumbnail mb-2">
        <?php endif; ?>
    </div>

    <div class="mt-4">
        <div class="section-title">Child Details</div>
        <p><strong>Name:</strong> <?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></p>
        <p><strong>DOB:</strong> <?= htmlspecialchars($child['date_of_birth']) ?></p>
        <p><strong>Gender:</strong> <?= htmlspecialchars($child['gender']) ?></p>
        <p><strong>City:</strong> <?= htmlspecialchars($child['city']) ?></p>
    </div>

    <div class="mt-4">
        <div class="section-title">Vaccination Record</div>
        <?php if ($vac_records->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Vaccination</th>
                        <th>Date Given</th>
                        <th>Given By (Worker)</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; while ($row = $vac_records->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['vaccination_name']) ?></td>
                        <td><?= htmlspecialchars($row['date_given']) ?></td>
                        <td><?= htmlspecialchars($row['worker_name']) ?: 'N/A' ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No vaccination records found for this child.</p>
        <?php endif; ?>
    </div>

    <div class="text-right mt-4">
        <button onclick="window.print()" class="btn btn-primary">Print Certificate</button>
    </div>
</div>
</body>
</html>
