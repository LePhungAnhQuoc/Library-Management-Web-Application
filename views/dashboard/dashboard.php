<?php
require_once __DIR__ . '/../../controllers/BookController.php';
require_once __DIR__ . '/../../controllers/ReaderController.php';
require_once __DIR__ . '/../../controllers/LoanController.php';

$baseUrl = 'index.php?action=dashboard&';
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'book';

$content = '';
switch ($currentPage) {
    case 'reader':
        $controller = new ReaderController();
        $content = $controller->index();
        break;
    case 'account':
        $controller = new LoanController();
        $content = $controller->dashboard();
        break;
    case 'search':
        $controller = new BookController();
        $content = $controller->browse();
        break;
    case 'detail':
        $controller = new BookController();
        $content = $controller->detail();
        break;
    case 'book':
    default:
        $controller = new BookController();
        $content = $controller->index();
        break;
}

$menuItems = [
    [
        'title' => 'My Account',
        'slug' => 'account',
        'icon' => 'fa-solid fa-user',
        'expanded' => false,
        'submenu' => []
    ],
    [
        'title' => 'Book Management',
        'slug' => 'book',
        'icon' => 'fa-solid fa-book',
        'expanded' => false,
        'submenu' => []
    ],
    [
        'title' => 'Search & Browse',
        'slug' => 'search',
        'icon' => 'fa-solid fa-magnifying-glass',
        'expanded' => false,
        'submenu' => []
    ],
    [
        'title' => 'Reader Management',
        'slug' => 'reader',
        'icon' => 'fa-sharp-duotone fa-solid fa-book-open-reader',
        'expanded' => false,
        'submenu' => []
    ],
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Navigation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../views/dashboard/dashboard.css">
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-header">Menu</div>

        <ul class="nav-list">
            <?php foreach ($menuItems as $item): ?>
                <?php $isMainActive = ($currentPage === $item['slug']); ?>
                <li class="nav-item">
                    <a href="<?= $baseUrl ?>page=<?= urlencode($item['slug']) ?>" class="nav-link <?= $isMainActive ? 'active' : '' ?>">
                        <span class="nav-icon"><i class="<?= $item['icon'] ?>"></i></span>
                        <span class="nav-text"><?= htmlspecialchars($item['title']) ?></span>

                        <?php if (!empty($item['submenu'])): ?>
                            <i class="fas <?= $item['expanded'] ? 'fa-chevron-up' : 'fa-chevron-down' ?> chevron"></i>
                        <?php else: ?>
                            <i class="fas fa-chevron-down chevron"></i>
                        <?php endif; ?>
                    </a>

                    <?php if (!empty($item['submenu']) && $item['expanded']): ?>
                        <div class="submenu-wrapper">
                            <ul class="submenu">
                                <?php foreach ($item['submenu'] as $sub): ?>
                                    <?php $isActive = ($currentPage === $sub['slug']); ?>
                                    <li class="submenu-item <?= $isActive ? 'active' : '' ?>">
                                        <a href="<?= $baseUrl ?>page=<?= urlencode($sub['slug']) ?>" class="submenu-link">
                                            <?= htmlspecialchars($sub['title']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>

            <li class="nav-item logout-item">
                <a href="index.php?action=logout" class="nav-link logout-link">
                    <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <?= $content ?>
    </main>

</body>

</html>
