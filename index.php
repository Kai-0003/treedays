<?php
require_once 'includes/db.php';

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Redirect to dashboard if already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$activeTab = 'login'; // 'login' or 'register'

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['auth_type']) && $_POST['auth_type'] === 'login') {
        $activeTab = 'login';
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            // Find user securely
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Initialize today's quests for this user
                initialize_user_quests($pdo, $user['id']);
                
                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    } elseif (isset($_POST['auth_type']) && $_POST['auth_type'] === 'register') {
        $activeTab = 'register';
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($username) || empty($password) || empty($confirmPassword)) {
            $error = 'Please fill in all fields.';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $error = 'Username is already taken.';
            } else {
                // Register user
                try {
                    $pdo->beginTransaction();
                    
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmtInsert = $pdo->prepare("INSERT INTO users (username, password_hash, total_points) VALUES (?, ?, 0)");
                    $stmtInsert->execute([$username, $passwordHash]);
                    $newUserId = $pdo->lastInsertId();
                    
                    $pdo->commit();
                    
                    // Set session and log them in
                    $_SESSION['user_id'] = $newUserId;
                    $_SESSION['username'] = $username;
                    
                    // Initialize today's quests
                    initialize_user_quests($pdo, $newUserId);
                    
                    header("Location: dashboard.php");
                    exit;
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $error = 'Registration failed: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = "Welcome to EcoFit - Login / Register";
require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto my-8 grid grid-cols-1 md:grid-cols-2 rounded-2xl overflow-hidden shadow-2xl border border-darkBorder bg-surfaceSolid/50 backdrop-blur-xl">
    
    <!-- Left Column: Branding Details (Hidden on small screens) -->
    <div class="hidden md:flex flex-col justify-center p-8 bg-gradient-to-tr from-emerald-950/80 to-base text-gray-100 border-r border-darkBorder">
        <div class="flex items-center gap-3 text-3xl font-black mb-6">
            <i class="fa-solid fa-tree text-primary drop-shadow-[0_0_10px_var(--primary-glow)]"></i>
            <span>Eco<span class="text-primary">Fit</span></span>
        </div>
        <p class="text-gray-400 text-lg mb-8 leading-relaxed">
            Turn your real-world exercise into a thriving virtual garden.
        </p>
        <ul class="space-y-4">
            <li class="flex items-center gap-3 text-sm text-gray-300">
                <i class="fa-solid fa-circle-check text-primary"></i> Complete customized daily exercise quests
            </li>
            <li class="flex items-center gap-3 text-sm text-gray-300">
                <i class="fa-solid fa-circle-check text-primary"></i> Earn gold points for walking, running, and yoga
            </li>
            <li class="flex items-center gap-3 text-sm text-gray-300">
                <i class="fa-solid fa-circle-check text-primary"></i> Spend points in the shop to purchase unique trees
            </li>
            <li class="flex items-center gap-3 text-sm text-gray-300">
                <i class="fa-solid fa-circle-check text-primary"></i> Design and plant your customized virtual garden grid
            </li>
        </ul>
    </div>
    
    <!-- Right Column: Authentication Forms -->
    <div class="p-8 flex flex-col justify-center">
        <!-- Tabs -->
        <div class="flex border-b border-darkBorder mb-6">
            <button type="button" 
                    id="tab-login"
                    class="flex-1 pb-3 text-center text-sm font-semibold transition-all duration-200 <?php echo ($activeTab === 'login') ? 'text-primary border-b-2 border-primary font-bold' : 'text-gray-400 hover:text-white'; ?>" 
                    onclick="switchAuthTab('login')">
                Login
            </button>
            <button type="button" 
                    id="tab-register"
                    class="flex-1 pb-3 text-center text-sm font-semibold transition-all duration-200 <?php echo ($activeTab === 'register') ? 'text-primary border-b-2 border-primary font-bold' : 'text-gray-400 hover:text-white'; ?>" 
                    onclick="switchAuthTab('register')">
                Register
            </button>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-400 p-3 rounded-lg text-sm mb-6 animate-pulse">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form action="index.php" method="POST" id="login-form" class="<?php echo ($activeTab === 'login') ? '' : 'hidden'; ?> space-y-4">
            <input type="hidden" name="auth_type" value="login">
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Username</label>
                <div class="relative">
                    <i class="fa-regular fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" 
                           name="username" 
                           class="w-full bg-base border border-darkBorder focus:border-primary/50 focus:ring-1 focus:ring-primary/20 rounded-lg py-2.5 pl-10 pr-4 text-sm text-white focus:outline-none transition-all duration-200" 
                           placeholder="Enter username" 
                           required 
                           value="<?php echo ($activeTab === 'login') ? htmlspecialchars($username ?? '') : ''; ?>">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="password" 
                           name="password" 
                           class="w-full bg-base border border-darkBorder focus:border-primary/50 focus:ring-1 focus:ring-primary/20 rounded-lg py-2.5 pl-10 pr-4 text-sm text-white focus:outline-none transition-all duration-200" 
                           placeholder="Enter password" 
                           required>
                </div>
            </div>
            
            <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-bold text-white bg-primary hover:bg-primary-light transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:scale-[1.01]">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In
            </button>
        </form>
        
        <!-- Register Form -->
        <form action="index.php" method="POST" id="register-form" class="<?php echo ($activeTab === 'register') ? '' : 'hidden'; ?> space-y-4">
            <input type="hidden" name="auth_type" value="register">
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Username</label>
                <div class="relative">
                    <i class="fa-regular fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" 
                           name="username" 
                           class="w-full bg-base border border-darkBorder focus:border-primary/50 focus:ring-1 focus:ring-primary/20 rounded-lg py-2.5 pl-10 pr-4 text-sm text-white focus:outline-none transition-all duration-200" 
                           placeholder="Choose a username" 
                           required 
                           value="<?php echo ($activeTab === 'register') ? htmlspecialchars($username ?? '') : ''; ?>">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="password" 
                           name="password" 
                           class="w-full bg-base border border-darkBorder focus:border-primary/50 focus:ring-1 focus:ring-primary/20 rounded-lg py-2.5 pl-10 pr-4 text-sm text-white focus:outline-none transition-all duration-200" 
                           placeholder="Create password (min. 6 chars)" 
                           required>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Confirm Password</label>
                <div class="relative">
                    <i class="fa-solid fa-shield-halved absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="password" 
                           name="confirm_password" 
                           class="w-full bg-base border border-darkBorder focus:border-primary/50 focus:ring-1 focus:ring-primary/20 rounded-lg py-2.5 pl-10 pr-4 text-sm text-white focus:outline-none transition-all duration-200" 
                           placeholder="Confirm your password" 
                           required>
                </div>
            </div>
            
            <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-bold text-white bg-primary hover:bg-primary-light transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:scale-[1.01]">
                <i class="fa-solid fa-user-plus"></i> Create Account
            </button>
        </form>
    </div>
</div>

<script>
function switchAuthTab(tab) {
    const formLogin = document.getElementById('login-form');
    const formRegister = document.getElementById('register-form');
    const btnLogin = document.getElementById('tab-login');
    const btnRegister = document.getElementById('tab-register');
    
    if (tab === 'login') {
        formLogin.classList.remove('hidden');
        formRegister.classList.add('hidden');
        btnLogin.className = "flex-1 pb-3 text-center text-sm font-semibold transition-all duration-200 text-primary border-b-2 border-primary font-bold";
        btnRegister.className = "flex-1 pb-3 text-center text-sm font-semibold transition-all duration-200 text-gray-400 hover:text-white";
    } else {
        formLogin.classList.add('hidden');
        formRegister.classList.remove('hidden');
        btnLogin.className = "flex-1 pb-3 text-center text-sm font-semibold transition-all duration-200 text-gray-400 hover:text-white";
        btnRegister.className = "flex-1 pb-3 text-center text-sm font-semibold transition-all duration-200 text-primary border-b-2 border-primary font-bold";
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
