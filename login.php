<?php
require_once 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT staff_id, name, password_hash FROM staff WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['staff_id'] = $row['staff_id'];
            $_SESSION['staff_name'] = $row['name'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password! Access denied.";
        }
    } else {
        $error = "Staff username not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Login - Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" style="max-width: 420px; margin-top: 90px;">
    <div class="section-box">
        <h2>Staff Login</h2>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div style="margin-bottom: 12px;">
                <label>Username</label>
                <input type="text" name="username" required placeholder="admin">
            </div>
            <div style="margin-bottom: 16px;">
                <label>Password</label>
                <input type="password" name="password" required placeholder="admin123">
            </div>
            <button type="submit" style="width: 100%;">Login</button>
        </form>
    </div>
</div>
</body>
</html>