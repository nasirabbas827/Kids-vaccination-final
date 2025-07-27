<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch bookings with child and user info
$sql = "SELECT vb.*, 
               c.first_name AS child_name, 
               u.username AS parent_name, 
               w.username AS worker_name, 
               v.name AS vaccination_name
        FROM visit_bookings vb
        JOIN children c ON vb.kid_id = c.child_id
        JOIN users u ON vb.user_id = u.id
        JOIN vaccinations v ON vb.vaccination_id = v.vaccination_id
        LEFT JOIN users w ON vb.worker_id = w.id
        ORDER BY vb.created_at DESC";

$result = mysqli_query($conn, $sql);

$result = mysqli_query($conn, $sql);

// Fetch workers for dropdown
$workers = [];
$wsql = "SELECT id, username FROM users WHERE user_type = 'Worker'";
$wres = mysqli_query($conn, $wsql);
while ($wrow = mysqli_fetch_assoc($wres)) {
    $workers[] = $wrow;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View All Bookings</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="container mt-5">
    <h2 class="mb-4">All Visit Bookings</h2>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Parent</th>
                <th>Child</th>
                <th>Vaccine Name</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Worker</th>
                <th>Feedback</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['parent_name']) ?></td>
                <td><?= htmlspecialchars($row['child_name']) ?></td>
                <td><?= htmlspecialchars($row['vaccination_name']) ?></td>
                <td><?= $row['visit_date'] ?></td>
                <td><?= $row['visit_time'] ?></td>
                <td><?= $row['status'] ?></td>
                <td><?= $row['worker_name'] ?? 'Not Assigned' ?></td>
                <td><?= htmlspecialchars($row['feedback']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?= $row['booking_id'] ?>">Edit</button>
                </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $row['booking_id'] ?>" tabindex="-1" role="dialog">
              <div class="modal-dialog modal-lg">
                <form method="post" action="update_booking_admin.php">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Update Booking #<?= $row['booking_id'] ?></h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      <div class="modal-body">
                          <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">

                          <div class="form-group">
                              <label>Status</label>
                              <select name="status" class="form-control" required>
                                  <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                  <option value="Confirmed" <?= $row['status'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                  <option value="Rejected" <?= $row['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                              </select>
                          </div>

                          <div class="form-group">
                              <label>Assign Health Worker</label>
                              <select name="worker_id" class="form-control">
                                  <option value="">-- None --</option>
                                  <?php foreach ($workers as $worker): ?>
                                      <option value="<?= $worker['id'] ?>" <?= $row['worker_id'] == $worker['id'] ? 'selected' : '' ?>>
                                          <?= htmlspecialchars($worker['username']) ?>
                                      </option>
                                  <?php endforeach; ?>
                              </select>
                          </div>

                          <div class="form-group">
                              <label>Feedback</label>
                              <textarea name="feedback" class="form-control"><?= htmlspecialchars($row['feedback']) ?></textarea>
                          </div>
                      </div>
                      <div class="modal-footer">
                          <button type="submit" class="btn btn-success">Save Changes</button>
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                </form>
              </div>
            </div>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
