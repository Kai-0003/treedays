<?php
require_once 'includes/db.php';
require_once 'includes/tree_svgs.php';

// Guarantee authentication
require_login();

$userId = get_logged_in_user_id();

// Auto initialize today's quests
initialize_user_quests($pdo, $userId);

// Fetch today's quests for the user
$today = date('Y-m-d');
$stmtQuests = $pdo->prepare("
    SELECT uq.id as user_quest_id, uq.progress, uq.is_completed, 
           dq.title, dq.description, dq.target_value, dq.points_reward
    FROM user_quests uq
    JOIN daily_quests dq ON uq.quest_id = dq.id
    WHERE uq.user_id = ? AND uq.quest_date = ?
");
$stmtQuests->execute([$userId, $today]);
$quests = $stmtQuests->fetchAll();

// Fetch all trees in user's garden
$stmtGarden = $pdo->prepare("
    SELECT ug.x_coordinate, ug.y_coordinate, ts.tree_name, ts.image_url 
    FROM user_garden ug
    JOIN tree_shop ts ON ug.tree_id = ts.id
    WHERE ug.user_id = ?
");
$stmtGarden->execute([$userId]);
$plantedTrees = $stmtGarden->fetchAll();

// Map planted trees by coordinate grid: y_coordinate => x_coordinate => tree details
$gardenGrid = [];
for ($y = 0; $y < 6; $y++) {
    for ($x = 0; $x < 6; $x++) {
        $gardenGrid[$y][$x] = null;
    }
}
foreach ($plantedTrees as $tree) {
    $y = intval($tree['y_coordinate']);
    $x = intval($tree['x_coordinate']);
    if ($y >= 0 && $y < 6 && $x >= 0 && $x < 6) {
        $gardenGrid[$y][$x] = $tree;
    }
}

// Fetch tree types in shop for quick planting overlay
$stmtShop = $pdo->query("SELECT id, tree_name, cost_points, image_url FROM tree_shop ORDER BY cost_points ASC");
$shopTrees = $stmtShop->fetchAll();

$pageTitle = "My EcoFit Dashboard";
$activePage = 'dashboard';
require_once 'includes/header.php';
?>

<!-- Mobile-first Stack Layout: Garden on Top for Mobile, Side-by-Side on Desktop -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <!-- Virtual Garden Section (Prominent on top for mobile, right column on desktop) -->
    <div class="col-span-1 lg:col-span-7 order-1 lg:order-2">
        <div class="bg-surfaceSolid/50 border border-darkBorder backdrop-blur-xl rounded-2xl p-4 sm:p-6 shadow-xl">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-darkBorder pb-3">
                <i class="fa-solid fa-seedling text-primary"></i> <?php echo __('virtual_garden_title'); ?>
            </h2>
            
            <div class="flex flex-col items-center gap-4">
                <!-- Garden Grid Container (Responsive fit) -->
                <div class="w-full max-w-md aspect-square bg-gradient-to-tr from-primary/5 to-transparent border border-darkBorder p-3 rounded-2xl flex items-center justify-center">
                    <div class="grid grid-cols-6 grid-rows-6 gap-1 w-full h-full" id="garden-grid">
                        <?php for ($y = 0; $y < 6; $y++): ?>
                            <?php for ($x = 0; $x < 6; $x++): 
                                $tree = $gardenGrid[$y][$x];
                                $cellClass = $tree ? 'occupied' : 'empty';
                            ?>
                                <div class="garden-cell <?php echo $cellClass; ?> relative aspect-square bg-emerald-950/10 border border-emerald-950/20 hover:border-primary/50 hover:bg-emerald-950/20 rounded-lg flex items-center justify-center cursor-pointer transition-all duration-200" 
                                     data-x="<?php echo $x; ?>" 
                                     data-y="<?php echo $y; ?>"
                                     <?php if (!$tree): ?> onclick="selectGardenCell(this)" <?php endif; ?>>
                                    
                                    <?php if ($tree): ?>
                                        <div class="w-[85%] h-[85%] flex items-center justify-center" title="<?php echo htmlspecialchars(__($tree['tree_name'])); ?> <?php echo __('planting_at'); ?> (<?php echo $x; ?>, <?php echo $y; ?>)">
                                            <?php echo get_tree_svg($tree['image_url']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <span class="absolute bottom-1 right-1 text-[8px] text-gray-600 select-none pointer-events-none"><?php echo $x; ?>,<?php echo $y; ?></span>
                                </div>
                            <?php endfor; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <!-- Action / Plant Panel Overlay -->
                <div class="w-full min-h-[40px] flex justify-center items-center py-2">
                    <div id="selection-prompt" class="text-sm text-gray-500 dark:text-gray-400 text-center flex items-center gap-2">
                        <i class="fa-solid fa-arrow-pointer text-primary animate-pulse"></i>
                        <span><?php echo __('click_slot'); ?></span>
                    </div>
                    
                    <div id="plant-panel" class="hidden items-center flex-wrap justify-center gap-3 animate-slideIn">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300"><?php echo __('planting_at'); ?> (<strong id="selected-coords" class="text-primary">0,0</strong>):</span>
                        <select id="quick-tree-select" class="bg-base border border-darkBorder text-gray-900 dark:text-white text-sm rounded-lg py-1.5 px-3 focus:outline-none focus:border-primary/50">
                            <?php foreach ($shopTrees as $shopTree): ?>
                                <option value="<?php echo $shopTree['id']; ?>">
                                    <?php echo htmlspecialchars(__($shopTree['tree_name'])); ?> (<?php echo $shopTree['cost_points']; ?> <?php echo __('pts'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="bg-primary hover:bg-primary-light text-white text-xs font-bold py-1.5 px-4 rounded-lg flex items-center gap-1.5 transition-all duration-200" onclick="purchaseAndPlantTree()">
                            <i class="fa-solid fa-leaf"></i> <?php echo __('plant_tree_btn'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Fitness Quests Section (Left column on desktop, bottom on mobile) -->
    <div class="col-span-1 lg:col-span-5 order-2 lg:order-1">
        <div class="bg-surfaceSolid/50 border border-darkBorder backdrop-blur-xl rounded-2xl p-4 sm:p-6 shadow-xl">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-darkBorder pb-3">
                <i class="fa-solid fa-person-running text-primary"></i> <?php echo __('daily_quests_title'); ?>
            </h2>
            
            <div class="space-y-4">
                <?php if (empty($quests)): ?>
                    <p class="text-center text-gray-500 my-8">No quests loaded. Database needs to be seeded.</p>
                <?php else: ?>
                    <?php foreach ($quests as $quest): 
                        $percentage = min(100, round(($quest['progress'] / $quest['target_value']) * 100));
                        $isCompleted = (bool)$quest['is_completed'];
                    ?>
                        <div class="border border-darkBorder bg-white/[0.01] hover:bg-white/[0.02] <?php echo $isCompleted ? 'border-primary/30 bg-primary/[0.01]' : ''; ?> rounded-xl p-4 transition-all duration-200" id="quest-card-<?php echo $quest['user_quest_id']; ?>">
                            
                            <!-- Card Header -->
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base leading-tight mb-1"><?php echo htmlspecialchars(__($quest['title'])); ?></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-normal"><?php echo htmlspecialchars(__($quest['description'])); ?></p>
                                </div>
                                <div class="bg-accent/10 text-accent border border-accent/20 text-xs font-bold px-2 py-1 rounded flex items-center gap-1 shrink-0">
                                    <i class="fa-solid fa-coins text-accent"></i> +<?php echo $quest['points_reward']; ?> <?php echo __('pts'); ?>
                                </div>
                            </div>
                            
                            <!-- Progress Section -->
                            <div class="mt-4 space-y-1">
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-gray-500 dark:text-gray-400"><?php echo $percentage; ?>% <?php echo __('complete_pct'); ?></span>
                                    <span class="text-gray-950 dark:text-white" id="progress-val-<?php echo $quest['user_quest_id']; ?>">
                                        <?php echo number_format($quest['progress']); ?> / <?php echo number_format($quest['target_value']); ?>
                                    </span>
                                </div>
                                <div class="w-full h-2 bg-base rounded-full overflow-hidden relative">
                                    <div class="h-full bg-gradient-to-r from-primary to-primary-light transition-all duration-500 rounded-full" id="progress-bar-<?php echo $quest['user_quest_id']; ?>" style="width: <?php echo $percentage; ?>%;"></div>
                                </div>
                            </div>
                            
                            <!-- Actions / inputs -->
                            <div class="mt-4 flex items-center gap-2 justify-end" id="quest-action-<?php echo $quest['user_quest_id']; ?>">
                                <?php if ($isCompleted): ?>
                                    <span class="text-xs font-bold text-primary flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check"></i> <?php echo __('completed_badge'); ?>
                                    </span>
                                <?php else: ?>
                                    <input type="number" 
                                           id="quest-input-<?php echo $quest['user_quest_id']; ?>" 
                                           class="bg-base border border-darkBorder text-gray-900 dark:text-white text-xs rounded-lg py-1.5 px-3 w-20 text-center focus:outline-none focus:border-primary/50" 
                                           placeholder="<?php echo __('value_placeholder'); ?>" 
                                           min="0" 
                                           max="<?php echo $quest['target_value']; ?>"
                                           value="<?php echo $quest['progress']; ?>">
                                    <button class="bg-primary hover:bg-primary-light text-white text-xs font-bold py-1.5 px-4 rounded-lg transition-all duration-200" onclick="updateQuestProgress(<?php echo $quest['user_quest_id']; ?>)">
                                        <?php echo __('update_btn'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>

<?php require_once 'includes/footer.php'; ?>
