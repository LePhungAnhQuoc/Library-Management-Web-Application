<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search & Browse</title>
    <style>
        <?php include 'book_view.css'; ?>
    </style>
</head>

<body>
    <div class="search-page">
        <form class="search-form" method="GET" action="index.php">
            <input type="hidden" name="action" value="dashboard">
            <input type="hidden" name="page" value="search">

            <div class="search-toolbar">
                <div class="search-bar">
                    <span class="search-icon">🔎</span>
                    <input type="search" name="keyword" value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>" placeholder="Search books, authors, ISBNs, or categories">
                </div>
            </div>
        </form>

        <section class="results-panel">
            <?php if (!empty($reserveMessage)): ?>
                <div class="<?php echo $reserveSuccess ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($reserveMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($books)): ?>
                <div class="empty-state">
                    No books matched your search.
                </div>
            <?php else: ?>
                <div class="book-results grid">
                    <?php if (!empty($filteredBooks))
                        $books = $filteredBooks; ?>
                    <?php foreach ($books as $book): ?>
                        <article class="book-card">
                            <div class="book-thumb"><?php echo htmlspecialchars($book['emoji'] ?? '📘'); ?></div>

                            <div class="book-body">
                                <div class="book-badges">
                                    <span class="status-badge <?php echo strtolower($book['status']) === 'available' ? 'available' : 'borrowed'; ?>"><?php echo htmlspecialchars($book['status']); ?></span>
                                    <span class="category-badge-tag"><?php echo htmlspecialchars($book['category']); ?></span>
                                    <span class="format-badge"><?php echo htmlspecialchars($book['format']); ?></span>
                                </div>

                                <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                                <p class="book-summary"><?php echo htmlspecialchars($book['summary']); ?></p>

                                <div class="meta-row">
                                    <span class="meta-item"><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></span>
                                    <span class="meta-item"><strong>Genre:</strong> <?php echo htmlspecialchars($book['genre']); ?></span>
                                </div>

                                <div class="card-actions">
                                    <?php $bookId = (int) ($book['id'] ?? 0); ?>
                                    <?php $isReserved = in_array($bookId, $reservedBookIds ?? [], true); ?>
                                    <?php if ($isReserved): ?>
                                        <button type="button" class="primary-button reserved-button" disabled>Reserved</button>
                                    <?php else: ?>
                                        <form method="POST" action="index.php?action=dashboard&page=search" style="display: inline;">
                                            <input type="hidden" name="reserve_book_id" value="<?php echo $bookId; ?>">
                                            <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                                            <button type="submit" class="primary-button">Reserve</button>
                                        </form>
                                    <?php endif; ?>
                                    <a class="secondary-button" href="index.php?action=dashboard&page=detail&id=<?php echo $bookId; ?>">View details</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>

</html>
