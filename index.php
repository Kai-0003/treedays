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

<div class="auth-wrapper glass-panel">
    <div class="auth-brand-panel">
        <div class="auth-brand-logo">
            <i class="fa-solid fa-tree"></i> <span>Eco<span>Fit</span></span>
        </div>
        <div class="auth-brand-tagline">
            Turn your real-world exercise into a thriving virtual garden.
        </div>
        <ul class="feature-list">
            <li><i class="fa-solid fa-circle-check"></i> Complete customized daily exercise quests</li>
            <li><i class="fa-solid fa-circle-check"></i> Earn gold points for walking, running, and yoga</li>
            <li><i class="fa-solid fa-circle-check"></i> Spend points in the shop to purchase unique trees</li>
            <li><i class="fa-solid fa-circle-check"></i> Design and plant your customized virtual garden grid</li>
        </ul>
    </div>
    
    <div class="auth-form-panel">
        <div class="auth-tabs">
            <button type="button" class="auth-tab <?php echo ($activeTab === 'login') ? 'active' : ''; ?>" onclick="switchAuthTab('login')">Login</button>
            <button type="button" class="auth-tab <?php echo ($activeTab === 'register') ? 'active' : ''; ?>" onclick="switchAuthTab('register')">Register</button>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-banner">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form action="index.php" method="POST" id="login-form" class="auth-form <?php echo ($activeTab === 'login') ? 'active' : ''; ?>">
            <input type="hidden" name="auth_type" value="login">
            
            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="form-input-wrapper">
                    <i class="fa-regular fa-user"></i>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required value="<?php echo ($activeTab === 'login') ? htmlspecialchars($username ?? '') : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="form-input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In
            </button>
        </form>
        
        <!-- Register Form -->
        <form action="index.php" method="POST" id="register-form" class="auth-form <?php echo ($activeTab === 'register') ? 'active' : ''; ?>">
            <input type="hidden" name="auth_type" value="register">
            
            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="form-input-wrapper">
                    <i class="fa-regular fa-user"></i>
                    <input type="text" name="username" class="form-control" placeholder="Choose a username" required value="<?php echo ($activeTab === 'register') ? htmlspecialchars($username ?? '') : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="form-input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Create password (min. 6 chars)" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <div class="form-input-wrapper">
                    <i class="fa-solid fa-shield-halved"></i>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fa-solid fa-user-plus"></i> Create Account
            </button>
        </form>
    </div>
</div>

<script>
function switchAuthTab(tab) {
    // Hide all forms
    document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
    // Remove active tab highlights
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    
    if (tab === 'login') {
        document.getElementById('login-form').classList.add('active');
        document.querySelector('.auth-tab:nth-child(1)').classList.add('active');
    } else {
        document.getElementById('register-form').classList.add('active');
        document.querySelector('.auth-tab:nth-child(2)').classList.add('active');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
