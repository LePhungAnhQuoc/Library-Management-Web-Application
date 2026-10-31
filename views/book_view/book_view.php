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
                <input type="text" name="name" placeholder="Type a book name..." required>
                <select name="category" id="category" required>
                    <option value="" disabled selected>-- Select a category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['Id']); ?>">
                            <?php echo htmlspecialchars($cat['Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Add new book</button>
            </form>
        </div>

        <div class="card">
            <h2>Saved Books</h2>
            <table class="book-table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($books)): ?>
                        <?php foreach ($books as $item): ?>
                            <tr>
                                <td class="book-title"><?php echo htmlspecialchars($item['Name']); ?></td>
                                <td>
                                    <span class="category-badge">
                                        <?php
                                        $catId = $item['IdCategory'];
                                        echo htmlspecialchars($categoryMap[$catId] ?? 'Unknown Category');
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="empty-message">No books found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>