<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['child_id'])) {
    echo "Invalid request.";
    exit;
}

$child_id = intval($_GET['child_id']);

// Fetch child info
$child_query = "
    SELECT c.*, u.username, u.email, u.phone, u.age, u.bio, u.profile_picture
    FROM children c
    JOIN users u ON c.parent_id = u.id
    WHERE c.child_id = $child_id
";
$result = mysqli_query($conn, $child_query);
$child = mysqli_fetch_assoc($result);

if (!$child) {
    echo "Child not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Child & Parent Details</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h3, h4 {
            font-weight: 600;
        }
        .img-thumbnail {
            max-width: 150px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Child & Parent Information</h3>
    <div class="row">
        <!-- Parent Details Card -->
        <div class="col-md-6 mb-4">
            <div class="card p-4">
                <h4 class="mb-3">Parent Details</h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($child['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($child['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($child['phone']) ?></p>
                <p><strong>Age:</strong> <?= htmlspecialchars($child['age']) ?></p>
                <p><strong>Bio:</strong> <?= nl2br(htmlspecialchars($child['bio'])) ?></p>
                <?php if ($child['profile_picture']): ?>
                    <p>
                        <strong>Profile Picture:</strong><br>
                        <img src="../uploads/<?= htmlspecialchars($child['profile_picture']) ?>" class="img-thumbnail mt-2">
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Child Details Card -->
        <div class="col-md-6 mb-4">
            <div class="card p-4">
                <h4 class="mb-3">Child Details</h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) ?></p>
                <p><strong>Date of Birth:</strong> <?= htmlspecialchars($child['date_of_birth']) ?></p>
                <p><strong>Gender:</strong> <?= htmlspecialchars($child['gender']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($child['address']) ?></p>
                <p><strong>City:</strong> <?= htmlspecialchars($child['city']) ?></p>
                <p><strong>Province:</strong> <?= htmlspecialchars($child['province']) ?></p>
                <p><strong>Postal Code:</strong> <?= htmlspecialchars($child['postal_code']) ?></p>
                <p><strong>Special Notes:</strong> <?= nl2br(htmlspecialchars($child['special_notes'])) ?></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
