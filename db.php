<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'library_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Ensure default admin account (username: admin, password: admin123) exists with valid password_hash
$default_user = 'admin';
$stmt = $conn->prepare("SELECT staff_id, password_hash FROM staff WHERE username = ?");
$stmt->bind_param("s", $default_user);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $name = 'Meraj Ali';
    $ins = $conn->prepare("INSERT INTO staff (name, username, password_hash) VALUES (?, ?, ?)");
    $ins->bind_param("sss", $name, $default_user, $hash);
    $ins->execute();
} else {
    $row = $res->fetch_assoc();
    if (!password_verify('admin123', $row['password_hash'])) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE staff SET password_hash = ? WHERE staff_id = ?");
        $upd->bind_param("si", $hash, $row['staff_id']);
        $upd->execute();
    }
}

function requireLogin() {
    if (!isset($_SESSION['staff_id'])) {
        header("Location: login.php");
        exit();
    }
}

function renderNavbar() {
    echo '
    <nav>
        <div class="brand">College Library LMS <span style="font-size:0.75rem;background:#0284c7;color:#fff;padding:2px 6px;border-radius:4px;">v2.0 Pro</span></div>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="books.php">Books</a></li>
            <li><a href="members.php">Members</a></li>
            <li><a href="issue_return.php">Issue / Return</a></li>
            <li><a href="authors.php">M:N Authors</a></li>
            <li><a href="analytics.php">Analytics & Logs</a></li>
            <li><a href="student_portal.php" style="color:#38bdf8;">Student Portal</a></li>
            <li><a href="logout.php">Logout (' . htmlspecialchars($_SESSION['staff_name'] ?? 'Staff') . ')</a></li>
        </ul>
    </nav>';
}