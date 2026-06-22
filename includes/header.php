<?php
require_once __DIR__ . '/db.php';

// Fetch current user details if logged in
$currentUser = null;
if (is_logged_in()) {
    $userId = get_logged_in_user_id();
    $stmt = $pdo->prepare("SELECT id, username, total_points FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch();
    
    // If user not found in DB but session exists, clear session
    if (!$currentUser) {
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'EcoFit - Gamified Fitness & Nature'; ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for beautiful icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="app-header">
        <div class="header-container">
            <a href="dashboard.php" class="logo">
                <i class="fa-solid fa-tree logo-icon"></i>
                <span>Eco<span>Fit</span></span>
            </a>
            
            <?php if (is_logged_in()): ?>
                <nav class="nav-menu">
                    <a href="dashboard.php" class="nav-link <?php echo ($activePage === 'dashboard') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </a>
                    <a href="shop.php" class="nav-link <?php echo ($activePage === 'shop') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-store"></i> Shop
                    </a>
                </nav>

                <div class="user-status">
                    <div class="points-badge" id="header-points-badge">
                        <i class="fa-solid fa-coins gold-coin animate-pulse"></i>
                        <span class="points-val"><?php echo number_format($currentUser['total_points']); ?></span> <span class="points-lbl">pts</span>
                    </div>
                    <div class="user-profile">
                        <span class="username"><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($currentUser['username']); ?></span>
                        <a href="index.php?action=logout" class="logout-btn" title="Logout">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="header-tagline">
                    Grow Your Garden by Staying Fit
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="main-content">
