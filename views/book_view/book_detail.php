<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Details</title>
    <style>
        <?php include 'book_view.css'; ?>
    </style>
</head>

<body>
    <div class="detail-page">
        <?php if (!empty($book)): ?>
            <div class="detail-shell">
                <a class="back-link" href="index.php?action=dashboard&page=search">
                    <span>←</span> Back to browse
                </a>

                <div class="detail-hero">
                    <div class="detail-cover"><?php echo htmlspecialchars($book['emoji'] ?? '📘'); ?></div>

                    <div class="detail-info">
                        <div class="book-badges">
                            <span class="status-badge <?php echo strtolower($book['status'] ?? 'Available') === 'available' ? 'available' : 'borrowed'; ?>"><?php echo htmlspecialchars($book['status'] ?? 'Available'); ?></span>
                            <span class="category-badge-tag"><?php echo htmlspecialchars($book['category'] ?? 'General'); ?></span>
                            <span class="format-badge"><?php echo htmlspecialchars($book['format'] ?? 'Print'); ?></span>
                        </div>

                        <h1 class="detail-title"><?php echo htmlspecialchars($book['title'] ?? 'Untitled Book'); ?></h1>
                        <p class="detail-author">by <?php echo htmlspecialchars($book['author'] ?? 'Unknown Author'); ?></p>

                        <div class="meta-row">
                            <span class="meta-item"><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></span>
                            <span class="meta-item"><strong>Genre:</strong> <?php echo htmlspecialchars($book['genre'] ?? 'N/A'); ?></span>
                            <span class="meta-item"><strong>Category:</strong> <?php echo htmlspecialchars($book['category'] ?? 'General'); ?></span>
                        </div>

                        <div class="card-actions">
                            <button type="button" class="primary-button">Reserve</button>
                            <a class="secondary-button" href="index.php?action=dashboard&page=search">Browse more</a>
                        </div>
                    </div>
                </div>

                <div class="detail-sections">
                    <section class="detail-section">
                        <h2>About this book</h2>
                        <p><?php echo nl2br(htmlspecialchars($book['summary'] ?? 'No summary available for this book yet.')); ?></p>
                    </section>

                    <section class="detail-grid">
                        <div class="detail-card">
                            <span class="detail-card-label">Availability</span>
                            <strong><?php echo htmlspecialchars($book['status'] ?? 'Available'); ?></strong>
                        </div>
                        <div class="detail-card">
                            <span class="detail-card-label">Format</span>
                            <strong><?php echo htmlspecialchars($book['format'] ?? 'Print'); ?></strong>
                        </div>
                        <div class="detail-card">
                            <span class="detail-card-label">Author</span>
                            <strong><?php echo htmlspecialchars($book['author'] ?? 'Unknown Author'); ?></strong>
                        </div>
                        <div class="detail-card">
                            <span class="detail-card-label">Category</span>
                            <strong><?php echo htmlspecialchars($book['category'] ?? 'General'); ?></strong>
                        </div>
                    </section>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                The requested book could not be found.
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
