<?php
require_once 'db.php';
requireLogin();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $member_type = $_POST['member_type'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO member (name, email, phone, member_type, join_date, status) VALUES (?, ?, ?, ?, CURRENT_DATE, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $member_type, $status);
    if ($stmt->execute()) {
        $msg = "Member '$name' registered successfully!";
    } else {
        $err = "Error adding member: " . $conn->error;
    }
}

if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];
    $stmt = $conn->prepare("UPDATE member SET status = IF(status='Active', 'Suspended', 'Active') WHERE member_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "Member status updated!";
}

$members = $conn->query("
    SELECT m.*, 
    (SELECT COUNT(*) FROM issue i WHERE i.member_id = m.member_id AND i.return_date IS NULL) AS active_issues
    FROM member m ORDER BY m.member_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Members - Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php renderNavbar(); ?>
<div class="container">
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="section-box">
        <h2>Register New Member</h2>
        <form method="POST" class="grid-form">
            <div><label>Full Name</label><input type="text" name="name" required></div>
            <div><label>Email</label><input type="email" name="email" required></div>
            <div><label>Phone (10 digits)</label><input type="text" name="phone" pattern="[0-9]{10}" required></div>
            <div>
                <label>Member Type</label>
                <select name="member_type">
                    <option value="Student">Student (Max 3 Books)</option>
                    <option value="Faculty">Faculty (Max 5 Books)</option>
                </select>
            </div>
            <div>
                <label>Status</label>
                <select name="status">
                    <option value="Active">Active</option>
                    <option value="Suspended">Suspended</option>
                </select>
            </div>
            <div><button type="submit" name="add_member">Add Member</button></div>
        </form>
    </div>

    <div class="section-box">
        <h2>All Library Members</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Books Held</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($m = $members->fetch_assoc()): ?>
                <tr>
                    <td><?= $m['member_id'] ?></td>
                    <td><?= htmlspecialchars($m['name']) ?></td>
                    <td><?= htmlspecialchars($m['email']) ?></td>
                    <td><?= htmlspecialchars($m['phone']) ?></td>
                    <td><?= $m['member_type'] ?></td>
                    <td><strong><?= $m['active_issues'] ?></strong></td>
                    <td>
                        <span class="badge <?= $m['status'] === 'Active' ? 'badge-active' : 'badge-suspended' ?>">
                            <?= $m['status'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="members.php?toggle_id=<?= $m['member_id'] ?>" class="btn" style="padding:4px 8px;font-size:0.8rem;">
                            Toggle Status
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>