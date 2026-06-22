<?php
require_once 'includes/db.php';

// Guarantee authentication
require_login();

$userId = get_logged_in_user_id();

$pageTitle = "EcoFit - Sleep Analytics";
$activePage = 'sleep';
require_once 'includes/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-surfaceSolid/50 border border-darkBorder backdrop-blur-xl rounded-2xl p-6 shadow-xl">
        <h2 class="text-xl font-black text-white mb-2 flex items-center gap-2 border-b border-darkBorder pb-4">
            <i class="fa-solid fa-moon text-primary"></i> Sleep Analytics
        </h2>
        <p class="text-sm text-gray-400 mb-6">
            Understand your sleeping cycles and improve recovery score.
        </p>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left: Sleep Ring -->
            <div class="bg-base border border-darkBorder rounded-2xl p-5 flex flex-col items-center">
                <h3 class="font-bold text-white text-base mb-4 self-start flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-primary-light"></i> Sleep Quality
                </h3>
                
                <!-- Sleep Duration Circle -->
                <div class="relative w-40 h-40 flex items-center justify-center my-4">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="80" cy="80" r="68" stroke="rgba(255,255,255,0.05)" stroke-width="8" fill="transparent" />
                        <circle cx="80" cy="80" r="68" stroke="#10b981" stroke-width="8" fill="transparent" 
                                stroke-dasharray="427" stroke-dashoffset="100" class="transition-all duration-1000" />
                    </svg>
                    <div class="absolute flex flex-col items-center justify-center text-center">
                        <span class="text-2xl font-black text-white">7h 45m</span>
                        <span class="text-xs text-gray-400">Sleep Duration</span>
                    </div>
                </div>

                <div class="w-full grid grid-cols-3 gap-2 mt-4 text-center">
                    <div class="bg-surfaceSolid/40 p-2 rounded-lg border border-darkBorder/30">
                        <p class="text-xs text-gray-400">Deep</p>
                        <p class="text-sm font-black text-white">2h 15m</p>
                    </div>
                    <div class="bg-surfaceSolid/40 p-2 rounded-lg border border-darkBorder/30">
                        <p class="text-xs text-gray-400">REM</p>
                        <p class="text-sm font-black text-white">1h 50m</p>
                    </div>
                    <div class="bg-surfaceSolid/40 p-2 rounded-lg border border-darkBorder/30">
                        <p class="text-xs text-gray-400">Light</p>
                        <p class="text-sm font-black text-white">3h 40m</p>
                    </div>
                </div>
            </div>

            <!-- Right: Log Sleep Form -->
            <div class="bg-base border border-darkBorder rounded-2xl p-5">
                <h3 class="font-bold text-white text-base mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-bed text-primary-light"></i> Log Sleep Cycle
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Bed Time</label>
                        <input type="time" class="w-full bg-surfaceSolid border border-darkBorder text-white text-sm rounded-lg py-2 px-3 focus:outline-none focus:border-primary/50" value="23:00">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Wake Up Time</label>
                        <input type="time" class="w-full bg-surfaceSolid border border-darkBorder text-white text-sm rounded-lg py-2 px-3 focus:outline-none focus:border-primary/50" value="07:00">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Sleep Quality Rating</label>
                        <select class="w-full bg-surfaceSolid border border-darkBorder text-white text-sm rounded-lg py-2 px-3 focus:outline-none focus:border-primary/50">
                            <option>Restful & Deep (5/5)</option>
                            <option>Good (4/5)</option>
                            <option>Average (3/5)</option>
                            <option>Disrupted (2/5)</option>
                            <option>Poor / Insomnia (1/5)</option>
                        </select>
                    </div>
                    <button class="w-full bg-primary hover:bg-primary-light text-white text-sm font-bold py-2.5 rounded-lg transition-all duration-200" onclick="alert('Sleep logged!')">
                        Save Log
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
