<?php
require_once 'db.php';
requireLogin();

// 1. One-Click CSV / Excel Export for Overdue Books
if (isset($_GET['export']) && $_GET['export'] === 'overdue_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=overdue_books_report_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Issue ID', 'Book Title', 'Member Name', 'Phone', 'Issue Date', 'Due Date', 'Days Overdue', 'Estimated Fine (INR)']);
    
    $rows = $conn->query("SELECT * FROM v_overdue_books");
    while ($r = $rows->fetch_assoc()) {
        fputcsv($output, [$r['issue_id'], $r['title'], $r['member_name'], $r['phone'], $r['issue_date'], $r['due_date'], $r['days_overdue'], $r['estimated_fine']]);
    }
    fclose($output);
    exit();
}

// 2. Fetch Category-wise Book Data for Chart.js
$catData = $conn->query("
    SELECT c.category_name, COUNT(b.book_id) AS titles, COALESCE(SUM(b.total_copies),0) AS copies
    FROM category c
    LEFT JOIN book b ON c.category_id = b.category_id
    GROUP BY c.category_id, c.category_name
");
$catLabels = [];
$catCounts = [];
while ($row = $catData->fetch_assoc()) {
    $catLabels[] = $row['category_name'];
    $catCounts[] = (int)$row['copies'];
}

// 3. Fetch Issue Status Breakdown (Active On-Time vs Overdue vs Returned)
$returnedCnt = $conn->query("SELECT COUNT(*) AS c FROM issue WHERE return_date IS NOT NULL")->fetch_assoc()['c'];
$overdueCnt = $conn->query("SELECT COUNT(*) AS c FROM v_overdue_books")->fetch_assoc()['c'];
$activeOnTimeCnt = $conn->query("SELECT COUNT(*) AS c FROM issue WHERE return_date IS NULL AND CURRENT_DATE <= due_date")->fetch_assoc()['c'];

// 4. Fetch Pending Book Reservations (Waitlist Queue)
$reservations = $conn->query("
    SELECT r.reservation_id, b.title, b.available_copies, m.name AS member_name, m.member_type, r.request_date, r.status
    FROM reservation r
    JOIN book b ON r.book_id = b.book_id
    JOIN member m ON r.member_id = m.member_id
    ORDER BY r.request_date ASC
");

// 5. Fetch Automated Trigger Audit Logs
$logs = $conn->query("SELECT * FROM audit_log ORDER BY log_id DESC LIMIT 15");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics, Waitlist & Audit Logs - LMS v2.0</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php renderNavbar(); ?>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <h2>Library Analytics & Enterprise Reports</h2>
        <a href="analytics.php?export=overdue_csv" class="btn btn-success">⬇ Export Overdue Report (Excel / CSV)</a>
    </div>

    <!-- Visual Charts Row -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap:20px; margin-bottom:24px;">
        <div class="section-box" style="margin-bottom:0;">
            <h2>Category-wise Book Inventory</h2>
            <canvas id="categoryChart" height="200"></canvas>
        </div>
        <div class="section-box" style="margin-bottom:0;">
            <h2>Circulation & Overdue Status</h2>
            <canvas id="statusChart" height="200"></canvas>
        </div>
    </div>

    <!-- Waitlist / Reservation Queue (FIFO) -->
    <div class="section-box">
        <h2>Book Reservation Queue (FIFO Waitlist for Out-of-Stock Books)</h2>
        <table>
            <thead>
                <tr>
                    <th>Queue #</th>
                    <th>Book Title</th>
                    <th>Available Now</th>
                    <th>Requested By</th>
                    <th>Type</th>
                    <th>Request Timestamp</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reservations->num_rows === 0): ?>
                    <tr><td colspan="7">No active reservations in queue. (Students can reserve books from the Student Portal).</td></tr>
                <?php else: ?>
                    <?php while ($res = $reservations->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $res['reservation_id'] ?></td>
                        <td><?= htmlspecialchars($res['title']) ?></td>
                        <td><strong><?= $res['available_copies'] ?></strong></td>
                        <td><?= htmlspecialchars($res['member_name']) ?></td>
                        <td><?= $res['member_type'] ?></td>
                        <td><?= $res['request_date'] ?></td>
                        <td><span class="badge badge-active"><?= $res['status'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Automated Database Trigger Audit Logs -->
    <div class="section-box">
        <h2>Real-Time Database Trigger Audit Logs (ACID Event Tracking)</h2>
        <table>
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Event Type</th>
                    <th>Description (Generated by MySQL Trigger)</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = $logs->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $log['log_id'] ?></td>
                    <td><span class="badge badge-active"><?= htmlspecialchars($log['action_type']) ?></span></td>
                    <td><?= htmlspecialchars($log['description']) ?></td>
                    <td><?= $log['action_timestamp'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
new Chart(document.getElementById('categoryChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($catLabels) ?>,
        datasets: [{
            label: 'Total Copies',
            data: <?= json_encode($catCounts) ?>,
            backgroundColor: '#0284c7',
            borderRadius: 6
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Returned On Time/Late', 'Active (Within Due Date)', 'Currently Overdue'],
        datasets: [{
            data: [<?= (int)$returnedCnt ?>, <?= (int)$activeOnTimeCnt ?>, <?= (int)$overdueCnt ?>],
            backgroundColor: ['#16a34a', '#0284c7', '#dc2626']
        }]
    }
});
</script>
</body>
</html>