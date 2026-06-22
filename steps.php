<?php
require_once 'includes/db.php';

// Guarantee authentication
require_login();

$userId = get_logged_in_user_id();
$today = date('Y-m-d');

// Fetch today's "Morning Walk" quest for this user
$stmtQuest = $pdo->prepare("
    SELECT uq.id as user_quest_id, uq.progress, uq.is_completed, dq.target_value, dq.points_reward
    FROM user_quests uq
    JOIN daily_quests dq ON uq.quest_id = dq.id
    WHERE uq.user_id = ? AND uq.quest_date = ? AND dq.title = 'Morning Walk'
    LIMIT 1
");
$stmtQuest->execute([$userId, $today]);
$morningWalkQuest = $stmtQuest->fetch();

$pageTitle = "EcoFit - Steps Tracker";
$activePage = 'steps';
require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-surfaceSolid/50 border border-darkBorder backdrop-blur-xl rounded-2xl p-6 shadow-xl">
        <h2 class="text-xl font-black text-gray-900 dark:text-white mb-2 flex items-center gap-2 border-b border-darkBorder pb-4">
            <i class="fa-solid fa-shoe-prints text-primary"></i> <?php echo __('steps'); ?>
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            <?php echo __('steps_desc'); ?>
        </p>

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Left Side: Interactive Circular Progress & Live Stats -->
            <div class="bg-base border border-darkBorder rounded-2xl p-6 flex flex-col items-center justify-between">
                <h3 class="font-bold text-gray-900 dark:text-white text-base mb-4 self-start flex items-center gap-2">
                    <i class="fa-solid fa-gauge-high text-primary-light"></i> <?php echo __('step_metrics'); ?>
                </h3>
                
                <!-- Circular Progress -->
                <?php 
                $currentSteps = $morningWalkQuest ? intval($morningWalkQuest['progress']) : 0;
                $targetSteps = $morningWalkQuest ? intval($morningWalkQuest['target_value']) : 5000;
                $percentage = min(100, round(($currentSteps / $targetSteps) * 100));
                
                // SVG dashoffset calculation: circumference = 2 * pi * r = 2 * 3.14159 * 68 = 427.25
                $circumference = 427;
                $dashoffset = $circumference - ($percentage / 100) * $circumference;
                ?>
                <div class="relative w-44 h-44 flex items-center justify-center my-2">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="88" cy="88" r="68" stroke="rgba(255,255,255,0.04)" stroke-width="8" fill="transparent" />
                        <circle cx="88" cy="88" r="68" stroke="#10b981" stroke-width="8" fill="transparent" 
                                id="circle-progress"
                                stroke-dasharray="<?php echo $circumference; ?>" 
                                stroke-dashoffset="<?php echo $dashoffset; ?>" 
                                class="transition-all duration-1000 ease-out" />
                    </svg>
                    <div class="absolute flex flex-col items-center justify-center text-center">
                        <span class="text-3xl font-black text-gray-905 dark:text-white" id="step-count-display"><?php echo number_format($currentSteps); ?></span>
                        <span class="text-[10px] text-gray-500 uppercase tracking-wider"><?php echo __('steps_today'); ?></span>
                    </div>
                </div>

                <!-- Live Computed Stats Badges -->
                <div class="w-full grid grid-cols-2 gap-3 mt-4">
                    <div class="bg-surfaceSolid/40 border border-darkBorder/40 p-3 rounded-xl text-center">
                        <p class="text-[10px] text-gray-500 uppercase font-semibold"><?php echo __('distance'); ?></p>
                        <p class="text-base font-black text-gray-950 dark:text-white" id="distance-display">
                            <?php echo number_format($currentSteps * 0.0008, 2); ?> <span class="text-xs font-normal text-gray-500 dark:text-gray-400">km</span>
                        </p>
                    </div>
                    <div class="bg-surfaceSolid/40 border border-darkBorder/40 p-3 rounded-xl text-center">
                        <p class="text-[10px] text-gray-500 uppercase font-semibold"><?php echo __('calories'); ?></p>
                        <p class="text-base font-black text-gray-950 dark:text-white" id="calories-display">
                            <?php echo number_format($currentSteps * 0.04, 1); ?> <span class="text-xs font-normal text-gray-500 dark:text-gray-400">kcal</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Side: Log Steps Form -->
            <div class="bg-base border border-darkBorder rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-base mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-plus text-primary-light"></i> <?php echo __('log_steps_title'); ?>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                        <?php echo __('log_steps_desc'); ?>
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"><?php echo __('steps'); ?></label>
                            <input type="number" 
                                   id="step-input" 
                                   class="w-full bg-surfaceSolid border border-darkBorder focus:border-primary/50 focus:ring-1 focus:ring-primary/20 rounded-lg py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:outline-none transition-all duration-200" 
                                   placeholder="e.g. 5000" 
                                   min="0"
                                   value="<?php echo $currentSteps; ?>"
                                   oninput="calculateMetrics(this.value)">
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <?php if ($morningWalkQuest): ?>
                        <input type="hidden" id="user-quest-id" value="<?php echo $morningWalkQuest['user_quest_id']; ?>">
                        <?php if ($morningWalkQuest['is_completed']): ?>
                            <div class="w-full py-3 rounded-lg text-center bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-bold flex items-center justify-center gap-2">
                                <i class="fa-solid fa-circle-check animate-bounce"></i> <?php echo __('Morning Walk') . ' ' . __('completed_badge') . '!'; ?>
                            </div>
                        <?php else: ?>
                            <button onclick="submitSteps()" class="w-full py-2.5 rounded-lg text-sm font-bold text-white bg-primary hover:bg-primary-light transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:scale-[1.01]">
                                <i class="fa-solid fa-cloud-arrow-up"></i> <?php echo __('save_log_btn'); ?>
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="w-full py-3 rounded-lg text-center bg-red-500/10 border border-red-500/30 text-red-400 text-sm font-bold">
                            Morning Walk quest template is missing for today.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
// Circumference of SVG circle
const circleCircumference = 427;
const targetValue = <?php echo $targetSteps; ?>;

/**
 * Perform real-time metrics calculations
 */
function calculateMetrics(value) {
    const steps = parseInt(value) || 0;
    
    // Distance = Steps * 0.0008 km
    const distance = (steps * 0.0008).toFixed(2);
    document.getElementById('distance-display').innerHTML = `${Number(distance).toLocaleString()} <span class="text-xs font-normal text-gray-400">km</span>`;
    
    // Calories = Steps * 0.04 kcal
    const calories = (steps * 0.04).toFixed(1);
    document.getElementById('calories-display').innerHTML = `${Number(calories).toLocaleString()} <span class="text-xs font-normal text-gray-400">kcal</span>`;
    
    // Update central label
    document.getElementById('step-count-display').textContent = steps.toLocaleString();
    
    // Update SVG progress ring
    const percentage = Math.min(100, (steps / targetValue) * 100);
    const offset = circleCircumference - (percentage / 100) * circleCircumference;
    const circle = document.getElementById('circle-progress');
    if (circle) {
        circle.setAttribute('stroke-dashoffset', offset);
    }
}

/**
 * Submit step value via update_quest API
 */
function submitSteps() {
    const stepsInput = document.getElementById('step-input');
    const steps = parseInt(stepsInput.value) || 0;
    const userQuestId = parseInt(document.getElementById('user-quest-id').value);
    
    if (steps < 0) {
        showToast('error', 'Steps count must be a positive number.');
        return;
    }
    
    // Call existing API to update progress
    fetch('api/update_quest.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_quest_id: userQuestId,
            progress: steps
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update points in header
            updateHeaderPoints(data.new_total_points);
            
            if (data.completed) {
                showToast('success', `Awesome! Walk quest completed. +${data.points_awarded} pts!`);
                // Reload page after 1.5 seconds to show completed status
                setTimeout(() => window.location.reload(), 1500);
                if (typeof createConfettiExplosion === 'function') {
                    createConfettiExplosion();
                }
            } else {
                showToast('success', 'Steps progress updated successfully!');
            }
        } else {
            showToast('error', data.message || 'Failed to update steps.');
        }
    })
    .catch(error => {
        console.error('Error logging steps:', error);
        showToast('error', 'Connection error occurred. Please try again.');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
