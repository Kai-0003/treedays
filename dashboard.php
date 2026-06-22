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

<div class="dashboard-grid">
    <!-- Left Column: Quests & Exercises -->
    <div class="dashboard-left">
        <div class="glass-panel">
            <h2 class="panel-title"><i class="fa-solid fa-person-running"></i> Daily Fitness Quests</h2>
            
            <div class="quests-list">
                <?php if (empty($quests)): ?>
                    <p style="text-align: center; color: var(--text-muted); margin: 2rem 0;">No quests loaded. Database may need to be seeded.</p>
                <?php else: ?>
                    <?php foreach ($quests as $quest): 
                        $percentage = min(100, round(($quest['progress'] / $quest['target_value']) * 100));
                        $isCompleted = (bool)$quest['is_completed'];
                    ?>
                        <div class="quest-card <?php echo $isCompleted ? 'completed' : ''; ?>" id="quest-card-<?php echo $quest['user_quest_id']; ?>">
                            <div class="quest-header">
                                <div class="quest-info">
                                    <h4><?php echo htmlspecialchars($quest['title']); ?></h4>
                                    <p class="quest-desc"><?php echo htmlspecialchars($quest['description']); ?></p>
                                </div>
                                <div class="quest-reward">
                                    <i class="fa-solid fa-coins gold-coin"></i> +<?php echo $quest['points_reward']; ?> pts
                                </div>
                            </div>
                            
                            <div class="quest-progress-container">
                                <div class="progress-meta">
                                    <span class="progress-pct"><?php echo $percentage; ?>% Complete</span>
                                    <span class="progress-fraction" id="progress-val-<?php echo $quest['user_quest_id']; ?>">
                                        <?php echo number_format($quest['progress']); ?> / <?php echo number_format($quest['target_value']); ?>
                                    </span>
                                </div>
                                <div class="progress-bar-outer">
                                    <div class="progress-bar-inner" id="progress-bar-<?php echo $quest['user_quest_id']; ?>" style="width: <?php echo $percentage; ?>%;"></div>
                                </div>
                            </div>
                            
                            <div class="quest-action" id="quest-action-<?php echo $quest['user_quest_id']; ?>">
                                <?php if ($isCompleted): ?>
                                    <span class="status-badge"><i class="fa-solid fa-circle-check"></i> Completed</span>
                                <?php else: ?>
                                    <input type="number" 
                                           id="quest-input-<?php echo $quest['user_quest_id']; ?>" 
                                           class="quest-input" 
                                           placeholder="Value" 
                                           min="0" 
                                           max="<?php echo $quest['target_value']; ?>"
                                           value="<?php echo $quest['progress']; ?>">
                                    <button class="btn btn-sm" onclick="updateQuestProgress(<?php echo $quest['user_quest_id']; ?>)">
                                        Update
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column: Interactive Garden -->
    <div class="dashboard-right">
        <div class="glass-panel">
            <h2 class="panel-title"><i class="fa-solid fa-seedling"></i> My Virtual Garden</h2>
            
            <div class="garden-container">
                <div class="garden-grid-wrapper">
                    <div class="garden-grid" id="garden-grid">
                        <?php for ($y = 0; $y < 6; $y++): ?>
                            <?php for ($x = 0; $x < 6; $x++): 
                                $tree = $gardenGrid[$y][$x];
                                $cellClass = $tree ? 'occupied' : 'empty';
                            ?>
                                <div class="garden-cell <?php echo $cellClass; ?>" 
                                     data-x="<?php echo $x; ?>" 
                                     data-y="<?php echo $y; ?>"
                                     <?php if (!$tree): ?> onclick="selectGardenCell(this)" <?php endif; ?>>
                                    
                                    <?php if ($tree): ?>
                                        <div class="tree-wrapper" title="<?php echo htmlspecialchars($tree['tree_name']); ?> at (<?php echo $x; ?>, <?php echo $y; ?>)">
                                            <?php echo get_tree_svg($tree['image_url']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <span class="cell-coordinate"><?php echo $x; ?>,<?php echo $y; ?></span>
                                </div>
                            <?php endfor; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="garden-actions">
                    <div id="selection-prompt" class="selection-prompt">
                        <i class="fa-solid fa-arrow-pointer"></i> Click any empty grass slot to buy and plant a tree.
                    </div>
                    
                    <div id="plant-panel" class="plant-control-panel" style="display: none;">
                        <span class="selection-prompt">Planting at (<strong id="selected-coords">0,0</strong>):</span>
                        <select id="quick-tree-select" class="quest-input" style="width: 150px; text-align: left;" onchange="updateSelectedTreeCost()">
                            <?php foreach ($shopTrees as $shopTree): ?>
                                <option value="<?php echo $shopTree['id']; ?>" data-cost="<?php echo $shopTree['cost_points']; ?>">
                                    <?php echo htmlspecialchars($shopTree['tree_name']); ?> (<?php echo $shopTree['cost_points']; ?> pts)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm" onclick="purchaseAndPlantTree()">
                            <i class="fa-solid fa-leaf"></i> Plant Tree
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
