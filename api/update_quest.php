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

$userQuestId = isset($input['user_quest_id']) ? intval($input['user_quest_id']) : 0;
$newProgress = isset($input['progress']) ? intval($input['progress']) : 0;

if ($userQuestId <= 0 || $newProgress < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters provided.']);
    exit;
}

try {
    // 1. Fetch the user quest detail and joining with daily_quests to verify Ownership and Target Value
    $stmt = $pdo->prepare("
        SELECT uq.id, uq.user_id, uq.progress, uq.is_completed, 
               dq.target_value, dq.points_reward, dq.title
        FROM user_quests uq
        JOIN daily_quests dq ON uq.quest_id = dq.id
        WHERE uq.id = ? AND uq.user_id = ?
    ");
    $stmt->execute([$userQuestId, $userId]);
    $quest = $stmt->fetch();

    if (!$quest) {
        echo json_encode(['success' => false, 'message' => 'Quest not found or access denied.']);
        exit;
    }

    if ($quest['is_completed']) {
        echo json_encode(['success' => false, 'message' => 'Quest is already completed.']);
        exit;
    }

    // Limit progress to target value
    $cappedProgress = min($newProgress, $quest['target_value']);
    $newlyCompleted = ($cappedProgress >= $quest['target_value']);
    $pointsAwarded = 0;
    $newTotalPoints = 0;

    // Start Transaction for atomic updates
    $pdo->beginTransaction();

    if ($newlyCompleted) {
        $pointsAwarded = $quest['points_reward'];
        
        // Mark quest completed
        $stmtUpdateQuest = $pdo->prepare("UPDATE user_quests SET progress = ?, is_completed = 1 WHERE id = ?");
        $stmtUpdateQuest->execute([$cappedProgress, $userQuestId]);

        // Award points to the user
        $stmtUpdateUser = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?");
        $stmtUpdateUser->execute([$pointsAwarded, $userId]);
        
        // Fetch new total points
        $stmtPoints = $pdo->prepare("SELECT total_points FROM users WHERE id = ?");
        $stmtPoints->execute([$userId]);
        $newTotalPoints = $stmtPoints->fetchColumn();
    } else {
        // Just update progress
        $stmtUpdateQuest = $pdo->prepare("UPDATE user_quests SET progress = ? WHERE id = ?");
        $stmtUpdateQuest->execute([$cappedProgress, $userQuestId]);
    }

    // Commit Transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'completed' => $newlyCompleted,
        'progress' => $cappedProgress,
        'target_value' => $quest['target_value'],
        'points_awarded' => $pointsAwarded,
        'new_total_points' => $newTotalPoints
    ]);

} catch (Exception $e) {
    // Rollback transaction on failure
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating quest: ' . $e->getMessage()
    ]);
}
