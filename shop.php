<?php
require_once 'includes/db.php';
require_once 'includes/tree_svgs.php';

// Guarantee authentication
require_login();

$userId = get_logged_in_user_id();

// Fetch current user details to check points
$stmtUser = $pdo->prepare("SELECT total_points FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$userPoints = $stmtUser->fetchColumn();

// Fetch all available trees in the shop
$stmtTrees = $pdo->query("SELECT id, tree_name, cost_points, image_url FROM tree_shop ORDER BY cost_points ASC");
$trees = $stmtTrees->fetchAll();

$pageTitle = "EcoFit Virtual Tree Shop";
$activePage = 'shop';
require_once 'includes/header.php';
?>

<div class="glass-panel">
    <h2 class="panel-title"><i class="fa-solid fa-store"></i> Virtual Tree Shop</h2>
    <p style="color: var(--text-muted); margin-bottom: 2rem;">
        Complete physical activities to earn points, then spend them here to purchase trees. 
        Select a tree below to proceed to your garden and plant it!
    </p>
    
    <div class="shop-grid">
        <?php foreach ($trees as $tree): 
            $canAfford = ($userPoints >= $tree['cost_points']);
        ?>
            <div class="shop-card">
                <div class="shop-tree-preview">
                    <?php echo get_tree_svg($tree['image_url']); ?>
                </div>
                <h3><?php echo htmlspecialchars($tree['tree_name']); ?></h3>
                
                <div class="shop-card-cost">
                    <i class="fa-solid fa-coins gold-coin"></i>
                    <span><?php echo number_format($tree['cost_points']); ?> pts</span>
                </div>
                
                <?php if ($canAfford): ?>
                    <a href="dashboard.php?plant_tree_id=<?php echo $tree['id']; ?>" class="btn" style="width: 100%;">
                        <i class="fa-solid fa-seedling"></i> Plant Now
                    </a>
                <?php else: ?>
                    <button class="btn" style="width: 100%; background: var(--border-light); color: var(--text-muted); cursor: not-allowed; box-shadow: none;" disabled>
                        <i class="fa-solid fa-ban"></i> Insufficient Points
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
