<?php
require_once "../includes/customer_auth.php";
require_once "../includes/db.php";
require_once "../includes/dashboard_data.php";

$customerId = (int) $_SESSION["user_id"];
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $contractorId = (int) ($_POST["contractor_id"] ?? 0);
    $appointmentDate = trim($_POST["appointment_date"] ?? "");
    $appointmentTime = trim($_POST["appointment_time"] ?? "");
    $notes = trim($_POST["notes"] ?? "");

    if (!$contractorId || $appointmentDate === "" || $appointmentTime === "") {
        $error = "Please choose a contractor, date, and time.";
    } else {
        try {
            $appointmentDateTime = date("Y-m-d H:i:s", strtotime($appointmentDate . " " . $appointmentTime));
            $stmt = $pdo->prepare(
                "INSERT INTO appointments (customer_id, contractor_id, appointment_date, status, notes)
                 VALUES (?, ?, ?, 'pending', ?)"
            );
            $stmt->execute([$customerId, $contractorId, $appointmentDateTime, $notes]);
            $success = "Meeting request sent successfully.";
        } catch (Throwable $e) {
            $error = "The appointment could not be saved. Make sure the appointments table exists.";
        }
    }
}

$contractors = fetchTopContractors($pdo);
$appointments = fetchCustomerAppointments($pdo, $customerId);

$calendarDays = [];
$today = new DateTimeImmutable("today");
for ($i = 0; $i < 14; $i++) {
    $day = $today->modify("+$i day");
    $calendarDays[] = [
        "date" => $day->format("Y-m-d"),
        "label" => $day->format("D"),
        "day" => $day->format("j"),
        "month" => $day->format("M"),
    ];
}

$timeSlots = ["09:00", "10:30", "12:00", "13:30", "15:00", "16:30"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Schedule</title>
    <link rel="stylesheet" href="../views/styles.css?v=3.0">
</head>
<body>
<div class="dash-container">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-mark">OnSight</span>
        </div>

        <nav class="sidebar-links">
            <a href="welcomeCus.php">Dashboard</a>
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
                <p class="eyebrow">Customer Schedule</p>
                <h1>Book a meeting with your contractor team.</h1>
            </div>
            <div class="profile-icon">C</div>
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
                    <h3>Calendar</h3>
                    <span>Pick a date for your consultation</span>
                </div>

                <div class="calendar-grid">
                    <?php foreach ($calendarDays as $day): ?>
                        <label class="calendar-day">
                            <input type="radio" name="appointment_date_pick" value="<?= htmlspecialchars($day["date"]) ?>" form="booking-form" <?= $day === $calendarDays[0] ? "checked" : "" ?>>
                            <span><?= htmlspecialchars($day["label"]) ?></span>
                            <strong><?= htmlspecialchars($day["day"]) ?></strong>
                            <small><?= htmlspecialchars($day["month"]) ?></small>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="section-head section-spaced">
                    <h3>Available Times</h3>
                    <span>Example booking slots</span>
                </div>

                <div class="timeslot-grid">
                    <?php foreach ($timeSlots as $slot): ?>
                        <label class="timeslot-pill">
                            <input type="radio" name="appointment_time" value="<?= htmlspecialchars($slot) ?>" form="booking-form" <?= $slot === $timeSlots[0] ? "checked" : "" ?>>
                            <span><?= date("g:i A", strtotime($slot)) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="schedule-panel">
                <div class="section-head">
                    <h3>Request Meeting</h3>
                    <span>Send the contractor a meeting request</span>
                </div>

                <form method="POST" id="booking-form" class="booking-form">
                    <input type="hidden" name="appointment_date" id="appointment_date" value="<?= htmlspecialchars($calendarDays[0]["date"]) ?>">

                    <label for="contractor_id">Contractor</label>
                    <select name="contractor_id" id="contractor_id" required>
                        <option value="">Choose a contractor</option>
                        <?php foreach ($contractors as $contractor): ?>
                            <option value="<?= (int) $contractor["id"] ?>">
                                <?= htmlspecialchars($contractor["name"]) ?> - <?= htmlspecialchars($contractor["trade"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="notes">Project Notes</label>
                    <textarea name="notes" id="notes" rows="5" placeholder="Tell the contractor what you need help with."></textarea>

                    <button type="submit" class="primary-btn">Request Appointment</button>
                </form>
            </div>
        </section>

        <section class="schedule-panel appointment-list">
            <div class="section-head">
                <h3>Your Upcoming Requests</h3>
                <span>Latest meetings on your calendar</span>
            </div>

            <?php if (!$appointments): ?>
                <p class="empty-state">No appointments booked yet.</p>
            <?php else: ?>
                <?php foreach ($appointments as $appointment): ?>
                    <div class="appointment-item">
                        <div>
                            <strong><?= htmlspecialchars($appointment["contractor_name"]) ?></strong>
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

<script>
const dateRadios = document.querySelectorAll('input[name="appointment_date_pick"]');
const hiddenDateInput = document.getElementById('appointment_date');

dateRadios.forEach((radio) => {
    radio.addEventListener('change', () => {
        hiddenDateInput.value = radio.value;
    });
});
</script>
</body>
</html>
