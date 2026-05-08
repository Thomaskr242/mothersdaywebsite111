<?php
require_once "../includes/contractor_auth.php";
require_once "../includes/db.php";
require_once "../includes/dashboard_data.php";

$contractorId = (int) $_SESSION["user_id"];
$appointments = fetchContractorAppointments($pdo, $contractorId);
$metrics = fetchContractorMetrics($pdo, $contractorId);
$availability = fetchAvailability($pdo, $contractorId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contractor Dashboard</title>
    <link rel="stylesheet" href="../views/styles.css?v=3.0">
</head>
<body>
<div class="dash-container contractor-theme">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-mark">OnSight</span>
        </div>

        <nav class="sidebar-links">
            <a href="welcomeCon.php" class="is-active">Dashboard</a>
            <a href="ConSchedule.php">Availability</a>
            <a href="ConInbox.php">Inbox</a>
            <a href="ConArchive.php">Archive</a>
            <a href="ConSettings.php">Settings</a>
            <a href="logout.php">Sign Out</a>
        </nav>
    </aside>

    <main class="dash-main">
        <header class="dash-top">
            <div>
                <p class="eyebrow">Contractor Dashboard</p>
                <h1>Welcome <strong><?= htmlspecialchars($_SESSION["full_name"] ?? "Contractor") ?></strong>!</h1>
            </div>
            <div class="profile-icon">T</div>
        </header>

        <section class="dash-cards">
            <a href="ConSchedule.php" class="card highlight">
                <span class="card-label">Upcoming Jobs</span>
                <strong><?= (int) $metrics["upcoming_jobs"] ?></strong>
            </a>
            <div class="card">
                <span class="card-label">Pending Requests</span>
                <strong><?= (int) $metrics["pending_requests"] ?></strong>
            </div>
            <div class="card">
                <span class="card-label">Confirmed Jobs</span>
                <strong><?= (int) $metrics["confirmed_jobs"] ?></strong>
            </div>
            <div class="card">
                <span class="card-label">Month Revenue</span>
                <strong>$<?= number_format((float) $metrics["monthly_revenue"], 0) ?></strong>
            </div>
        </section>

        <section class="dash-row">
            <div class="contractors-box">
                <div class="section-head">
                    <h3>Next Appointments</h3>
                    <span>Your nearest customer meetings</span>
                </div>

                <?php if (!$appointments): ?>
                    <p class="empty-state">No customer meetings yet.</p>
                <?php else: ?>
                    <?php foreach ($appointments as $appointment): ?>
                        <div class="contractor-item static-card">
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
            </div>

            <div class="search-box">
                <div class="section-head">
                    <h3>Your Availability</h3>
                    <span>Open slots customers can request</span>
                </div>

                <?php if (!$availability): ?>
                    <p class="empty-state">No open slots yet.</p>
                <?php else: ?>
                    <div class="availability-list">
                        <?php foreach ($availability as $slot): ?>
                            <div class="availability-item">
                                <strong><?= date("M j", strtotime($slot["available_date"])) ?></strong>
                                <span><?= date("g:i A", strtotime($slot["start_time"])) ?> - <?= date("g:i A", strtotime($slot["end_time"])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <a href="ConSchedule.php" class="primary-btn full-width">MANAGE CALENDAR</a>
            </div>
        </section>

        <section class="meeting-box">
            <div class="meeting-copy">
                <p class="eyebrow">Availability Control</p>
                <h2>Publish times customers can book with you.</h2>
                <p>Keep your calendar updated, review incoming requests, and stay organized from one dashboard.</p>
                <a href="ConSchedule.php" class="enter-btn">OPEN AVAILABILITY</a>
            </div>
            <div class="meeting-art">
                <div class="meeting-graphic contractor-graphic">
                    <span class="calendar-chip active">9 AM</span>
                    <span class="calendar-chip">11 AM</span>
                    <span class="calendar-chip">1 PM</span>
                    <span class="calendar-chip active">3 PM</span>
                    <span class="calendar-chip">5 PM</span>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
