<?php
session_start();
include('config.php');

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// User status counts
$user_status_sql = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
$user_status_result = mysqli_query($conn, $user_status_sql);
$user_status_data = [];
while ($row = mysqli_fetch_assoc($user_status_result)) {
    $user_status_data[$row['status']] = $row['count'];
}

// User types distribution
$user_types_sql = "SELECT user_type, COUNT(*) as count FROM users WHERE status = 'approved' GROUP BY user_type";
$user_types_result = mysqli_query($conn, $user_types_sql);
$user_types_data = [];
$user_types_labels = [];
while ($row = mysqli_fetch_assoc($user_types_result)) {
    $user_types_labels[] = $row['user_type'] ?? 'Unknown';
    $user_types_data[] = $row['count'];
}

// Vaccinations count
$vaccination_count_sql = "SELECT COUNT(*) as total FROM vaccinations";
$vacc_result = mysqli_query($conn, $vaccination_count_sql);
$total_vaccinations = mysqli_fetch_assoc($vacc_result)['total'] ?? 0;

// Total children
$total_children_sql = "SELECT COUNT(*) as total FROM children";
$total_children_result = mysqli_query($conn, $total_children_sql);
$total_children = mysqli_fetch_assoc($total_children_result)['total'] ?? 0;

// Vaccinated vs Unvaccinated
$vaccinated_sql = "SELECT COUNT(DISTINCT kid_id) as total FROM vaccination_records";
$vaccinated_result = mysqli_query($conn, $vaccinated_sql);
$total_vaccinated = mysqli_fetch_assoc($vaccinated_result)['total'] ?? 0;
$total_unvaccinated = $total_children - $total_vaccinated;

// Age group distribution
$age_groups_sql = "
    SELECT 
        CASE 
            WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) <= 12 THEN '0-1 years'
            WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) <= 60 THEN '1-5 years'
            WHEN TIMESTAMPDIFF(MONTH, date_of_birth, CURDATE()) <= 144 THEN '5-12 years'
            ELSE '12+ years'
        END as age_group,
        COUNT(*) as count
    FROM children
    GROUP BY age_group
";
$age_groups_result = mysqli_query($conn, $age_groups_sql);
$age_groups_data = [];
$age_groups_labels = [];
while ($row = mysqli_fetch_assoc($age_groups_result)) {
    $age_groups_labels[] = $row['age_group'];
    $age_groups_data[] = $row['count'];
}

// Pending user approvals
$pending_users_sql = "SELECT id, username, email, phone, user_type FROM users WHERE status = 'pending'";
$pending_users_result = mysqli_query($conn, $pending_users_sql);

// Upcoming visit bookings
$upcoming_visits_sql = "
    SELECT vb.*, u.username, c.first_name, c.last_name, v.name AS vaccine
    FROM visit_bookings vb
    JOIN users u ON vb.user_id = u.id
    JOIN children c ON vb.kid_id = c.child_id
    JOIN vaccinations v ON vb.vaccination_id = v.vaccination_id
    WHERE vb.status IN ('Pending', 'Confirmed') AND vb.visit_date >= CURDATE()
    ORDER BY vb.visit_date ASC
    LIMIT 10
";
$upcoming_visits_result = mysqli_query($conn, $upcoming_visits_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        canvas { max-height: 300px; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .stat-card { min-height: 150px; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <h2 class="text-center text-primary mb-4">Admin Dashboard</h2>

    <!-- Dashboard Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success stat-card">
                <div class="card-body">
                    <h5 class="card-title">Approved Users</h5>
                    <p class="card-text display-4"><?= $user_status_data['approved'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info stat-card">
                <div class="card-body">
                    <h5 class="card-title">Total Vaccinations</h5>
                    <p class="card-text display-4"><?= $total_vaccinations ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-secondary stat-card">
                <div class="card-body">
                    <h5 class="card-title">Total Children</h5>
                    <p class="card-text display-4"><?= $total_children ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning stat-card">
                <div class="card-body">
                    <h5 class="card-title">Pending Approvals</h5>
                    <p class="card-text display-4"><?= $user_status_data['pending'] ?? 0 ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    Vaccination Coverage
                </div>
                <div class="card-body">
                    <canvas id="vaccinationPieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    User Types Distribution
                </div>
                <div class="card-body">
                    <canvas id="userTypesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Age Group Distribution -->
    <div class="card mb-5">
        <div class="card-header bg-dark text-white">
            Children Age Distribution
        </div>
        <div class="card-body">
            <canvas id="ageGroupsChart"></canvas>
        </div>
    </div>

    <!-- Pending User Approvals -->
    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            Pending User Approvals
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($pending_users_result) > 0): ?>
                <table class="table table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>User Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($pending_users_result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= htmlspecialchars($row['user_type']) ?></td>
                            <td>
                                <form method="POST" action="approve_user.php" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-3 alert alert-info">No users awaiting approval.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Visit Bookings -->
    <div class="card mb-5">
        <div class="card-header bg-info text-white">
            Upcoming Visit Bookings
        </div>
        <div class="card-body p-0">
            <?php if (mysqli_num_rows($upcoming_visits_result) > 0): ?>
                <table class="table table-striped table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Parent</th>
                            <th>Child</th>
                            <th>Vaccine</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($visit = mysqli_fetch_assoc($upcoming_visits_result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($visit['username']) ?></td>
                            <td><?= htmlspecialchars($visit['first_name'] . ' ' . $visit['last_name']) ?></td>
                            <td><?= htmlspecialchars($visit['vaccine']) ?></td>
                            <td><?= htmlspecialchars($visit['visit_date']) ?></td>
                            <td><?= htmlspecialchars($visit['visit_time']) ?></td>
                            <td>
                                <span class="badge badge-<?= $visit['status'] === 'Confirmed' ? 'success' : 'warning' ?>">
                                    <?= htmlspecialchars($visit['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-3 alert alert-info">No upcoming visits scheduled.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const vaccinationPieChart = document.getElementById('vaccinationPieChart').getContext('2d');
new Chart(vaccinationPieChart, {
    type: 'pie',
    data: {
        labels: ['Vaccinated', 'Unvaccinated'],
        datasets: [{
            data: [<?= $total_vaccinated ?>, <?= $total_unvaccinated ?>],
            backgroundColor: ['#28a745', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: {
                display: true,
                text: 'Vaccinated vs Unvaccinated Children'
            }
        }
    }
});

const userTypesChart = document.getElementById('userTypesChart').getContext('2d');
new Chart(userTypesChart, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($user_types_labels) ?>,
        datasets: [{
            data: <?= json_encode($user_types_data) ?>,
            backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: {
                display: true,
                text: 'User Types Distribution'
            }
        }
    }
});

const ageGroupsChart = document.getElementById('ageGroupsChart').getContext('2d');
new Chart(ageGroupsChart, {
    type: 'bar',
    data: {
        labels: <?= json_encode($age_groups_labels) ?>,
        datasets: [{
            label: 'Number of Children',
            data: <?= json_encode($age_groups_data) ?>,
            backgroundColor: '#17a2b8'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Children'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Age Groups'
                }
            }
        },
        plugins: {
            title: {
                display: true,
                text: 'Children Age Distribution'
            }
        }
    }
});
</script>

</body>
</html>