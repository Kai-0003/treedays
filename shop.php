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

<div class="bg-surfaceSolid/50 border border-darkBorder backdrop-blur-xl rounded-2xl p-6 shadow-xl max-w-5xl mx-auto">
    <h2 class="text-xl font-black text-white mb-2 flex items-center gap-2 border-b border-darkBorder pb-4">
        <i class="fa-solid fa-store text-primary"></i> Virtual Tree Shop
    </h2>
    <p class="text-sm text-gray-400 mb-8 leading-relaxed">
        Complete physical activities to earn points, then spend them here to purchase trees. 
        Select a tree below to proceed to your garden and plant it!
    </p>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($trees as $tree): 
            $canAfford = ($userPoints >= $tree['cost_points']);
        ?>
            <div class="bg-base border border-darkBorder hover:border-primary/30 rounded-2xl p-5 flex flex-col items-center text-center transition-all duration-300 hover:scale-[1.02]">
                
                <!-- Tree Preview Round Wrapper -->
                <div class="w-24 h-24 mb-4 flex items-center justify-center bg-gradient-to-tr from-primary/10 to-transparent rounded-full border border-darkBorder shadow-inner animate-breeze">
                    <div class="w-[80%] h-[80%] flex items-center justify-center">
                        <?php echo get_tree_svg($tree['image_url']); ?>
                    </div>
                </div>
                
                <h3 class="font-bold text-white text-base mb-2 truncate w-full"><?php echo htmlspecialchars($tree['tree_name']); ?></h3>
                
                <div class="flex items-center gap-1.5 px-3 py-1 bg-accent/10 border border-accent/20 text-accent font-bold text-xs rounded-full mb-6">
                    <i class="fa-solid fa-coins"></i>
                    <span><?php echo number_format($tree['cost_points']); ?> pts</span>
                </div>
                
                <?php if ($canAfford): ?>
                    <a href="dashboard.php?plant_tree_id=<?php echo $tree['id']; ?>" class="w-full bg-primary hover:bg-primary-light text-white text-xs font-bold py-2 rounded-lg transition-all duration-200 flex items-center justify-center gap-1">
                        <i class="fa-solid fa-seedling"></i> Plant Now
                    </a>
                <?php else: ?>
                    <button class="w-full bg-darkBorder text-gray-500 text-xs font-bold py-2 rounded-lg cursor-not-allowed flex items-center justify-center gap-1" disabled>
                        <i class="fa-solid fa-ban"></i> Insufficient Points
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
