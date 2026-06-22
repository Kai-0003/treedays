<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

// Check authentication
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$userId = get_logged_in_user_id();

// Read JSON input or POST fields
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$treeId = isset($input['tree_id']) ? intval($input['tree_id']) : 0;
$x = isset($input['x']) ? intval($input['x']) : -1;
$y = isset($input['y']) ? intval($input['y']) : -1;

// Define grid bounds (e.g., 6x6 grid)
$gridMin = 0;
$gridMax = 5;

if ($treeId <= 0 || $x < $gridMin || $x > $gridMax || $y < $gridMin || $y > $gridMax) {
    echo json_encode(['success' => false, 'message' => 'Invalid tree selection or grid coordinates.']);
    exit;
}

try {
    // 1. Fetch tree details to check validity and cost
    $stmtTree = $pdo->prepare("SELECT id, tree_name, cost_points FROM tree_shop WHERE id = ?");
    $stmtTree->execute([$treeId]);
    $tree = $stmtTree->fetch();

    if (!$tree) {
        echo json_encode(['success' => false, 'message' => 'Tree not found in the shop.']);
        exit;
    }

    // 2. Fetch user's current points
    $stmtUser = $pdo->prepare("SELECT total_points FROM users WHERE id = ?");
    $stmtUser->execute([$userId]);
    $userPoints = $stmtUser->fetchColumn();

    if ($userPoints < $tree['cost_points']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Insufficient points. You need ' . ($tree['cost_points'] - $userPoints) . ' more points.'
        ]);
        exit;
    }

    // 3. Check if coordinate is already occupied for this user
    $stmtCheckGrid = $pdo->prepare("
        SELECT COUNT(*) 
        FROM user_garden 
        WHERE user_id = ? AND x_coordinate = ? AND y_coordinate = ?
    ");
    $stmtCheckGrid->execute([$userId, $x, $y]);
    $isOccupied = $stmtCheckGrid->fetchColumn() > 0;

    if ($isOccupied) {
        echo json_encode(['success' => false, 'message' => 'That spot in your garden is already occupied.']);
        exit;
    }

    // Start Transaction
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
    }

    // Deduct points
    $stmtDeduct = $pdo->prepare("UPDATE users SET total_points = total_points - ? WHERE id = ?");
    $stmtDeduct->execute([$tree['cost_points'], $userId]);

    // Insert tree into user_garden
    $stmtPlant = $pdo->prepare("
        INSERT INTO user_garden (user_id, tree_id, x_coordinate, y_coordinate) 
        VALUES (?, ?, ?, ?)
    ");
    $stmtPlant->execute([$userId, $tree['id'], $x, $y]);

    // Fetch updated points
    $stmtNewPoints = $pdo->prepare("SELECT total_points FROM users WHERE id = ?");
    $stmtNewPoints->execute([$userId]);
    $newTotalPoints = $stmtNewPoints->fetchColumn();

    // Commit Transaction
    if ($pdo->inTransaction()) {
        $pdo->commit();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Successfully purchased and planted ' . $tree['tree_name'] . '!',
        'new_total_points' => $newTotalPoints
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while purchasing: ' . $e->getMessage()
    ]);
}
