<?php
require_once __DIR__ . '/lang.php';
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
$theme = $_COOKIE['theme'] ?? 'dark';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" class="<?php echo $theme; ?> h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $pageTitle ?? 'EcoFit - Gamified Fitness & Nature'; ?></title>
    <!-- SVG Tree Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath d='M50 15 L20 60 L35 60 L15 90 L85 90 L65 60 L80 60 Z' fill='%2310b981'/%3E%3Crect x='45' y='90' width='10' height='10' fill='%23653b1b'/%3E%3C/svg%3E">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Local Tailwind CSS (Offline-friendly) -->
    <script src="assets/js/tailwind.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        base: 'var(--tailwind-base, #090d16)',
                        surface: 'var(--tailwind-surface, rgba(18, 25, 41, 0.7))',
                        surfaceSolid: 'var(--tailwind-surface-solid, #121929)',
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
                        darkBorder: 'var(--tailwind-border, rgba(255, 255, 255, 0.08))'
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
<body class="bg-base text-gray-900 dark:text-gray-100 flex flex-col min-h-full font-sans antialiased transition-colors duration-300">

    <!-- Desktop & Mobile Top Header Bar -->
    <header class="sticky top-0 z-50 bg-base/85 backdrop-blur-xl border-b border-darkBorder">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            
            <!-- Interactive Logo (acts as menu toggler on mobile when logged in) -->
            <a href="dashboard.php" 
               id="logo-link" 
               onclick="handleLogoClick(event)" 
               class="flex items-center gap-2 text-xl font-extrabold text-gray-900 dark:text-white select-none focus:outline-none">
                <i class="fa-solid fa-tree text-primary drop-shadow-[0_0_8px_var(--primary-glow)] animate-pulse"></i>
                <span>Eco<span class="text-primary">Fit</span></span>
            </a>
            
            <!-- Desktop Middle Navigation (Hidden on Mobile) -->
            <?php if (is_logged_in()): ?>
                <nav class="hidden md:flex items-center gap-1">
                    <a href="dashboard.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'dashboard') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-house"></i> <?php echo __('home'); ?>
                    </a>
                    <a href="steps.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'steps') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-shoe-prints"></i> <?php echo __('steps'); ?>
                    </a>
                    <a href="breakfast.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'breakfast') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-utensils"></i> <?php echo __('breakfast'); ?>
                    </a>
                    <a href="sleep.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'sleep') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-moon"></i> <?php echo __('sleep'); ?>
                    </a>
                    <a href="shop.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'shop') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-store"></i> <?php echo __('shop'); ?>
                    </a>
                    <a href="calendar.php" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2 <?php echo ($activePage === 'calendar') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                        <i class="fa-solid fa-calendar-days"></i> <?php echo __('calendar'); ?>
                    </a>
                </nav>
            <?php endif; ?>

            <!-- Right-side actions (Theme toggle, Lang select, Profile) -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Theme Toggle Button -->
                <button onclick="toggleThemeMode()" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg bg-surfaceSolid hover:bg-black/5 dark:hover:bg-white/5 border border-darkBorder text-gray-600 dark:text-gray-300 transition-all duration-200 focus:outline-none" title="Toggle Theme">
                    <i class="fa-solid fa-sun dark:hidden text-xs sm:text-sm"></i>
                    <i class="fa-solid fa-moon hidden dark:block text-xs sm:text-sm"></i>
                </button>

                <!-- Language Selector -->
                <div class="relative">
                    <select onchange="window.location.href='?lang='+this.value" class="bg-surfaceSolid hover:bg-black/5 dark:hover:bg-white/5 border border-darkBorder text-gray-600 dark:text-gray-300 py-1.5 px-2 rounded-lg text-xs sm:text-sm font-semibold focus:outline-none cursor-pointer">
                        <option value="en" <?php echo $lang === 'en' ? 'selected' : ''; ?>>🇬🇧 EN</option>
                        <option value="my" <?php echo $lang === 'my' ? 'selected' : ''; ?>>🇲🇲 MY</option>
                        <option value="ja" <?php echo $lang === 'ja' ? 'selected' : ''; ?>>🇯🇵 JA</option>
                        <option value="vi" <?php echo $lang === 'vi' ? 'selected' : ''; ?>>🇻🇳 VI</option>
                    </select>
                </div>

                <?php if (is_logged_in()): ?>
                    <!-- Points -->
                    <div class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 rounded-full bg-accent/10 border border-accent/30 text-accent font-bold" id="header-points-badge">
                        <i class="fa-solid fa-coins animate-pulse text-xs sm:text-sm"></i>
                        <span class="points-val text-xs sm:text-sm"><?php echo number_format($currentUser['total_points']); ?></span>
                        <span class="text-[10px] sm:text-xs opacity-75"><?php echo __('pts'); ?></span>
                    </div>

                    <!-- User Profile Dropdown Button -->
                    <div class="relative" id="profile-dropdown-container">
                        <button onclick="toggleProfileDropdown()" class="flex items-center gap-2 focus:outline-none group">
                            <!-- Avatar with Gradient -->
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gradient-to-tr from-primary to-teal-500 flex items-center justify-center text-white font-black text-xs uppercase shadow-md shadow-primary/20 ring-2 ring-primary/20 group-hover:ring-primary/50 transition-all duration-200">
                                <?php echo $userInitials; ?>
                            </div>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="profile-dropdown" class="hidden absolute right-0 mt-3 w-44 sm:w-48 bg-surfaceSolid border border-darkBorder rounded-xl shadow-2xl p-2 z-50 animate-slideIn">
                            <div class="px-3 py-2 border-b border-darkBorder mb-1">
                                <p class="text-[10px] text-gray-500"><?php echo __('logged_in_as'); ?></p>
                                <p class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($currentUser['username']); ?></p>
                            </div>
                            <a href="dashboard.php" class="flex items-center gap-2 px-3 py-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 hover:bg-black/5 dark:hover:bg-white/5 rounded-lg transition-all duration-200">
                                <i class="fa-solid fa-user-gear text-gray-400"></i> <?php echo __('profile'); ?>
                            </a>
                            <a href="index.php?action=logout" class="flex items-center gap-2 px-3 py-2 text-xs sm:text-sm text-red-500 dark:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> <?php echo __('logout'); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Mobile Off-Canvas Side Drawer (Collapsible) - Positioned Outside <header> to ignore containing block -->
    <?php if (is_logged_in()): ?>
        <!-- Backdrop Mask -->
        <div id="mobile-menu-backdrop" 
             onclick="toggleMobileMenu()" 
             class="fixed inset-0 z-40 bg-black/60 opacity-0 pointer-events-none transition-opacity duration-300 md:hidden"></div>
             
        <!-- Drawer Body -->
        <div id="mobile-menu" 
             class="fixed top-0 left-0 bottom-0 w-72 max-w-[80vw] z-50 bg-base border-r border-darkBorder/30 flex flex-col transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden shadow-2xl">
             
             <!-- Drawer Header -->
             <div class="h-16 px-6 border-b border-darkBorder/40 flex items-center justify-between shrink-0">
                 <div class="flex items-center gap-2 text-lg font-extrabold text-gray-900 dark:text-white">
                     <i class="fa-solid fa-tree text-primary"></i>
                     <span>Eco<span class="text-primary">Fit</span></span>
                 </div>
                 <button onclick="toggleMobileMenu()" class="text-gray-400 hover:text-gray-900 dark:hover:text-white p-1 rounded-lg border border-darkBorder/30 focus:outline-none">
                     <i class="fa-solid fa-xmark text-base"></i>
                 </button>
             </div>
             
             <!-- Drawer Scrollable Links -->
             <div class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
                 <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo ($activePage === 'dashboard') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                     <i class="fa-solid fa-house w-5 text-center"></i> <?php echo __('home'); ?>
                 </a>
                 <a href="steps.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo ($activePage === 'steps') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                     <i class="fa-solid fa-shoe-prints w-5 text-center"></i> <?php echo __('steps'); ?>
                 </a>
                 <a href="breakfast.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo ($activePage === 'breakfast') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                     <i class="fa-solid fa-utensils w-5 text-center"></i> <?php echo __('breakfast'); ?>
                 </a>
                 <a href="sleep.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo ($activePage === 'sleep') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                     <i class="fa-solid fa-moon w-5 text-center"></i> <?php echo __('sleep'); ?>
                 </a>
                 <a href="shop.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo ($activePage === 'shop') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                     <i class="fa-solid fa-store w-5 text-center"></i> <?php echo __('shop'); ?>
                 </a>
                 <a href="calendar.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold <?php echo ($activePage === 'calendar') ? 'bg-primary/10 text-primary dark:text-primary-light border border-primary/20' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5'; ?>">
                     <i class="fa-solid fa-calendar-days w-5 text-center"></i> <?php echo __('calendar'); ?>
                 </a>
             </div>
             
             <!-- Drawer Footer -->
             <div class="p-4 border-t border-darkBorder/40 bg-surfaceSolid/30 shrink-0">
                 <!-- Language & Theme Settings for Mobile -->
                 <div class="flex items-center justify-between gap-2 mb-4">
                     <select onchange="window.location.href='?lang='+this.value" class="flex-1 bg-surfaceSolid border border-darkBorder text-gray-700 dark:text-gray-300 py-2 px-3 rounded-xl text-sm font-semibold focus:outline-none cursor-pointer">
                         <option value="en" <?php echo $lang === 'en' ? 'selected' : ''; ?>>🇬🇧 English</option>
                         <option value="my" <?php echo $lang === 'my' ? 'selected' : ''; ?>>🇲🇲 မြန်မာ</option>
                         <option value="ja" <?php echo $lang === 'ja' ? 'selected' : ''; ?>>🇯🇵 日本語</option>
                         <option value="vi" <?php echo $lang === 'vi' ? 'selected' : ''; ?>>🇻🇳 Tiếng Việt</option>
                     </select>
                     <button onclick="toggleThemeMode()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-surfaceSolid border border-darkBorder text-gray-600 dark:text-gray-300 transition-all duration-200" title="Toggle Theme">
                         <i class="fa-solid fa-sun dark:hidden text-sm"></i>
                         <i class="fa-solid fa-moon hidden dark:block text-sm"></i>
                     </button>
                 </div>

                 <div class="flex items-center gap-3 mb-4">
                     <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-primary to-teal-500 flex items-center justify-center text-white font-black text-xs uppercase shadow-md shadow-primary/20">
                         <?php echo $userInitials; ?>
                     </div>
                     <div class="truncate">
                         <p class="text-[10px] text-gray-500"><?php echo __('logged_in_as'); ?></p>
                         <p class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate"><?php echo htmlspecialchars($currentUser['username']); ?></p>
                     </div>
                 </div>
                 <a href="index.php?action=logout" class="w-full py-2.5 rounded-xl text-xs font-bold text-red-400 border border-red-500/30 bg-red-500/5 hover:bg-red-500/10 flex items-center justify-center gap-2 transition-all duration-200">
                     <i class="fa-solid fa-arrow-right-from-bracket"></i> <?php echo __('logout'); ?>
                 </a>
             </div>
        </div>
    <?php endif; ?>

    <!-- Mobile Bottom Navigation (Helper toolbar) -->
    <?php if (is_logged_in()): ?>
        <nav class="md:hidden fixed bottom-0 left-0 right-0 z-35 bg-base/90 backdrop-blur-xl border-t border-darkBorder px-4 h-16 flex items-center justify-around">
            <a href="dashboard.php" class="flex flex-col items-center gap-0.5 text-[10px] font-bold transition-all duration-200 <?php echo ($activePage === 'dashboard') ? 'text-primary' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'; ?>">
                <i class="fa-solid fa-house text-base"></i>
                <span><?php echo __('home'); ?></span>
            </a>
            <a href="steps.php" class="flex flex-col items-center gap-0.5 text-[10px] font-bold transition-all duration-200 <?php echo ($activePage === 'steps') ? 'text-primary' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'; ?>">
                <i class="fa-solid fa-shoe-prints text-base"></i>
                <span><?php echo __('steps'); ?></span>
            </a>
            <a href="breakfast.php" class="flex flex-col items-center gap-0.5 text-[10px] font-bold transition-all duration-200 <?php echo ($activePage === 'breakfast') ? 'text-primary' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'; ?>">
                <i class="fa-solid fa-utensils text-base"></i>
                <span><?php echo __('breakfast'); ?></span>
            </a>
            <a href="sleep.php" class="flex flex-col items-center gap-0.5 text-[10px] font-bold transition-all duration-200 <?php echo ($activePage === 'sleep') ? 'text-primary' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'; ?>">
                <i class="fa-solid fa-moon text-base"></i>
                <span><?php echo __('sleep'); ?></span>
            </a>
            <a href="shop.php" class="flex flex-col items-center gap-0.5 text-[10px] font-bold transition-all duration-200 <?php echo ($activePage === 'shop') ? 'text-primary' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'; ?>">
                <i class="fa-solid fa-store text-base"></i>
                <span><?php echo __('shop'); ?></span>
            </a>
            <a href="calendar.php" class="flex flex-col items-center gap-0.5 text-[10px] font-bold transition-all duration-200 <?php echo ($activePage === 'calendar') ? 'text-primary' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'; ?>">
                <i class="fa-solid fa-calendar-days text-base"></i>
                <span><?php echo __('calendar'); ?></span>
            </a>
        </nav>
    <?php endif; ?>

    <!-- Main Container -->
    <main class="flex-1 w-full max-w-6xl mx-auto px-4 py-6 mb-16 md:mb-6">
