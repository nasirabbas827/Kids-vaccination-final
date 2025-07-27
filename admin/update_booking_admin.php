<?php
include('config.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION["usertype"]) && $_SESSION["usertype"] === "admin") {
    $booking_id = intval($_POST["booking_id"]);
    $status = $_POST["status"];
    $feedback = trim($_POST["feedback"]);
    $worker_id = !empty($_POST["worker_id"]) ? intval($_POST["worker_id"]) : NULL;

    $sql = "UPDATE visit_bookings SET status=?, feedback=?, worker_id=? WHERE booking_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $status, $feedback, $worker_id, $booking_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: admin_view_bookings.php?updated=1");
    } else {
        echo "Update failed.";
    }

    mysqli_stmt_close($stmt);
} else {
    header("Location: admin_login.php");
    exit;
}
?>
