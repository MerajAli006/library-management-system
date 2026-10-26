<?php
require_once 'db.php';
requireLogin();
$msg = '';
$err = '';

// Add New Author & Link to Book (Many-to-Many Junction Table)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_author'])) {
    $author_name = trim($_POST['author_name']);
    $bio = trim($_POST['bio']);
    $book_id = (int)$_POST['book_id'];

    // Insert or fetch Author ID
    $stmt = $conn->prepare("INSERT INTO author (author_name, bio) VALUES (?, ?) ON DUPLICATE KEY UPDATE author_id=LAST_INSERT_ID(author_id), bio=VALUES(bio)");
    $stmt->bind_param("ss", $author_name, $bio);
    $stmt->execute();
    $author_id = $conn->insert_id;

    // Insert into M:N Junction Table (book_author)
    $stmt2 = $conn->prepare("INSERT IGNORE INTO book_author (book_id, author_id) VALUES (?, ?)");
    $stmt2->bind_param("ii", $book_id, $author_id);
    if ($stmt2->execute()) {
        $msg = "Author '$author_name' linked to Book ID #$book_id (Many-to-Many table updated)!";
    } else {
        $err = "Could not link author.";
    }
}

$books = $conn->query("SELECT book_id, title, isbn FROM book ORDER BY title");

// 3-Table M:N JOIN Query using GROUP_CONCAT
$mnMapping = $conn->query("
    SELECT b.book_id, b.isbn, b.title,
           GROUP_CONCAT(a.author_name SEPARATOR ', ') AS all_authors,
           COUNT(ba.author_id) AS author_count
    FROM book b
    LEFT JOIN book_author ba ON b.book_id = ba.book_id
    LEFT JOIN author a ON ba.author_id = a.author_id
    GROUP BY b.book_id, b.isbn, b.title
    ORDER BY author_count DESC, b.book_id ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>M:N Author Mapping - LMS v2.0</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php renderNavbar(); ?>
<div class="container">
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="section-box">
        <h2>Link Co-Author to Book (Many-to-Many Junction Table: <code>book_author</code>)</h2>
        <form method="POST" class="grid-form">
            <div>
                <label>Select Book</label>
                <select name="book_id" required>
                    <?php while ($b = $books->fetch_assoc()): ?>
                        <option value="<?= $b['book_id'] ?>"><?= htmlspecialchars($b['title']) ?> (<?= $b['isbn'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Co-Author Name</label>
                <input type="text" name="author_name" placeholder="e.g., Dennis Ritchie" required>
            </div>
            <div>
                <label>Author Specialization / Bio</label>
                <input type="text" name="bio" value="Computer Scientist & Co-Author" required>
            </div>
            <div>
                <button type="submit" name="link_author">Link Co-Author (M:N)</button>
            </div>
        </form>
    </div>

    <div class="section-box">
        <h2>Books with Multi-Author (M:N) Mapping</h2>
        <table>
            <thead>
                <tr>
                    <th>Book ID</th>
                    <th>ISBN</th>
                    <th>Book Title</th>
                    <th>Linked Authors (via <code>book_author</code> table)</th>
                    <th>Author Count</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $mnMapping->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $row['book_id'] ?></td>
                    <td><?= htmlspecialchars($row['isbn']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><strong><?= htmlspecialchars($row['all_authors'] ?? 'None') ?></strong></td>
                    <td><span class="badge badge-active"><?= $row['author_count'] ?> Author(s)</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>