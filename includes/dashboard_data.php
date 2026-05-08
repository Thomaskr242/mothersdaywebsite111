<?php

function fetchTopContractors(PDO $pdo, string $search = ""): array
{
    try {
        if ($search !== "") {
            $stmt = $pdo->prepare(
                "SELECT id, name, trade, rating
                 FROM contractors
                 WHERE name LIKE ? OR trade LIKE ?
                 ORDER BY rating DESC, name ASC"
            );
            $term = "%" . $search . "%";
            $stmt->execute([$term, $term]);
            return $stmt->fetchAll();
        }

        $stmt = $pdo->query(
            "SELECT id, name, trade, rating
             FROM contractors
             ORDER BY rating DESC, name ASC
             LIMIT 5"
        );
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetchCustomerAppointments(PDO $pdo, int $customerId): array
{
    try {
        $stmt = $pdo->prepare(
            "SELECT a.id, a.appointment_date, a.status, a.notes, c.name AS contractor_name, c.trade
             FROM appointments a
             INNER JOIN contractors c ON c.id = a.contractor_id
             WHERE a.customer_id = ?
             ORDER BY a.appointment_date ASC
             LIMIT 8"
        );
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetchContractorAppointments(PDO $pdo, int $contractorId): array
{
    try {
        $stmt = $pdo->prepare(
            "SELECT a.id, a.appointment_date, a.status, a.notes, u.full_name AS customer_name
             FROM appointments a
             INNER JOIN users u ON u.id = a.customer_id
             WHERE a.contractor_id = ?
             ORDER BY a.appointment_date ASC
             LIMIT 8"
        );
        $stmt->execute([$contractorId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function fetchContractorMetrics(PDO $pdo, int $contractorId): array
{
    $metrics = [
        "upcoming_jobs" => 0,
        "pending_requests" => 0,
        "confirmed_jobs" => 0,
        "monthly_revenue" => 0,
    ];

    try {
        $stmt = $pdo->prepare(
            "SELECT
                SUM(CASE WHEN appointment_date >= NOW() THEN 1 ELSE 0 END) AS upcoming_jobs,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_requests,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_jobs,
                COALESCE(SUM(CASE WHEN status = 'confirmed' AND MONTH(appointment_date) = MONTH(CURRENT_DATE()) AND YEAR(appointment_date) = YEAR(CURRENT_DATE()) THEN quoted_price ELSE 0 END), 0) AS monthly_revenue
             FROM appointments
             WHERE contractor_id = ?"
        );
        $stmt->execute([$contractorId]);
        $row = $stmt->fetch();

        if ($row) {
            $metrics = array_merge($metrics, $row);
        }
    } catch (Throwable $e) {
    }

    return $metrics;
}

function fetchAvailability(PDO $pdo, int $contractorId): array
{
    try {
        $stmt = $pdo->prepare(
            "SELECT id, available_date, start_time, end_time, is_booked
             FROM contractor_availability
             WHERE contractor_id = ? AND available_date >= CURRENT_DATE()
             ORDER BY available_date ASC, start_time ASC
             LIMIT 21"
        );
        $stmt->execute([$contractorId]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}
