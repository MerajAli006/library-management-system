<?php
require_once 'db.php';
requireLogin();
$msg = '';
$err = '';

// Add Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $isbn = trim($_POST['isbn']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $publisher = trim($_POST['publisher']);
    $pub_year = (int)$_POST['pub_year'];
    $category_id = (int)$_POST['category_id'];
    $total_copies = (int)$_POST['total_copies'];

    $stmt = $conn->prepare("INSERT INTO book (isbn, title, author, publisher, pub_year, category_id, total_copies, available_copies) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiiii", $isbn, $title, $author, $publisher, $pub_year, $category_id, $total_copies, $total_copies);
    if ($stmt->execute()) {
        $msg = "Book '$title' added successfully!";
    } else {
        $err = "Error adding book: " . $conn->error;
    }
}

// Delete Book
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM book WHERE book_id = ?");
    $stmt->bind_param("i", $del_id);
    if ($stmt->execute()) {
        $msg = "Book deleted successfully!";
    } else {
        $err = "Cannot delete book (it may have active issue records).";
    }
}

// Search Books
$search = trim($_GET['search'] ?? '');
$cat_filter = (int)($_GET['category_id'] ?? 0);

$sql = "SELECT b.*, c.category_name FROM book b JOIN category c ON b.category_id = c.category_id WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}
if ($cat_filter > 0) {
    $sql .= " AND b.category_id = ?";
    $params[] = $cat_filter;
    $types .= "i";
}
$sql .= " ORDER BY b.book_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$books = $stmt->get_result();

$categories = $conn->query("SELECT * FROM category ORDER BY category_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books - Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php renderNavbar(); ?>
<div class="container">
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="section-box">
        <h2>Add New Book</h2>
        <form method="POST" class="grid-form">
            <div><label>ISBN</label><input type="text" name="isbn" required></div>
            <div><label>Title</label><input type="text" name="title" required></div>
            <div><label>Author</label><input type="text" name="author" required></div>
            <div><label>Publisher</label><input type="text" name="publisher" required></div>
            <div><label>Publication Year</label><input type="number" name="pub_year" min="1900" max="2026" value="2024" required></div>
            <div>
                <label>Category</label>
                <select name="category_id" required>
                    <?php while ($c = $categories->fetch_assoc()): ?>
                        <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div><label>Total Copies</label><input type="number" name="total_copies" min="1" value="3" required></div>
            <div><button type="submit" name="add_book">Add Book</button></div>
        </form>
    </div>

    <div class="section-box">
        <h2>View & Search Books</h2>
        <form method="GET" class="grid-form" style="margin-bottom: 16px;">
            <div>
                <label>Search by Title or Author</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Enter title or author...">
            </div>
            <div>
                <label>Filter by Category</label>
                <select name="category_id">
                    <option value="0">All Categories</option>
                    <?php
                    $categories->data_seek(0);
                    while ($c = $categories->fetch_assoc()):
                    ?>
                        <option value="<?= $c['category_id'] ?>" <?= $cat_filter == $c['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['category_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div><button type="submit">Search</button> <a href="books.php" class="btn" style="background:#64748b;">Reset</a></div>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Total</th>
                    <th>Available</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($b = $books->fetch_assoc()): ?>
                <tr>
                    <td><?= $b['book_id'] ?></td>
                    <td><?= htmlspecialchars($b['isbn']) ?></td>
                    <td><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= htmlspecialchars($b['author']) ?></td>
                    <td><?= htmlspecialchars($b['category_name']) ?></td>
                    <td><?= $b['total_copies'] ?></td>
                    <td style="font-weight:bold; color: <?= $b['available_copies'] == 0 ? '#dc2626' : '#16a34a' ?>;">
                        <?= $b['available_copies'] ?>
                    </td>
                    <td>
                        <a href="books.php?delete_id=<?= $b['book_id'] ?>" class="btn btn-danger" style="padding:4px 8px;font-size:0.8rem;" onclick="return confirm('Delete this book?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>