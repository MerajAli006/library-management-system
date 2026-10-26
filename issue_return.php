<?php
require_once 'db.php';
requireLogin();
$msg = '';
$err = '';

// 1. ISSUE BOOK LOGIC WITH ALL BUSINESS RULES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_book'])) {
    $member_id = (int)$_POST['member_id'];
    $book_id = (int)$_POST['book_id'];
    $staff_id = (int)$_SESSION['staff_id'];

    // Check Member Status and Type
    $stmt = $conn->prepare("SELECT name, member_type, status FROM member WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();

    // Check Book Availability
    $stmt2 = $conn->prepare("SELECT title, available_copies FROM book WHERE book_id = ?");
    $stmt2->bind_param("i", $book_id);
    $stmt2->execute();
    $book = $stmt2->get_result()->fetch_assoc();

    // Check Currently Held Books Count
    $stmt3 = $conn->prepare("SELECT COUNT(*) AS held FROM issue WHERE member_id = ? AND return_date IS NULL");
    $stmt3->bind_param("i", $member_id);
    $stmt3->execute();
    $held = $stmt3->get_result()->fetch_assoc()['held'];

    $maxLimit = ($member['member_type'] === 'Faculty') ? 5 : 3;

    if ($member['status'] === 'Suspended') {
        $err = "Rejected: Member '{$member['name']}' is Suspended and cannot borrow books!";
    } elseif ($held >= $maxLimit) {
        $err = "Rejected: {$member['member_type']} '{$member['name']}' already holds $held books (Max allowed: $maxLimit)!";
    } elseif ($book['available_copies'] <= 0) {
        $err = "Rejected: Book '{$book['title']}' has 0 available copies!";
    } else {
        $conn->begin_transaction();
        try {
            $ins = $conn->prepare("INSERT INTO issue (book_id, member_id, staff_id, issue_date, due_date) VALUES (?, ?, ?, CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY))");
            $ins->bind_param("iii", $book_id, $member_id, $staff_id);
            $ins->execute();

            $upd = $conn->prepare("UPDATE book SET available_copies = available_copies - 1 WHERE book_id = ?");
            $upd->bind_param("i", $book_id);
            $upd->execute();

            $conn->commit();
            $msg = "Book '{$book['title']}' issued to '{$member['name']}' for 14 days!";
        } catch (Exception $e) {
            $conn->rollback();
            $err = "Transaction failed: " . $e->getMessage();
        }
    }
}

// 2. RETURN BOOK LOGIC & AUTOMATIC FINE CALCULATION (₹2 per day late)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_book'])) {
    $issue_id = (int)$_POST['issue_id'];

    $stmt = $conn->prepare("SELECT book_id, due_date, DATEDIFF(CURRENT_DATE, due_date) AS days_late FROM issue WHERE issue_id = ? AND return_date IS NULL");
    $stmt->bind_param("i", $issue_id);
    $stmt->execute();
    $issue = $stmt->get_result()->fetch_assoc();

    if ($issue) {
        $conn->begin_transaction();
        try {
            $updIssue = $conn->prepare("UPDATE issue SET return_date = CURRENT_DATE WHERE issue_id = ?");
            $updIssue->bind_param("i", $issue_id);
            $updIssue->execute();

            $updBook = $conn->prepare("UPDATE book SET available_copies = available_copies + 1 WHERE book_id = ?");
            $updBook->bind_param("i", $issue['book_id']);
            $updBook->execute();

            $daysLate = (int)$issue['days_late'];
            if ($daysLate > 0) {
                $fineAmount = $daysLate * 2.00;
                $insFine = $conn->prepare("INSERT INTO fine (issue_id, amount, paid_status) VALUES (?, ?, 'Unpaid')");
                $insFine->bind_param("id", $issue_id, $fineAmount);
                $insFine->execute();
                $msg = "Book returned $daysLate days late! A fine of ₹" . number_format($fineAmount, 2) . " has been generated.";
            } else {
                $msg = "Book returned on time! No fine applicable.";
            }
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $err = "Error returning book: " . $e->getMessage();
        }
    }
}

// 3. PAY FINE LOGIC
if (isset($_GET['pay_fine_id'])) {
    $fine_id = (int)$_GET['pay_fine_id'];
    $stmt = $conn->prepare("UPDATE fine SET paid_status = 'Paid', paid_date = CURRENT_DATE WHERE fine_id = ?");
    $stmt->bind_param("i", $fine_id);
    $stmt->execute();
    $msg = "Fine #$fine_id marked as Paid!";
}

$members = $conn->query("SELECT member_id, name, member_type, status FROM member ORDER BY name");
$books = $conn->query("SELECT book_id, title, available_copies FROM book ORDER BY title");
$activeIssues = $conn->query("
    SELECT i.issue_id, b.title, m.name AS member_name, i.issue_date, i.due_date,
           GREATEST(0, DATEDIFF(CURRENT_DATE, i.due_date)) AS days_late
    FROM issue i
    JOIN book b ON i.book_id = b.book_id
    JOIN member m ON i.member_id = m.member_id
    WHERE i.return_date IS NULL
    ORDER BY i.due_date ASC
");
$fines = $conn->query("
    SELECT f.*, m.name AS member_name, b.title
    FROM fine f
    JOIN issue i ON f.issue_id = i.issue_id
    JOIN member m ON i.member_id = m.member_id
    JOIN book b ON i.book_id = b.book_id
    ORDER BY f.fine_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issue, Return & Fines - Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php renderNavbar(); ?>
<div class="container">
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="section-box">
        <h2>Issue a Book (Loan Period: 14 Days automatically)</h2>
        <form method="POST" class="grid-form">
            <div>
                <label>Select Member</label>
                <select name="member_id" required>
                    <?php while ($m = $members->fetch_assoc()): ?>
                        <option value="<?= $m['member_id'] ?>">
                            <?= htmlspecialchars($m['name']) ?> (<?= $m['member_type'] ?> - <?= $m['status'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Select Book</label>
                <select name="book_id" required>
                    <?php while ($b = $books->fetch_assoc()): ?>
                        <option value="<?= $b['book_id'] ?>">
                            <?= htmlspecialchars($b['title']) ?> (Available: <?= $b['available_copies'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <button type="submit" name="issue_book">Issue Book</button>
            </div>
        </form>
    </div>

    <div class="section-box">
        <h2>Return Issued Books</h2>
        <table>
            <thead>
                <tr>
                    <th>Issue ID</th>
                    <th>Book Title</th>
                    <th>Member</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Days Late</th>
                    <th>Estimated Fine</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $activeIssues->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $row['issue_id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['member_name']) ?></td>
                    <td><?= $row['issue_date'] ?></td>
                    <td><?= $row['due_date'] ?></td>
                    <td><?= $row['days_late'] ?> days</td>
                    <td>₹<?= number_format($row['days_late'] * 2, 2) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="issue_id" value="<?= $row['issue_id'] ?>">
                            <button type="submit" name="return_book" class="btn-success" style="padding:5px 10px;font-size:0.82rem;">
                                Return Book
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="section-box">
        <h2>Fine Management</h2>
        <table>
            <thead>
                <tr>
                    <th>Fine ID</th>
                    <th>Issue ID</th>
                    <th>Member Name</th>
                    <th>Book Title</th>
                    <th>Fine Amount</th>
                    <th>Status</th>
                    <th>Paid Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($f = $fines->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $f['fine_id'] ?></td>
                    <td>#<?= $f['issue_id'] ?></td>
                    <td><?= htmlspecialchars($f['member_name']) ?></td>
                    <td><?= htmlspecialchars($f['title']) ?></td>
                    <td><strong>₹<?= number_format($f['amount'], 2) ?></strong></td>
                    <td>
                        <span class="badge <?= $f['paid_status'] === 'Paid' ? 'badge-active' : 'badge-suspended' ?>">
                            <?= $f['paid_status'] ?>
                        </span>
                    </td>
                    <td><?= $f['paid_date'] ?? '-' ?></td>
                    <td>
                        <?php if ($f['paid_status'] === 'Unpaid'): ?>
                            <a href="issue_return.php?pay_fine_id=<?= $f['fine_id'] ?>" class="btn" style="padding:4px 8px;font-size:0.8rem;">Mark Paid</a>
                        <?php else: ?>
                            Completed
                        <?php endif; ?>
                        <a href="receipt.php?fine_id=<?= $f['fine_id'] ?>" class="btn" style="padding:4px 8px;font-size:0.8rem;background:#334155;margin-left:4px;">🖨 Receipt</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>