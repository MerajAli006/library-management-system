<?php
require_once 'db.php';
requireLogin();

$fine_id = (int)($_GET['fine_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT f.fine_id, f.amount, f.paid_status, f.paid_date,
           i.issue_id, i.issue_date, i.due_date, i.return_date,
           DATEDIFF(i.return_date, i.due_date) AS days_late,
           b.title, b.isbn, b.author,
           m.member_id, m.name AS member_name, m.email, m.phone, m.member_type,
           s.name AS staff_name
    FROM fine f
    JOIN issue i ON f.issue_id = i.issue_id
    JOIN book b ON i.book_id = b.book_id
    JOIN member m ON i.member_id = m.member_id
    JOIN staff s ON i.staff_id = s.staff_id
    WHERE f.fine_id = ?
");
$stmt->bind_param("i", $fine_id);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();

if (!$receipt) {
    die("Receipt not found! Please select a valid fine record from Issue/Return page.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fine Receipt #FIN-<?= 1000 + $receipt['fine_id'] ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .receipt-card {
            max-width: 680px;
            margin: 30px auto;
            background: #fff;
            padding: 32px;
            border: 2px solid #1e293b;
            border-radius: 8px;
        }
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px dashed #cbd5e1;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .receipt-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        .total-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 16px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
        }
        @media print {
            nav, .no-print { display: none !important; }
            body { background: #fff; }
            .receipt-card { margin: 0 auto; border: 1px solid #000; box-shadow: none; }
        }
    </style>
</head>
<body>
<?php renderNavbar(); ?>
<div class="container">
    <div class="no-print" style="max-width: 680px; margin: 0 auto 15px auto; display:flex; justify-content:space-between;">
        <a href="issue_return.php" class="btn" style="background:#64748b;">← Back to Issue / Return</a>
        <button onclick="window.print()" class="btn btn-success">🖨 Print / Save as PDF</button>
    </div>

    <div class="receipt-card">
        <div class="receipt-header">
            <div>
                <h2 style="margin-bottom:4px;">COLLEGE CENTRAL LIBRARY</h2>
                <p style="font-size:0.85rem; color:#64748b;">Official Library Fine & Circulation Receipt</p>
                <p style="font-size:0.85rem;"><strong>Receipt No:</strong> #FIN-<?= 1000 + $receipt['fine_id'] ?></p>
            </div>
            <div style="text-align:right;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=85x85&data=RECEIPT-FIN-<?= 1000 + $receipt['fine_id'] ?>-AMT-<?= $receipt['amount'] ?>-<?= $receipt['paid_status'] ?>" alt="Receipt QR">
            </div>
        </div>

        <div class="receipt-grid">
            <div>
                <p><strong>Member Name:</strong> <?= htmlspecialchars($receipt['member_name']) ?></p>
                <p><strong>Member ID:</strong> #MEM-<?= 1000 + $receipt['member_id'] ?> (<?= $receipt['member_type'] ?>)</p>
                <p><strong>Email / Phone:</strong> <?= htmlspecialchars($receipt['email']) ?> / <?= htmlspecialchars($receipt['phone']) ?></p>
            </div>
            <div>
                <p><strong>Issue ID:</strong> #<?= $receipt['issue_id'] ?></p>
                <p><strong>Issue Date:</strong> <?= $receipt['issue_date'] ?></p>
                <p><strong>Due Date:</strong> <?= $receipt['due_date'] ?></p>
                <p><strong>Returned Date:</strong> <?= $receipt['return_date'] ?> (<?= $receipt['days_late'] ?> days late)</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>ISBN</th>
                    <th>Late Days</th>
                    <th>Rate / Day</th>
                    <th>Total Fine</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($receipt['title']) ?></td>
                    <td><?= htmlspecialchars($receipt['isbn']) ?></td>
                    <td><?= $receipt['days_late'] ?> Days</td>
                    <td>₹2.00</td>
                    <td><strong>₹<?= number_format($receipt['amount'], 2) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="total-box">
            <div>
                <strong>Payment Status:</strong>
                <span class="badge <?= $receipt['paid_status'] === 'Paid' ? 'badge-active' : 'badge-suspended' ?>">
                    <?= strtoupper($receipt['paid_status']) ?>
                </span>
                <?php if ($receipt['paid_date']): ?>
                    <span style="font-size:0.85rem; color:#475569;"> (Paid on <?= $receipt['paid_date'] ?>)</span>
                <?php endif; ?>
            </div>
            <div style="font-size:1.3rem; font-weight:bold; color:#0f172a;">
                Grand Total: ₹<?= number_format($receipt['amount'], 2) ?>
            </div>
        </div>

        <div style="margin-top:28px; display:flex; justify-content:space-between; font-size:0.85rem; color:#64748b;">
            <div>Processed By Staff: <strong><?= htmlspecialchars($receipt['staff_name']) ?></strong></div>
            <div>Generated On: <?= date('d M Y, h:i A') ?></div>
        </div>
    </div>
</div>
</body>
</html>