<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch vaccination list
$vaccinations = [];
$result = mysqli_query($conn, "SELECT vaccination_id, name FROM vaccinations");
while ($row = mysqli_fetch_assoc($result)) {
    $vaccinations[] = $row;
}

// Process report
$report_type = $_POST['report_type'] ?? '';
$selected_vaccine = $_POST['vaccine_id'] ?? '';
$report_data = [];

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($report_type)) {
    $query = "";

    switch ($report_type) {
        case "all":
            $query = "
                SELECT c.first_name, c.last_name, c.city, v.name AS vaccine, r.date_given
                FROM vaccination_records r
                JOIN children c ON r.kid_id = c.child_id
                JOIN vaccinations v ON r.vaccination_id = v.vaccination_id
            ";
            break;
        case "city":
            $query = "
                SELECT c.first_name, c.last_name, c.city, COUNT(*) AS total
                FROM vaccination_records r
                JOIN children c ON r.kid_id = c.child_id
                GROUP BY c.city
            ";
            break;
        case "weekly":
            $query = "
                SELECT c.first_name, c.last_name, r.date_given, v.name AS vaccine
                FROM vaccination_records r
                JOIN children c ON r.kid_id = c.child_id
                JOIN vaccinations v ON r.vaccination_id = v.vaccination_id
                WHERE YEARWEEK(r.date_given) = YEARWEEK(CURDATE())
            ";
            break;
        case "monthly":
            $query = "
                SELECT c.first_name, c.last_name, r.date_given, v.name AS vaccine
                FROM vaccination_records r
                JOIN children c ON r.kid_id = c.child_id
                JOIN vaccinations v ON r.vaccination_id = v.vaccination_id
                WHERE MONTH(r.date_given) = MONTH(CURDATE())
                  AND YEAR(r.date_given) = YEAR(CURDATE())
            ";
            break;
        case "by_vaccine":
            if (!empty($selected_vaccine)) {
                $query = "
                    SELECT c.first_name, c.last_name, v.name AS vaccine, r.date_given
                    FROM vaccination_records r
                    JOIN children c ON r.kid_id = c.child_id
                    JOIN vaccinations v ON r.vaccination_id = v.vaccination_id
                    WHERE r.vaccination_id = " . intval($selected_vaccine);
            }
            break;
    }

    if (!empty($query)) {
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $report_data[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Reports</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css" />

</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2>Generate Vaccination Reports</h2>
    <form method="post" class="mb-4">
        <div class="form-group">
            <label for="report_type">Select Report Type:</label>
            <select name="report_type" id="report_type" class="form-control" required>
                <option value="">--Select--</option>
                <option value="all">All Vaccinated Children</option>
                <option value="city">Vaccinated Children (Area-wise)</option>
                <option value="weekly">Vaccinated This Week</option>
                <option value="monthly">Vaccinated This Month</option>
                <option value="by_vaccine">Children Vaccinated by Specific Vaccine</option>
            </select>
        </div>

        <div class="form-group" id="vaccine_select" style="display: none;">
            <label for="vaccine_id">Select Vaccination:</label>
            <select name="vaccine_id" id="vaccine_id" class="form-control">
                <option value="">--Select--</option>
                <?php foreach ($vaccinations as $v): ?>
                    <option value="<?= $v['vaccination_id'] ?>"><?= htmlspecialchars($v['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Generate</button>
        <button onclick="window.print()" class="btn btn-dark">Print Report</button>
    </form>

    <?php if (!empty($report_data)): ?>
        <h4 class="mb-3">Report Results:</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <?php foreach (array_keys($report_data[0]) as $column): ?>
                        <th><?= ucfirst(str_replace('_', ' ', $column)) ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($report_data as $row): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?= htmlspecialchars($value) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
        <div class="alert alert-warning">No data found for the selected report.</div>
    <?php endif; ?>
</div>

<script>
    document.getElementById("report_type").addEventListener("change", function () {
        const showVaccine = this.value === "by_vaccine";
        document.getElementById("vaccine_select").style.display = showVaccine ? "block" : "none";
    });
</script>

</body>
</html>
