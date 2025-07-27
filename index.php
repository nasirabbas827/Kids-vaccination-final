<?php
session_start();
include('config.php');

// Total children
$total_children_sql = "SELECT COUNT(*) as total FROM children";
$total_children_result = mysqli_query($conn, $total_children_sql);
$total_children = mysqli_fetch_assoc($total_children_result)['total'] ?? 0;

// Vaccinated vs Unvaccinated
$vaccinated_sql = "SELECT COUNT(DISTINCT kid_id) as total FROM vaccination_records";
$vaccinated_result = mysqli_query($conn, $vaccinated_sql);
$total_vaccinated = mysqli_fetch_assoc($vaccinated_result)['total'] ?? 0;
$total_unvaccinated = $total_children - $total_vaccinated;

// Available vaccinations
$vaccinations_sql = "SELECT * FROM vaccinations ORDER BY age_limit_months ASC LIMIT 15";
$vaccinations_result = mysqli_query($conn, $vaccinations_sql);

// Dummy data for vaccination status chart
$status_data = [
    'Vaccinated' => $total_vaccinated,
    'Unvaccinated' => $total_unvaccinated,
    'Total Children' => $total_children,
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kids Vaccination Booking System (KVBS)</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #007bff;
            --secondary: #6c757d;
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
            --accent: #ff6f61;
            --highlight: #6f42c1;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light) 0%, #e0e7ff 100%);
            color: var(--dark);
        }
        .navbar {
            background-color: var(--dark);
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .navbar-brand:hover, .nav-link:hover {
            color: var(--accent) !important;
        }
        .hero-section {
            height: 600px;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)),  url('./images/hotel.jpg') center/cover no-repeat;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: 0 0 30px 30px;
            animation: fadeIn 1.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .hero-section .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
            padding: 12px 30px;
            font-size: 1.2rem;
            transition: transform 0.3s, background-color 0.3s;
        }
        .hero-section .btn-primary:hover {
            background-color: var(--highlight);
            border-color: var(--highlight);
            transform: scale(1.05);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: white;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .card-img-top {
            height: 220px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .search-form {
            background-color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            margin-bottom: 40px;
            border-left: 5px solid var(--accent);
        }
        .section {
            padding: 80px 0;
        }
        .section-title {
            color: var(--highlight);
            margin-bottom: 40px;
            font-weight: 700;
            position: relative;
        }
        .section-title::after {
            content: '';
            width: 60px;
            height: 4px;
            background-color: var(--accent);
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
        .feature-icon {
            font-size: 3em;
            color: var(--accent);
            margin-bottom: 20px;
            transition: color 0.3s;
        }
        .card:hover .feature-icon {
            color: var(--highlight);
        }
        .chart-container {
            max-width: 450px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        footer {
            background-color: var(--dark);
            color: white;
            padding: 30px 0;
            border-top: 4px solid var(--accent);
        }
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        .btn-success:hover {
            background-color: var(--highlight);
            border-color: var(--highlight);
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 8px rgba(255, 111, 97, 0.3);
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="display-4 fw-bold">Kids Vaccination Booking System (KVBS)</h1>
        <p class="lead mb-4">Book Vaccinations and Track Your Child's Health</p>
        <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
            <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt me-2"></i>Login to Book</a>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-primary"><i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard</a>
        <?php endif; ?>
    </div>
    <!-- About Section -->
    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">About KVBS</h2>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="lead">The Kids Vaccination Booking System (KVBS) is designed to simplify the process of scheduling and tracking vaccinations for children. Parents can register their children, book vaccination appointments, and monitor vaccination records seamlessly.</p>
                    <p>Our mission is to ensure every child receives timely vaccinations through an easy-to-use platform, supporting public health initiatives and parental peace of mind.</p>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h4 class="text-center mb-4">Vaccination Status Overview</h4>
                        <canvas id="vaccinationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="section">
        <div class="container">
            <h2 class="text-center section-title">Key Features</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-syringe feature-icon"></i>
                            <h5 class="card-title">Book Vaccinations</h5>
                            <p class="card-text">Schedule vaccination appointments for your children with ease.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-clipboard-list feature-icon"></i>
                            <h5 class="card-title">Track Records</h5>
                            <p class="card-text">View and manage your child's vaccination history in one place.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-user-check feature-icon"></i>
                            <h5 class="card-title">Parent Portal</h5>
                            <p class="card-text">Secure account management for parents to oversee their children's health.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vaccinations Section -->
    <div class="section bg-light">
        <div class="container">
            <h2 class="text-center section-title">Available Vaccinations</h2>
            <?php if (mysqli_num_rows($vaccinations_result) > 0): ?>
                <div class="row">
                    <?php while ($vaccine = mysqli_fetch_assoc($vaccinations_result)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($vaccine['name']) ?></h5>
                                    <p class="card-text">
                                        <strong>Description:</strong> <?= htmlspecialchars($vaccine['description']) ?><br>
                                        <strong>Age Limit:</strong> <?= htmlspecialchars($vaccine['age_limit_months']) ?> months
                                    </p>
                                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                                        <a href="book_vaccination.php?vaccination_id=<?= $vaccine['vaccination_id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-calendar-plus me-2"></i>Book Now</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">No vaccinations available at the moment.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Call to Action Section -->
    <div class="section">
        <div class="container text-center">
            <h2 class="section-title">Join KVBS Today</h2>
            <p class="lead mb-4">Register now to start managing your child's vaccinations and stay updated with their health records.</p>
            <a href="register.php" class="btn btn-success btn-lg"><i class="fas fa-user-plus me-2"></i>Sign Up Now</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© 2025 Kids Vaccination Booking System (KVBS). All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Vaccination Status Pie Chart
        const ctx = document.getElementById('vaccinationChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Vaccinated', 'Unvaccinated', 'Total Children'],
                datasets: [{
                    data: [
                        <?= $status_data['Vaccinated'] ?>,
                        <?= $status_data['Unvaccinated'] ?>,
                        <?= $status_data['Total Children'] ?>,
                    ],
                    backgroundColor: ['#28a745', '#dc3545', '#6c757d'],
                    borderColor: 'rgba(255,255,255,0.8)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 14,
                                family: 'Segoe UI'
                            },
                            color: 'var(--dark)'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'var(--dark)',
                        titleFont: { size: 16 },
                        bodyFont: { size: 14 }
                    }
                }
            }
        });
    </script>
</body>
</html>