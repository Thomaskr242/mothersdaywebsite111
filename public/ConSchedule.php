<?php
require_once "../includes/contractor_auth.php";
require_once "../includes/db.php";
require_once "../includes/dashboard_data.php";

$contractorId = (int) $_SESSION["user_id"];
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $availableDate = trim($_POST["available_date"] ?? "");
    $startTime = trim($_POST["start_time"] ?? "");
    $endTime = trim($_POST["end_time"] ?? "");

    if ($availableDate === "" || $startTime === "" || $endTime === "") {
        $error = "Please complete all availability fields.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO contractor_availability (contractor_id, available_date, start_time, end_time, is_booked)
                 VALUES (?, ?, ?, ?, 0)"
            );
            $stmt->execute([$contractorId, $availableDate, $startTime, $endTime]);
            $success = "Availability added successfully.";
        } catch (Throwable $e) {
            $error = "Availability could not be saved. Make sure contractor_availability exists.";
        }
    }
}

$appointments = fetchContractorAppointments($pdo, $contractorId);
$availability = fetchAvailability($pdo, $contractorId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contractor Availability</title>
    <link rel="stylesheet" href="../views/styles.css?v=3.0">
</head>
<body>
<div class="dash-container contractor-theme">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-mark">OnSight</span>
        </div>

        <nav class="sidebar-links">
            <a href="welcomeCon.php">Dashboard</a>
            <a href="ConSchedule.php" class="is-active">Availability</a>
            <a href="ConInbox.php">Inbox</a>
            <a href="ConArchive.php">Archive</a>
            <a href="ConSettings.php">Settings</a>
            <a href="logout.php">Sign Out</a>
        </nav>
    </aside>

    <main class="dash-main">
        <header class="dash-top">
            <div>
                <p class="eyebrow">Contractor Calendar</p>
                <h1>Set the time slots customers can request.</h1>
            </div>
            <div class="profile-icon">T</div>
        </header>

        <?php if ($success !== ""): ?>
            <div class="notice success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error !== ""): ?>
            <div class="notice error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="schedule-layout">
            <div class="schedule-panel">
                <div class="section-head">
                    <h3>Add Availability</h3>
                    <span>Publish new meeting windows</span>
                </div>

                <form method="POST" class="booking-form">
                    <label for="available_date">Date</label>
                    <input type="date" name="available_date" id="available_date" required>

                    <label for="start_time">Start Time</label>
                    <input type="time" name="start_time" id="start_time" required>

                    <label for="end_time">End Time</label>
                    <input type="time" name="end_time" id="end_time" required>

                    <button type="submit" class="primary-btn">Save Availability</button>
                </form>
            </div>

            <div class="schedule-panel">
                <div class="section-head">
                    <h3>Published Slots</h3>
                    <span>What customers can see right now</span>
                </div>

                <?php if (!$availability): ?>
                    <p class="empty-state">No available times published yet.</p>
                <?php else: ?>
                    <div class="availability-list">
                        <?php foreach ($availability as $slot): ?>
                            <div class="availability-item">
                                <strong><?= date("D, M j", strtotime($slot["available_date"])) ?></strong>
                                <span><?= date("g:i A", strtotime($slot["start_time"])) ?> - <?= date("g:i A", strtotime($slot["end_time"])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="schedule-panel appointment-list">
            <div class="section-head">
                <h3>Incoming Meeting Requests</h3>
                <span>Latest scheduled customer calls</span>
            </div>

            <?php if (!$appointments): ?>
                <p class="empty-state">No appointment requests yet.</p>
            <?php else: ?>
                <?php foreach ($appointments as $appointment): ?>
                    <div class="appointment-item">
                        <div>
                            <strong><?= htmlspecialchars($appointment["customer_name"]) ?></strong>
                            <span><?= date("M j, Y g:i A", strtotime($appointment["appointment_date"])) ?></span>
                        </div>
                        <div class="status-badge <?= htmlspecialchars(strtolower($appointment["status"])) ?>">
                            <?= htmlspecialchars(ucfirst($appointment["status"])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
