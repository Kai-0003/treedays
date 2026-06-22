<?php
require_once 'includes/db.php';

// Guarantee authentication
require_login();

$userId = get_logged_in_user_id();

$pageTitle = "EcoFit - Step & Breakfast Tracker";
$activePage = 'stepbreakfast';
require_once 'includes/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-surfaceSolid/50 border border-darkBorder backdrop-blur-xl rounded-2xl p-6 shadow-xl">
        <h2 class="text-xl font-black text-white mb-2 flex items-center gap-2 border-b border-darkBorder pb-4">
            <i class="fa-solid fa-utensils text-primary"></i> Step & Breakfast Tracker
        </h2>
        <p class="text-sm text-gray-400 mb-6">
            Track your healthy nutritional starts and daily walking progress.
        </p>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left: Step Tracker -->
            <div class="bg-base border border-darkBorder rounded-2xl p-5 flex flex-col items-center">
                <h3 class="font-bold text-white text-base mb-4 self-start flex items-center gap-2">
                    <i class="fa-solid fa-shoe-prints text-primary-light"></i> Walking Progress
                </h3>
                
                <!-- Circular Progress Ring (SVG) -->
                <div class="relative w-40 h-40 flex items-center justify-center my-4">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="80" cy="80" r="68" stroke="rgba(255,255,255,0.05)" stroke-width="8" fill="transparent" />
                        <circle cx="80" cy="80" r="68" stroke="#10b981" stroke-width="8" fill="transparent" 
                                stroke-dasharray="427" stroke-dashoffset="150" class="transition-all duration-1000" />
                    </svg>
                    <div class="absolute flex flex-col items-center justify-center text-center">
                        <span class="text-2xl font-black text-white">6,420</span>
                        <span class="text-xs text-gray-400">/ 10,000 steps</span>
                    </div>
                </div>

                <!-- Input to add steps -->
                <div class="w-full mt-4 flex items-center gap-2">
                    <input type="number" id="add-steps-input" class="flex-1 bg-surfaceSolid border border-darkBorder text-white text-sm rounded-lg py-2 px-3 focus:outline-none focus:border-primary/50" placeholder="Add Steps">
                    <button class="bg-primary hover:bg-primary-light text-white text-sm font-bold py-2 px-4 rounded-lg transition-all duration-200" onclick="alert('Steps updated!')">
                        Add
                    </button>
                </div>
            </div>

            <!-- Right: Breakfast Log -->
            <div class="bg-base border border-darkBorder rounded-2xl p-5">
                <h3 class="font-bold text-white text-base mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-apple-whole text-primary-light"></i> Breakfast Logger
                </h3>

                <div class="space-y-3 mb-6">
                    <div class="flex justify-between items-center bg-surfaceSolid/40 border border-darkBorder/40 p-3 rounded-xl">
                        <div>
                            <p class="text-sm font-bold text-white">Oatmeal with Blueberries & Honey</p>
                            <p class="text-xs text-gray-400">8:30 AM</p>
                        </div>
                        <span class="text-xs bg-primary/10 text-primary-light border border-primary/20 px-2 py-1 rounded-lg">350 kcal</span>
                    </div>
                    <div class="flex justify-between items-center bg-surfaceSolid/40 border border-darkBorder/40 p-3 rounded-xl">
                        <div>
                            <p class="text-sm font-bold text-white">2 Boiled Eggs & Green Tea</p>
                            <p class="text-xs text-gray-400">8:45 AM</p>
                        </div>
                        <span class="text-xs bg-primary/10 text-primary-light border border-primary/20 px-2 py-1 rounded-lg">180 kcal</span>
                    </div>
                </div>

                <!-- Log Meal Form -->
                <div class="space-y-3">
                    <input type="text" class="w-full bg-surfaceSolid border border-darkBorder text-white text-sm rounded-lg py-2 px-3 focus:outline-none focus:border-primary/50" placeholder="Meal Name (e.g. Avocado Toast)">
                    <div class="flex gap-2">
                        <input type="number" class="flex-1 bg-surfaceSolid border border-darkBorder text-white text-sm rounded-lg py-2 px-3 focus:outline-none focus:border-primary/50" placeholder="Calories (kcal)">
                        <button class="bg-primary hover:bg-primary-light text-white text-sm font-bold py-2 px-6 rounded-lg transition-all duration-200" onclick="alert('Meal logged!')">
                            Log
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
