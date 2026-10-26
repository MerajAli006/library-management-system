<?php
require_once 'db.php';
$msg = '';
$err = '';

// Student Login via Registered Email (e.g., aarav@college.edu or priya@college.edu)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_login'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT * FROM member WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($member = $res->fetch_assoc()) {
        $_SESSION['student_id'] = $member['member_id'];
    } else {
        $err = "No member found with email '$email'!";
    }
}

if (isset($_GET['student_logout'])) {
    unset($_SESSION['student_id']);
    header("Location: student_portal.php");
    exit();
}

// Reserve Out-of-Stock Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_book']) && isset($_SESSION['student_id'])) {
    $book_id = (int)$_POST['book_id'];
    $member_id = (int)$_SESSION['student_id'];

    $chk = $conn->prepare("SELECT reservation_id FROM reservation WHERE book_id = ? AND member_id = ? AND status = 'Pending'");
    $chk->bind_param("ii", $book_id, $member_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $err = "You have already reserved this book in the waitlist!";
    } else {
        $ins = $conn->prepare("INSERT INTO reservation (book_id, member_id) VALUES (?, ?)");
        $ins->bind_param("ii", $book_id, $member_id);
        $ins->execute();
        $msg = "Book reserved successfully! You are added to the FIFO priority waitlist.";
    }
}

$currentMember = null;
if (isset($_SESSION['student_id'])) {
    $sid = (int)$_SESSION['student_id'];
    $currentMember = $conn->query("SELECT * FROM member WHERE member_id = $sid")->fetch_assoc();
    $myIssues = $conn->query("
        SELECT i.*, b.title, b.isbn,
               GREATEST(0, DATEDIFF(COALESCE(i.return_date, CURRENT_DATE), i.due_date)) AS late_days
        FROM issue i
        JOIN book b ON i.book_id = b.book_id
        WHERE i.member_id = $sid
        ORDER BY i.issue_date DESC
    ");
    $outOfStockBooks = $conn->query("SELECT * FROM book WHERE available_copies = 0");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student & Faculty Self-Service Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav>
    <div class="brand">College Library - Member Self-Service Portal</div>
    <ul>
        <li><a href="login.php">Staff / Admin Portal</a></li>
        <?php if ($currentMember): ?>
            <li><a href="student_portal.php?student_logout=1">Logout (<?= htmlspecialchars($currentMember['name']) ?>)</a></li>
        <?php endif; ?>
    </ul>
</nav>

<div class="container">
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <?php if (!$currentMember): ?>
        <div class="section-box" style="max-width:450px; margin:50px auto;">
            <h2>Student / Faculty Login</h2>
            <p style="font-size:0.9rem; color:#64748b; margin-bottom:14px;">
                Demo Emails: <code>aarav@college.edu</code>, <code>priya@college.edu</code>, or <code>suresh.iyer@college.edu</code>
            </p>
            <form method="POST">
                <div style="margin-bottom:14px;">
                    <label>Registered College Email</label>
                    <input type="email" name="email" required value="priya@college.edu">
                </div>
                <button type="submit" name="student_login" style="width:100%;">Access My Library Account</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Digital Library ID Card with Dynamic QR Code -->
        <div class="section-box" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; border-left:6px solid #0284c7;">
            <div>
                <h2>Digital Library ID Card</h2>
                <p><strong>Name:</strong> <?= htmlspecialchars($currentMember['name']) ?> (ID: #MEM-<?= 1000 + $currentMember['member_id'] ?>)</p>
                <p><strong>Email:</strong> <?= htmlspecialchars($currentMember['email']) ?> | <strong>Phone:</strong> <?= htmlspecialchars($currentMember['phone']) ?></p>
                <p><strong>Role:</strong> <?= $currentMember['member_type'] ?> | <strong>Status:</strong> 
                    <span class="badge <?= $currentMember['status'] === 'Active' ? 'badge-active' : 'badge-suspended' ?>"><?= $currentMember['status'] ?></span>
                </p>
            </div>
            <div style="text-align:center;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=MEM-<?= 1000 + $currentMember['member_id'] ?>-<?= urlencode($currentMember['email']) ?>" alt="Member QR" style="border:1px solid #cbd5e1; padding:4px; border-radius:6px;">
                <div style="font-size:0.75rem; color:#64748b;">Scan at Library Desk</div>
            </div>
        </div>

        <!-- Reserve Out-of-Stock Books -->
        <div class="section-box">
            <h2>Out-of-Stock Books (Join Priority Waitlist Queue)</h2>
            <form method="POST" class="grid-form">
                <div>
                    <label>Select Book Currently Out of Stock (Available Copies = 0)</label>
                    <select name="book_id" required>
                        <?php while ($b = $outOfStockBooks->fetch_assoc()): ?>
                            <option value="<?= $b['book_id'] ?>"><?= htmlspecialchars($b['title']) ?> by <?= htmlspecialchars($b['author']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" name="reserve_book">Reserve Book in Queue</button>
                </div>
            </form>
        </div>

        <!-- My Borrowed History & Live Fine Tracker -->
        <div class="section-box">
            <h2>My Borrowed Books & Live Due-Date Tracker</h2>
            <table>
                <thead>
                    <tr>
                        <th>Issue ID</th>
                        <th>ISBN</th>
                        <th>Book Title</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Status</th>
                        <th>Late Fine (₹2/day)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $myIssues->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['issue_id'] ?></td>
                        <td><?= htmlspecialchars($row['isbn']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= $row['issue_date'] ?></td>
                        <td><?= $row['due_date'] ?></td>
                        <td>
                            <?php if ($row['return_date']): ?>
                                <span class="badge badge-active">Returned on <?= $row['return_date'] ?></span>
                            <?php elseif ($row['late_days'] > 0): ?>
                                <span class="badge badge-suspended">Overdue by <?= $row['late_days'] ?> days</span>
                            <?php else: ?>
                                <span class="badge badge-active">Currently Reading</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:bold; color: <?= $row['late_days'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                            ₹<?= number_format($row['late_days'] * 2, 2) ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>