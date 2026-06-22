<?php
require_once __DIR__ . '/db.php';

// Fetch current user details if logged in
$currentUser = null;
$userInitials = 'U';
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
    // Generate avatar initials
    $userInitials = strtoupper(substr($currentUser['username'], 0, 2));
}

// Map active page classes
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $pageTitle ?? 'EcoFit - Gamified Fitness & Nature'; ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        base: '#090d16',
                        surface: 'rgba(18, 25, 41, 0.7)',
                        surfaceSolid: '#121929',
                        primary: {
                            DEFAULT: '#10b981',
                            light: '#34d399',
                            glow: 'rgba(16, 185, 129, 0.2)',
                        },
                        accent: {
                            DEFAULT: '#f59e0b',
                            light: '#fbbf24',
                            glow: 'rgba(245, 158, 11, 0.15)',
                        },
                        darkBorder: 'rgba(255, 255, 255, 0.08)'
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Custom animations and overrides -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-base text-gray-100 flex flex-col min-h-full font-sans antialiased">

    <!-- Desktop & Mobile Top Header Bar -->
    <header class="sticky top-0 z-50 bg-base/80 backdrop-blur-xl border-b border-darkBorder">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="dashboard.php" class="flex items-center gap-2 text-xl font-extrabold text-white">
                <i class="fa-solid fa-tree text-primary drop-shadow-[0_0_8px_var(--primary-glow)] animate-bounce"></i>
                <span>Eco<span class="text-primary">Fit</span></span>
            </a>
            
            <!-- Desktop Middle Navigation (Hidden on Mobile) -->
            <?php if (is_logged_in()): ?>
                <nav class="hidden md:flex items-center gap-1">
                    <a href="dashboard.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'dashboard') ? 'bg-primary/10 text-primary-light border border-primary/20' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-house"></i> Home
                    </a>
                    <a href="stepbreakfast.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'stepbreakfast') ? 'bg-primary/10 text-primary-light border border-primary/20' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-utensils"></i> StepBreakfast
                    </a>
                    <a href="sleep.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'sleep') ? 'bg-primary/10 text-primary-light border border-primary/20' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-moon"></i> Sleep
                    </a>
                    <a href="shop.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'shop') ? 'bg-primary/10 text-primary-light border border-primary/20' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-store"></i> Shop
                    </a>
                    <a href="calendar.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'calendar') ? 'bg-primary/10 text-primary-light border border-primary/20' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-calendar-days"></i> Calendar
                    </a>
                </nav>

                <!-- Right-side actions -->
                <div class="flex items-center gap-4">
                    <!-- Points -->
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-accent/10 border border-accent/30 text-accent font-bold" id="header-points-badge">
                        <i class="fa-solid fa-coins animate-pulse"></i>
                        <span class="points-val text-sm"><?php echo number_format($currentUser['total_points']); ?></span>
                        <span class="text-xs opacity-75">pts</span>
                    </div>

                    <!-- User Profile Dropdown Button -->
                    <div class="relative" id="profile-dropdown-container">
                        <button onclick="toggleProfileDropdown()" class="flex items-center gap-2 focus:outline-none group">
                            <!-- Avatar with Gradient -->
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-primary to-teal-500 flex items-center justify-center text-white font-black text-xs uppercase shadow-md shadow-primary/20 ring-2 ring-primary/20 group-hover:ring-primary/50 transition-all duration-200">
                                <?php echo $userInitials; ?>
                            </div>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="profile-dropdown" class="hidden absolute right-0 mt-3 w-48 bg-surfaceSolid border border-darkBorder rounded-xl shadow-2xl p-2 z-50 animate-slideIn">
                            <div class="px-3 py-2 border-b border-darkBorder mb-1">
                                <p class="text-xs text-gray-500">Logged in as</p>
                                <p class="text-sm font-bold text-white truncate"><?php echo htmlspecialchars($currentUser['username']); ?></p>
                            </div>
                            <a href="dashboard.php" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-300 hover:bg-white/5 rounded-lg transition-all duration-200">
                                <i class="fa-solid fa-user-gear text-gray-400"></i> Profile
                            </a>
                            <a href="index.php?action=logout" class="flex items-center gap-2 px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-sm font-medium text-gray-400">
                    Grow Your Garden by Staying Fit
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Mobile Bottom Navigation (Visible only on Mobile md:hidden) -->
    <?php if (is_logged_in()): ?>
        <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-base/90 backdrop-blur-xl border-t border-darkBorder px-4 h-16 flex items-center justify-around">
            <a href="dashboard.php" class="flex flex-col items-center gap-0.5 text-xs font-bold transition-all duration-200 <?php echo ($activePage === 'dashboard') ? 'text-primary-light' : 'text-gray-500 hover:text-white'; ?>">
                <i class="fa-solid fa-house text-lg"></i>
                <span>Home</span>
            </a>
            <a href="stepbreakfast.php" class="flex flex-col items-center gap-0.5 text-xs font-bold transition-all duration-200 <?php echo ($activePage === 'stepbreakfast') ? 'text-primary-light' : 'text-gray-500 hover:text-white'; ?>">
                <i class="fa-solid fa-utensils text-lg"></i>
                <span>StepBreakfast</span>
            </a>
            <a href="sleep.php" class="flex flex-col items-center gap-0.5 text-xs font-bold transition-all duration-200 <?php echo ($activePage === 'sleep') ? 'text-primary-light' : 'text-gray-500 hover:text-white'; ?>">
                <i class="fa-solid fa-moon text-lg"></i>
                <span>Sleep</span>
            </a>
            <a href="shop.php" class="flex flex-col items-center gap-0.5 text-xs font-bold transition-all duration-200 <?php echo ($activePage === 'shop') ? 'text-primary-light' : 'text-gray-500 hover:text-white'; ?>">
                <i class="fa-solid fa-store text-lg"></i>
                <span>Shop</span>
            </a>
            <a href="calendar.php" class="flex flex-col items-center gap-0.5 text-xs font-bold transition-all duration-200 <?php echo ($activePage === 'calendar') ? 'text-primary-light' : 'text-gray-500 hover:text-white'; ?>">
                <i class="fa-solid fa-calendar-days text-lg"></i>
                <span>Calendar</span>
            </a>
        </nav>
    <?php endif; ?>

    <!-- Main Container -->
    <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-6 mb-16 md:mb-6">
