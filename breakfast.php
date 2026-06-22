<?php
require_once 'includes/db.php';

// Guarantee authentication
require_login();

$userId = get_logged_in_user_id();
$error = '';
$success = '';

// Handle meal logging form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_breakfast') {
    $mealName = trim($_POST['meal_name'] ?? '');
    $calories = intval($_POST['calories'] ?? 0);
    $imagePath = null;
    
    if (empty($mealName) || $calories <= 0) {
        $error = 'Please fill in the food name and enter a valid positive calorie count.';
    } else {
        // Handle Photo Upload
        if (isset($_FILES['breakfast_photo']) && $_FILES['breakfast_photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['breakfast_photo']['tmp_name'];
            $fileName = $_FILES['breakfast_photo']['name'];
            $fileSize = $_FILES['breakfast_photo']['size'];
            $fileType = $_FILES['breakfast_photo']['type'];
            
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            
            // Allow only standard images
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                // Check file size (limit to 5MB)
                if ($fileSize < 5 * 1024 * 1024) {
                    $newFileName = uniqid('bf_') . '_' . time() . '.' . $fileExtension;
                    $uploadFileDir = __DIR__ . '/uploads/breakfast/';
                    $dest_path = $uploadFileDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $imagePath = 'uploads/breakfast/' . $newFileName;
                    } else {
                        $error = 'An error occurred while moving the uploaded photo.';
                    }
                } else {
                    $error = 'The photo is too large. Maximum size is 5MB.';
                }
            } else {
                $error = 'Invalid photo format. Only JPG, JPEG, PNG, and WEBP formats are allowed.';
            }
        }
        
        // Write record to database if no errors
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO breakfast_logs (user_id, meal_name, calories, image_path) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $mealName, $calories, $imagePath]);
                $success = 'Successfully logged your breakfast!';
            } catch (PDOException $e) {
                $error = 'Failed to save log: ' . $e->getMessage();
            }
        }
    }
}

// Fetch user's breakfast logs history
try {
    $stmtLogs = $pdo->prepare("
        SELECT id, meal_name, calories, image_path, created_at 
        FROM breakfast_logs 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmtLogs->execute([$userId]);
    $historyLogs = $stmtLogs->fetchAll();
} catch (PDOException $e) {
    $historyLogs = [];
    $error = 'Failed to load history: ' . $e->getMessage();
}

$pageTitle = "EcoFit - Breakfast Logger";
$activePage = 'breakfast';
require_once 'includes/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-surfaceSolid/50 border border-darkBorder backdrop-blur-xl rounded-2xl p-6 shadow-xl">
        <h2 class="text-xl font-black text-white mb-2 flex items-center gap-2 border-b border-darkBorder pb-4">
            <i class="fa-solid fa-apple-whole text-primary"></i> Breakfast Logger
        </h2>
        <p class="text-sm text-gray-400 mb-6">
            Take a photo of your breakfast, record your calorie count, and track your healthy nutrition history.
        </p>

        <!-- Main Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Side: Log Meal Form (Input camera / photo file) -->
            <div class="col-span-1 lg:col-span-5 bg-base border border-darkBorder rounded-2xl p-6 shadow-md h-fit">
                <h3 class="font-bold text-white text-base mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-plus text-primary-light"></i> Log Breakfast
                </h3>

                <?php if (!empty($error)): ?>
                    <div class="flex items-center gap-2 bg-red-500/10 border border-red-500/30 text-red-400 p-3 rounded-lg text-xs mb-4">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-3 rounded-lg text-xs mb-4">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form supporting File Uploads -->
                <form action="breakfast.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" value="log_breakfast">

                    <!-- Custom Camera / Photo Uploader Dropzone -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Breakfast Photo</label>
                        <div onclick="document.getElementById('breakfast-photo').click()" 
                             class="border-dashed border-2 border-darkBorder hover:border-primary/45 rounded-2xl cursor-pointer flex flex-col items-center justify-center p-5 bg-base/50 hover:bg-white/[0.01] transition-all text-center relative overflow-hidden min-h-[160px]"
                             id="dropzone">
                            
                            <!-- Preview Image element -->
                            <img id="image-preview" class="hidden absolute inset-0 w-full h-full object-cover z-10" />

                            <div class="flex flex-col items-center gap-2" id="upload-prompt">
                                <i class="fa-solid fa-camera text-3xl text-gray-500"></i>
                                <span class="text-xs text-gray-400 font-medium">Click to Snap Photo or Upload</span>
                                <span class="text-[10px] text-gray-600">Supports JPG, PNG, WEBP (Max 5MB)</span>
                            </div>
                        </div>
                        <input type="file" 
                               name="breakfast_photo" 
                               id="breakfast-photo" 
                               accept="image/*" 
                               capture="camera" 
                               class="hidden" 
                               onchange="previewPhoto(this)">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Food Name</label>
                        <input type="text" 
                               name="meal_name" 
                               class="w-full bg-surfaceSolid border border-darkBorder focus:border-primary/50 focus:ring-1 focus:ring-primary/20 rounded-lg py-2 px-3 text-sm text-white focus:outline-none transition-all" 
                               placeholder="e.g. Avocado Toast & Eggs" 
                               required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Calories (kcal)</label>
                        <input type="number" 
                               name="calories" 
                               class="w-full bg-surfaceSolid border border-darkBorder focus:border-primary/50 focus:ring-1 focus:ring-primary/20 rounded-lg py-2 px-3 text-sm text-white focus:outline-none transition-all" 
                               placeholder="e.g. 350" 
                               min="1" 
                               required>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-bold text-white bg-primary hover:bg-primary-light transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:scale-[1.01]">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Record Meal
                    </button>
                </form>
            </div>

            <!-- Right Side: Meal Logs History with Thumbnail and Created At -->
            <div class="col-span-1 lg:col-span-7 bg-base border border-darkBorder rounded-2xl p-6 shadow-md">
                <h3 class="font-bold text-white text-base mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-primary-light"></i> Nutrition History
                </h3>

                <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                    <?php if (empty($historyLogs)): ?>
                        <div class="text-center py-12 text-gray-500 text-sm">
                            <i class="fa-solid fa-utensils text-2xl mb-2 text-gray-700 block"></i>
                            No meals logged yet. Record your breakfast to start!
                        </div>
                    <?php else: ?>
                        <?php foreach ($historyLogs as $log): ?>
                            <div class="flex items-center gap-4 bg-surfaceSolid/40 border border-darkBorder/40 p-3 rounded-xl hover:bg-white/[0.01] transition-all">
                                
                                <!-- Photo Thumbnail -->
                                <div class="w-14 h-14 rounded-lg bg-emerald-950/20 border border-darkBorder/50 overflow-hidden shrink-0 flex items-center justify-center">
                                    <?php if (!empty($log['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($log['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($log['meal_name']); ?>" 
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <i class="fa-solid fa-bowl-food text-gray-600 text-lg"></i>
                                    <?php endif; ?>
                                </div>

                                <!-- Log Info -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-white text-sm truncate leading-tight mb-1">
                                        <?php echo htmlspecialchars($log['meal_name']); ?>
                                    </h4>
                                    <!-- Created At Timestamp -->
                                    <p class="text-[10px] text-gray-500 flex items-center gap-1">
                                        <i class="fa-regular fa-clock"></i> 
                                        <?php echo date('M d, Y - h:i A', strtotime($log['created_at'])); ?>
                                    </p>
                                </div>

                                <!-- Calories Badge -->
                                <span class="shrink-0 text-xs bg-primary/10 text-primary-light border border-primary/20 px-2.5 py-1 rounded-lg font-bold">
                                    <?php echo number_format($log['calories']); ?> kcal
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
/**
 * Render dynamic uploaded file preview inside dropzone
 */
function previewPhoto(input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('image-preview');
            const prompt = document.getElementById('upload-prompt');
            
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            prompt.classList.add('hidden'); // Hide default text
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
