<?php
require_once __DIR__ . '/../controllers/BookController.php';
require_once __DIR__ . '/../controllers/ReaderController.php';

$baseUrl = 'index.php?action=dashboard&'; // Base URL for routing

// 1. Determine active page route from URL query parameter
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'book';

// 2. Route controller call based on clicked menu item
$content = '';
switch ($currentPage) {
    case 'reader':
        $controller = new ReaderController();
        $content = $controller->index();
        break;
    case 'book':
    default:
        $controller = new BookController();
        $content = $controller->index();
        break;
}

// 3. Menu configuration with slugs for routing
$menuItems = [
    [
        'title' => 'Book Management',
        'slug' => 'book',
        'icon' => 'fa-solid fa-book',
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
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
        }

        body {
            background-color: #f4f7f6;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Container with Teal Gradient */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1fa37a 0%, #20b887 50%, #2ec893 100%);
            color: #ffffff;
            padding: 24px 16px;
            min-height: 100vh;
        }

        .sidebar-header {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 20px;
            padding-left: 8px;
        }

        /* Nav Structure */
        .nav-list {
            list-style: none;
        }

        .nav-item {
            position: relative;
            margin-bottom: 4px;
        }

        /* Main Menu Header Link */
        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
        }

        .nav-icon {
            width: 28px;
            font-size: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0.9;
        }

        .nav-text {
            flex-grow: 1;
            margin-left: 10px;
        }

        .chevron {
            font-size: 11px;
            opacity: 0.6;
            transition: transform 0.2s ease;
        }

        /* Continuous Vertical Tree Line for Expanded Menus */
        .submenu-wrapper {
            position: relative;
            padding-left: 14px;
        }

        .submenu-wrapper::before {
            content: '';
            position: absolute;
            left: 13px;
            top: -5px;
            bottom: 12px;
            width: 1.5px;
            background-color: rgba(255, 255, 255, 0.3);
        }

        .submenu {
            list-style: none;
            padding-left: 20px;
        }

        .submenu-item {
            margin: 3px 0;
        }

        /* Submenu Items Styling */
        .submenu-link {
            display: block;
            padding: 9px 16px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .submenu-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.15);
        }

        /* Active Item Style */
        .submenu-item.active .submenu-link {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
            font-weight: 700;
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        /* Content Area */
        .main-content {
            flex-grow: 1;
        }
    </style>
</head>

<body>

    <!-- Sidebar Starts Here -->
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
        </ul>
    </aside>

    <!-- Main Content Area where Controller Output Displays -->
    <main class="main-content">
        <?= $content ?>
    </main>

</body>

</html>