<?php
require_once 'includes/db.php';

// Guarantee authentication
require_login();

$userId = get_logged_in_user_id();

$pageTitle = "EcoFit - Nature Calendar";
$activePage = 'calendar';
require_once 'includes/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-surfaceSolid/50 border border-darkBorder backdrop-blur-xl rounded-2xl p-6 shadow-xl">
        <h2 class="text-xl font-black text-white mb-2 flex items-center gap-2 border-b border-darkBorder pb-4">
            <i class="fa-solid fa-calendar-days text-primary"></i> Nature Calendar
        </h2>
        <p class="text-sm text-gray-400 mb-6">
            Review your consistency calendar and celebrate days you planted trees.
        </p>

        <!-- Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Side: Stats and Streak indicators -->
            <div class="col-span-1 space-y-4">
                <div class="bg-base border border-darkBorder rounded-2xl p-5">
                    <h3 class="font-bold text-white text-sm mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-fire text-orange-500"></i> Active Streak
                    </h3>
                    <p class="text-3xl font-black text-white">5 <span class="text-sm text-gray-400">days in a row</span></p>
                    <p class="text-xs text-gray-500 mt-1">Keep it up! Plant one more tree to extend your streak.</p>
                </div>
                
                <div class="bg-base border border-darkBorder rounded-2xl p-5">
                    <h3 class="font-bold text-white text-sm mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-tree text-primary-light"></i> Planting Summary
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs text-gray-400">
                            <span>This Month</span>
                            <span class="text-white font-bold">12 Trees</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-400">
                            <span>All Time</span>
                            <span class="text-white font-bold">48 Trees</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Calendar Grid -->
            <div class="col-span-1 lg:col-span-2 bg-base border border-darkBorder rounded-2xl p-5">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-sm font-bold text-white">June 2026</span>
                    <div class="flex gap-1">
                        <button class="bg-surfaceSolid hover:bg-white/5 border border-darkBorder text-white text-xs px-2.5 py-1 rounded" onclick="alert('Previous month')">&lt;</button>
                        <button class="bg-surfaceSolid hover:bg-white/5 border border-darkBorder text-white text-xs px-2.5 py-1 rounded" onclick="alert('Next month')">&gt;</button>
                    </div>
                </div>

                <!-- Calendar Headings -->
                <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold text-gray-500 mb-2">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                </div>

                <!-- Calendar Days (Mock June 2026 starting on Monday) -->
                <div class="grid grid-cols-7 gap-1 text-center">
                    <!-- Blank slots for days of prev month -->
                    <div class="aspect-square flex items-center justify-center text-xs text-gray-700">31</div>
                    
                    <!-- Days of Month -->
                    <?php 
                    // Mock data: days we did exercise and planted trees
                    $activeDays = [1, 2, 3, 5, 8, 9, 10, 12, 15, 16, 17, 19, 20, 22];
                    $treeDays = [5, 10, 16, 22]; // Planted trees on these dates
                    
                    for ($d = 1; $d <= 30; $d++) {
                        $isActive = in_array($d, $activeDays);
                        $isTreePlanted = in_array($d, $treeDays);
                        
                        $class = "aspect-square flex flex-col items-center justify-center text-xs rounded-lg relative ";
                        if ($d === 22) {
                            // Current Day
                            $class .= "border-2 border-primary bg-primary/10 text-white font-bold";
                        } elseif ($isTreePlanted) {
                            $class .= "bg-emerald-950/40 border border-primary/30 text-primary-light font-bold";
                        } elseif ($isActive) {
                            $class .= "bg-white/5 text-white";
                        } else {
                            $class .= "text-gray-500";
                        }
                        
                        echo "<div class='{$class}'>";
                        echo "<span>{$d}</span>";
                        if ($isTreePlanted) {
                            echo "<i class='fa-solid fa-tree text-[8px] text-primary absolute bottom-1'></i>";
                        }
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
