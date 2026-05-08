<?php
require_once "../includes/customer_auth.php";
require_once "../includes/db.php";
require_once "../includes/dashboard_data.php";

$search = trim($_GET["search"] ?? "");
$contractors = fetchTopContractors($pdo, $search);
$appointments = fetchCustomerAppointments($pdo, (int) $_SESSION["user_id"]);
$nextAppointment = $appointments[0] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../views/styles.css?v=3.0">
</head>
<body>
<div class="dash-container">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-mark">OnSight</span>
        </div>

        <nav class="sidebar-links">
            <a href="CusInbox.php">Inbox</a>
            <a href="CusSchedule.php" class="is-active">Schedule</a>
            <a href="CusArchive.php">Archive</a>
            <a href="CusSettings.php">Settings</a>
            <a href="logout.php">Sign Out</a>
        </nav>
    </aside>

    <main class="dash-main">
        <header class="dash-top">
            <div>
                <p class="eyebrow">Customer Dashboard</p>
                <h1>Welcome <strong><?= htmlspecialchars($_SESSION["full_name"] ?? "Customer") ?></strong>!</h1>
            </div>
            <div class="profile-icon">C</div>
        </header>

        <section class="dash-cards">
            <a href="CusSchedule.php" class="card highlight">
                <span class="card-label">Book a Meeting</span>
                <strong><?= count($appointments) ?></strong>
            </a>
            <a href="CusSchedule.php" class="card">
                <span class="card-label">Upcoming Appointments</span>
                <strong><?= count($appointments) ?></strong>
            </a>
            <a href="CusArchive.php" class="card">
                <span class="card-label">Call Recordings</span>
                <strong>View</strong>
            </a>
            <a href="CusInbox.php" class="card">
                <span class="card-label">Unread Messages</span>
                <strong>Inbox</strong>
            </a>
        </section>

        <section class="dash-row">
            <div class="contractors-box">
                <div class="section-head">
                    <h3>Top Contractors</h3>
                    <span><?= $search !== "" ? "Results for \"" . htmlspecialchars($search) . "\"" : "Highest rated" ?></span>
                </div>

                <?php if (!$contractors): ?>
                    <p class="empty-state">No contractors found yet.</p>
                <?php else: ?>
                    <?php foreach ($contractors as $contractor): ?>
                        <a href="contractor_profile.php?id=<?= (int) $contractor["id"] ?>" class="contractor-item">
                            <div>
                                <strong><?= htmlspecialchars($contractor["name"]) ?></strong>
                                <span><?= htmlspecialchars($contractor["trade"]) ?></span>
                            </div>
                            <div class="rating-pill">⭐ <?= htmlspecialchars((string) $contractor["rating"]) ?></div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="search-box">
                <div class="section-head">
                    <h3>Find Your Solution</h3>
                    <span>Search by contractor or trade</span>
                </div>
                <form method="GET">
                    <input
                        type="text"
                        name="search"
                        placeholder="Electrician, plumber, painter..."
                        value="<?= htmlspecialchars($search) ?>"
                    >
                </form>

                <div class="mini-calendar-panel">
                    <h4>Next Virtual Meeting</h4>
                    <?php if ($nextAppointment): ?>
                        <p><?= date("M j, Y g:i A", strtotime($nextAppointment["appointment_date"])) ?></p>
                        <span><?= htmlspecialchars($nextAppointment["contractor_name"]) ?> · <?= htmlspecialchars($nextAppointment["trade"]) ?></span>
                    <?php else: ?>
                        <p>No meeting booked</p>
                        <span>Head to Schedule to lock in a time.</span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="meeting-box">
            <div class="meeting-copy">
                <p class="eyebrow">Scheduling</p>
                <h2>Set your next contractor meeting in a few clicks.</h2>
                <p>Browse availability, choose a time, and keep every consultation in one place.</p>
                <a href="CusSchedule.php" class="enter-btn">OPEN CALENDAR</a>
            </div>
            <div class="meeting-art">
                <div class="meeting-graphic">
                    <span class="calendar-chip">Mon</span>
                    <span class="calendar-chip">Tue</span>
                    <span class="calendar-chip active">Wed</span>
                    <span class="calendar-chip">Thu</span>
                    <span class="calendar-chip">Fri</span>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
