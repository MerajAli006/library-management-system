<?php
require_once 'db.php';
requireLogin();

$totalBooks = $conn->query("SELECT COALESCE(SUM(total_copies),0) AS cnt FROM book")->fetch_assoc()['cnt'];
$issuedBooks = $conn->query("SELECT COUNT(*) AS cnt FROM issue WHERE return_date IS NULL")->fetch_assoc()['cnt'];
$overdueBooks = $conn->query("SELECT COUNT(*) AS cnt FROM v_overdue_books")->fetch_assoc()['cnt'];
$unpaidFines = $conn->query("SELECT COALESCE(SUM(amount),0) AS amt FROM fine WHERE paid_status = 'Unpaid'")->fetch_assoc()['amt'];

$overdueList = $conn->query("SELECT * FROM v_overdue_books");
$issuedList = $conn->query("SELECT * FROM v_issued_books ORDER BY due_date ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php renderNavbar(); ?>
<div class="container">
    <div class="cards">
        <div class="card">
            <h3>Total Book Copies</h3>
            <div class="num"><?= $totalBooks ?></div>
        </div>
        <div class="card">
            <h3>Currently Issued Books</h3>
            <div class="num"><?= $issuedBooks ?></div>
        </div>
        <div class="card" style="border-left-color: #dc2626;">
            <h3>Overdue Books</h3>
            <div class="num"><?= $overdueBooks ?></div>
        </div>
        <div class="card" style="border-left-color: #d97706;">
            <h3>Unpaid Fines</h3>
            <div class="num">₹<?= number_format($unpaidFines, 2) ?></div>
        </div>
    </div>

    <div class="section-box">
        <h2>Overdue Books List (₹2 / day fine)</h2>
        <table>
            <thead>
                <tr>
                    <th>Issue ID</th>
                    <th>Book Title</th>
                    <th>Member Name</th>
                    <th>Phone</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Days Late</th>
                    <th>Accrued Fine</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $overdueList->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $row['issue_id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['member_name']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= $row['issue_date'] ?></td>
                    <td><?= $row['due_date'] ?></td>
                    <td style="color:#dc2626;font-weight:bold;"><?= $row['days_overdue'] ?> days</td>
                    <td style="color:#dc2626;font-weight:bold;">₹<?= number_format($row['estimated_fine'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="section-box">
        <h2>Currently Issued Books</h2>
        <table>
            <thead>
                <tr>
                    <th>Issue ID</th>
                    <th>ISBN</th>
                    <th>Book Title</th>
                    <th>Member Name</th>
                    <th>Type</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $issuedList->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $row['issue_id'] ?></td>
                    <td><?= htmlspecialchars($row['isbn']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['member_name']) ?></td>
                    <td><?= $row['member_type'] ?></td>
                    <td><?= $row['issue_date'] ?></td>
                    <td><?= $row['due_date'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>