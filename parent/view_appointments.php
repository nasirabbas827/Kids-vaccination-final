<?php
session_start();
include('config.php');

if (!isset($_SESSION["id"])) {
    header("location: ../index.php");
    exit;
}

$parent_id = $_SESSION["id"];

// Fetch appointments
$sql = "SELECT vb.*, 
               c.child_id, c.first_name AS child_name,
               v.vaccination_id, v.name AS vaccination_name,
               u.username AS worker_name
        FROM visit_bookings vb
        JOIN children c ON vb.kid_id = c.child_id
        JOIN vaccinations v ON vb.vaccination_id = v.vaccination_id
        LEFT JOIN users u ON vb.worker_id = u.id
        WHERE vb.user_id = ?
        ORDER BY vb.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all vaccinations for dropdown
$vaccinations = $conn->query("SELECT vaccination_id, name FROM vaccinations");

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Appointments</title>
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5 mb-5">

<h2 class="mb-4">My Appointments</h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Child</th>
            <th>Vaccination</th>
            <th>Visit Date</th>
            <th>Visit Time</th>
            <th>Worker</th>
            <th>Status</th>
            <th>Feedback</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['child_name']) ?></td>
            <td><?= htmlspecialchars($row['vaccination_name']) ?></td>
            <td><?= $row['visit_date'] ?></td>
            <td><?= $row['visit_time'] ?></td>
            <td><?= $row['worker_name'] ?? 'Not Assigned' ?></td>
            <td><?= $row['status'] ?></td>
            <td><?= $row['feedback'] ?? 'N/A' ?></td>
            <td>
                <?php if ($row['status'] === 'Pending') { ?>
                    <!-- Edit Button -->
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['booking_id'] ?>">Edit</button>

                    <!-- Delete Button -->
                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['booking_id'] ?>">Delete</button>
                <?php } else { echo '-'; } ?>
            </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?= $row['booking_id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="edit_id" value="<?= $row['booking_id'] ?>">

                    <div class="mb-3">
                        <label>Vaccination</label>
                        <select name="vaccination_id" class="form-select" required>
                            <?php
                            $vaccinations->data_seek(0); // reset pointer
                            while ($v = $vaccinations->fetch_assoc()) {
                                $selected = $v['vaccination_id'] == $row['vaccination_id'] ? 'selected' : '';
                                echo "<option value='{$v['vaccination_id']}' $selected>{$v['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Visit Date</label>
                        <input type="date" name="visit_date" class="form-control" value="<?= $row['visit_date'] ?>" required min="<?= date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label>Visit Time</label>
                        <input type="time" name="visit_time" class="form-control" value="<?= $row['visit_time'] ?>" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="update_appointment" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </div>
            </form>
          </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal<?= $row['booking_id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Delete Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p>Are you sure you want to delete this appointment?</p>
                    <input type="hidden" name="delete_id" value="<?= $row['booking_id'] ?>">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="delete_appointment" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </div>
            </form>
          </div>
        </div>
    <?php } ?>
    </tbody>
</table>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
// Handle update
if (isset($_POST['update_appointment'])) {
    $booking_id = $_POST['edit_id'];
    $vaccination_id = $_POST['vaccination_id'];
    $visit_date = $_POST['visit_date'];
    $visit_time = $_POST['visit_time'];

    $stmt = $conn->prepare("UPDATE visit_bookings SET vaccination_id=?, visit_date=?, visit_time=? WHERE booking_id=? AND user_id=?");
    $stmt->bind_param("issii", $vaccination_id, $visit_date, $visit_time, $booking_id, $parent_id);
    $stmt->execute();
    echo "<script>location.href='view_appointments.php';</script>";
}

// Handle delete
if (isset($_POST['delete_appointment'])) {
    $booking_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM visit_bookings WHERE booking_id=? AND user_id=?");
    $stmt->bind_param("ii", $booking_id, $parent_id);
    $stmt->execute();
    echo "<script>location.href='view_appointments.php';</script>";
}
?>
