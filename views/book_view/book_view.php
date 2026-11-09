<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Library management application</title>
    <style>
        <?php include 'book_view.css'; ?>
    </style>
</head>

<body>
    <div class="page-content">
        <h1 class="page-title">Book Management</h1>

        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="" class="book-form">
                <input type="text" name="title" placeholder="Book title" required>
                <input type="text" name="author" placeholder="Author" required>
                <input type="text" name="isbn" placeholder="ISBN" required>
                <input type="text" name="category" placeholder="Category" required>
                <input type="text" name="genre" placeholder="Genre" required>
                <input type="text" name="format" placeholder="Format" required>

                <div class="book-form-row">
                    <select name="status" id="status" required>
                        <?php foreach (BookStatus::cases() as $status): ?>
                            <option value="<?php echo htmlspecialchars($status->value); ?>">
                                <?php echo htmlspecialchars($status->value); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="emoji" placeholder="Emoji (optional)">
                </div>

                <textarea name="summary" placeholder="Summary"></textarea>
                <button type="submit">Add new book</button>
            </form>
        </div>

        <div class="card">
            <h2>Saved Books</h2>
            <div class="book-table-scroll">
                <table class="book-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Format</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($books)): ?>
                            <?php foreach ($books as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['title'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($item['author'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($item['isbn'] ?? ''); ?></td>
                                    <td>
                                        <span class="category-badge">
                                            <?php echo htmlspecialchars($item['category'] ?? 'Unknown Category'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['format'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($item['status'] ?? BookStatus::AVAILABLE->value); ?></td>
                                    <td>
                                        <a href="index.php?action=dashboard&page=detail&id=<?php echo (int) ($item['id'] ?? 0); ?>"
                                            class="table-detail-link">View details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="empty-message">No books found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>