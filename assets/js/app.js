/**
 * EcoFit Core Frontend Scripts
 */

// Selected garden coordinate tracking
let selectedX = -1;
let selectedY = -1;

/**
 * Toggle user profile dropdown menu
 */
function toggleProfileDropdown() {
    const dropdown = document.getElementById('profile-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

/**
 * Toggle light/dark theme modes
 */
function toggleThemeMode() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    const newTheme = isDark ? 'light' : 'dark';
    
    if (newTheme === 'dark') {
        html.classList.remove('light');
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
        html.classList.add('light');
    }
    
    // Save to localStorage
    localStorage.setItem('theme', newTheme);
    
    // Save to Cookie (1 year expiry)
    document.cookie = `theme=${newTheme}; max-age=${365 * 24 * 60 * 60}; path=/`;
}

/**
 * Toggle mobile side drawer and backdrop
 */
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    const backdrop = document.getElementById('mobile-menu-backdrop');
    if (mobileMenu && backdrop) {
        const isOpen = mobileMenu.classList.contains('translate-x-0');
        if (isOpen) {
            // Close Drawer
            mobileMenu.classList.remove('translate-x-0');
            mobileMenu.classList.add('-translate-x-full');
            
            // Fade Out Backdrop
            backdrop.classList.remove('opacity-100', 'pointer-events-auto');
            backdrop.classList.add('opacity-0', 'pointer-events-none');
        } else {
            // Open Drawer
            mobileMenu.classList.remove('-translate-x-full');
            mobileMenu.classList.add('translate-x-0');
            
            // Fade In Backdrop
            backdrop.classList.remove('opacity-0', 'pointer-events-none');
            backdrop.classList.add('opacity-100', 'pointer-events-auto');
        }
    }
}

/**
 * Intercept logo click on mobile to toggle menu instead of navigating
 */
function handleLogoClick(event) {
    if (window.innerWidth < 768) {
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenu) {
            event.preventDefault();
            toggleMobileMenu();
        }
    }
}

// Close dropdown when clicking outside
window.addEventListener('click', (e) => {
    const container = document.getElementById('profile-dropdown-container');
    const dropdown = document.getElementById('profile-dropdown');
    if (container && dropdown && !container.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

// Document Ready Bootstrap
document.addEventListener('DOMContentLoaded', () => {
    // Check if redirecting from shop with plant_tree_id
    const urlParams = new URLSearchParams(window.location.search);
    const plantTreeId = urlParams.get('plant_tree_id');
    
    if (plantTreeId) {
        // Pre-select this tree in the dropdown
        const selector = document.getElementById('quick-tree-select');
        if (selector) {
            selector.value = plantTreeId;
            showToast('info', 'Choose an empty slot in your garden to plant your tree!');
            
            // Highlight empty cells to prompt user
            document.querySelectorAll('.garden-cell.empty').forEach(cell => {
                cell.style.animation = 'pulse 1.5s infinite';
            });
        }
    }
});

/**
 * Switch between empty garden cells
 */
function selectGardenCell(cellElement) {
    // Remove pulse animations if any existed from shop redirect
    document.querySelectorAll('.garden-cell.empty').forEach(cell => {
        cell.style.animation = '';
    });

    // Deselect previously selected cells
    document.querySelectorAll('.garden-cell').forEach(c => c.classList.remove('selected'));
    
    // Select clicked cell
    cellElement.classList.add('selected');
    
    selectedX = parseInt(cellElement.getAttribute('data-x'));
    selectedY = parseInt(cellElement.getAttribute('data-y'));
    
    // Show actions panel
    document.getElementById('selection-prompt').style.display = 'none';
    document.getElementById('selected-coords').textContent = `${selectedX}, ${selectedY}`;
    document.getElementById('plant-panel').style.display = 'flex';
}

/**
 * Trigger dynamic toast messages
 */
function showToast(type, message) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    let icon = 'fa-info-circle';
    if (type === 'success') icon = 'fa-check-circle';
    if (type === 'error') icon = 'fa-exclamation-triangle';
    
    toast.innerHTML = `
        <i class="fa-solid ${icon}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    
    // Remove toast after 4 seconds
    setTimeout(() => {
        toast.style.animation = 'toastOut 0.3s ease-in forwards';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

/**
 * Quest update progress (AJAX Fetch API)
 */
function updateQuestProgress(userQuestId) {
    const inputElement = document.getElementById(`quest-input-${userQuestId}`);
    if (!inputElement) return;
    
    const progress = parseInt(inputElement.value);
    
    if (isNaN(progress) || progress < 0) {
        showToast('error', 'Please enter a valid positive progress number.');
        return;
    }
    
    // Send AJAX request
    fetch('api/update_quest.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_quest_id: userQuestId,
            progress: progress
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI elements
            const percentage = Math.min(100, Math.round((data.progress / data.target_value) * 100));
            
            // Progress bar and value
            document.getElementById(`progress-bar-${userQuestId}`).style.width = `${percentage}%`;
            document.getElementById(`progress-val-${userQuestId}`).textContent = `${data.progress.toLocaleString()} / ${data.target_value.toLocaleString()}`;
            
            if (data.completed) {
                // Mark quest completed visually
                const card = document.getElementById(`quest-card-${userQuestId}`);
                card.classList.add('completed');
                
                // Replace input with badge
                const actionArea = document.getElementById(`quest-action-${userQuestId}`);
                actionArea.innerHTML = `<span class="status-badge"><i class="fa-solid fa-circle-check"></i> Completed</span>`;
                
                // Update gold points in header navbar
                updateHeaderPoints(data.new_total_points);
                
                // Trigger celebratory confetti
                createConfettiExplosion();
                showToast('success', `Quest Completed! +${data.points_awarded} Points awarded!`);
            } else {
                showToast('info', 'Quest progress saved successfully.');
            }
        } else {
            showToast('error', data.message || 'Failed to update quest.');
        }
    })
    .catch(error => {
        console.error('Error updating quest:', error);
        showToast('error', 'A connection error occurred. Please try again.');
    });
}

/**
 * Purchase and Plant tree (AJAX Fetch API)
 */
function purchaseAndPlantTree() {
    if (selectedX === -1 || selectedY === -1) {
        showToast('error', 'Please select a garden slot first.');
        return;
    }
    
    const treeSelector = document.getElementById('quick-tree-select');
    const treeId = parseInt(treeSelector.value);
    
    if (isNaN(treeId) || treeId <= 0) {
        showToast('error', 'Please select a tree to plant.');
        return;
    }
    
    // Send AJAX request to buy and plant tree
    fetch('api/buy_tree.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            tree_id: treeId,
            x: selectedX,
            y: selectedY
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Find selected cell
            const cell = document.querySelector(`.garden-cell[data-x="${selectedX}"][data-y="${selectedY}"]`);
            if (cell) {
                cell.className = 'garden-cell occupied';
                cell.removeAttribute('onclick'); // Remove click handler
                
                // Inject the correct tree SVG
                const selectedOption = treeSelector.options[treeSelector.selectedIndex];
                const treeText = selectedOption.text.split('(')[0].trim().toLowerCase();
                let treeKey = 'sapling';
                if (treeText.includes('oak')) treeKey = 'oak';
                else if (treeText.includes('pine')) treeKey = 'pine';
                else if (treeText.includes('cherry') || treeText.includes('blossom')) treeKey = 'cherry_blossom';
                else if (treeText.includes('palm')) treeKey = 'palm';
                else if (treeText.includes('bonsai')) treeKey = 'bonsai';
                
                cell.innerHTML = `
                    <div class="tree-wrapper" title="${selectedOption.text.split('(')[0].trim()} at (${selectedX}, ${selectedY})">
                        ${getTreeSVGJs(treeKey)}
                    </div>
                    <span class="cell-coordinate">${selectedX},${selectedY}</span>
                `;
            }
            
            // Update points in header
            updateHeaderPoints(data.new_total_points);
            
            // Clean up selections
            selectedX = -1;
            selectedY = -1;
            document.getElementById('plant-panel').style.display = 'none';
            document.getElementById('selection-prompt').style.display = 'block';
            
            // Reset query params so highlights go away
            if (window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            createConfettiExplosion();
            showToast('success', data.message);
        } else {
            showToast('error', data.message || 'Failed to plant tree.');
        }
    })
    .catch(error => {
        console.error('Error planting tree:', error);
        showToast('error', 'A connection error occurred. Please try again.');
    });
}

/**
 * Update points displays
 */
function updateHeaderPoints(newPoints) {
    const badge = document.getElementById('header-points-badge');
    if (badge) {
        const valSpan = badge.querySelector('.points-val');
        if (valSpan) {
            valSpan.textContent = newPoints.toLocaleString();
            badge.classList.add('animate-pulse');
            setTimeout(() => badge.classList.remove('animate-pulse'), 1000);
        }
    }
}

/**
 * SVG JS Library to match PHP SVGs for real-time rendering
 */
function getTreeSVGJs(key) {
    const start = '<svg viewBox="0 0 100 100" class="tree-svg" xmlns="http://www.w3.org/2000/svg">';
    const end = '</svg>';
    let content = '';
    
    switch (key) {
        case 'oak':
            content = `
                <path d="M46,90 L54,90 L53,60 L47,60 Z" fill="#653b1b" />
                <path d="M48,65 L40,55 L43,53 L48,61 Z" fill="#653b1b" />
                <path d="M52,65 L60,53 L63,55 L52,61 Z" fill="#653b1b" />
                <circle cx="50" cy="40" r="22" fill="#2d6a4f" />
                <circle cx="36" cy="48" r="16" fill="#1b4332" />
                <circle cx="64" cy="48" r="16" fill="#1b4332" />
                <circle cx="50" cy="30" r="16" fill="#40916c" />
                <circle cx="42" cy="40" r="12" fill="#52b788" />
                <circle cx="58" cy="40" r="12" fill="#52b788" />
            `;
            break;
        case 'pine':
            content = `
                <rect x="47" y="75" width="6" height="15" fill="#4a2c11" />
                <polygon points="50,15 25,50 75,50" fill="#143625" />
                <polygon points="50,30 20,62 80,62" fill="#1b4d3e" />
                <polygon points="50,45 15,75 85,75" fill="#2d6a4f" />
                <polygon points="50,15 50,50 75,50" fill="#1b4d3e" opacity="0.3" />
                <polygon points="50,30 50,62 80,62" fill="#2d6a4f" opacity="0.3" />
                <polygon points="50,45 50,75 85,75" fill="#52b788" opacity="0.3" />
            `;
            break;
        case 'cherry_blossom':
            content = `
                <path d="M45,90 Q50,75 48,65 T53,50 L56,52 Q51,65 53,75 T50,90 Z" fill="#483226" />
                <path d="M48,65 Q38,58 35,48 L38,46 Q41,54 49,60 Z" fill="#483226" />
                <path d="M51,55 Q62,48 65,40 L68,42 Q63,52 52,58 Z" fill="#483226" />
                <circle cx="34" cy="44" r="12" fill="#ffb5a7" />
                <circle cx="66" cy="38" r="12" fill="#ffcad4" />
                <circle cx="50" cy="32" r="18" fill="#ffb5a7" />
                <circle cx="42" cy="24" r="14" fill="#ffcad4" />
                <circle cx="58" cy="24" r="14" fill="#ffe5ec" />
                <circle cx="48" cy="40" r="12" fill="#fcd5ce" />
                <ellipse cx="28" cy="70" rx="2" ry="3" fill="#ffcad4" transform="rotate(15 28 70)" />
                <ellipse cx="72" cy="78" rx="3" ry="2" fill="#ffb5a7" transform="rotate(-25 72 78)" />
            `;
            break;
        case 'palm':
            content = `
                <path d="M45,90 Q48,70 51,50 Q53,35 55,25 L50,25 Q48,35 46,50 Q43,70 41,90 Z" fill="#8d5b2d" />
                <ellipse cx="43" cy="80" rx="3.5" ry="1.5" fill="#75481f" />
                <ellipse cx="46" cy="65" rx="3" ry="1.5" fill="#75481f" />
                <ellipse cx="49" cy="50" rx="2.5" ry="1.5" fill="#75481f" />
                <ellipse cx="51" cy="35" rx="2" ry="1" fill="#75481f" />
                <path d="M53,25 Q35,28 20,20 Q35,38 52,26 Z" fill="#1b4d3e" />
                <path d="M53,25 Q30,15 15,30 Q30,40 52,26 Z" fill="#2d6a4f" />
                <path d="M53,25 Q70,20 85,15 Q75,32 54,26 Z" fill="#1b4d3e" />
                <path d="M53,25 Q75,30 88,45 Q70,45 54,26 Z" fill="#2d6a4f" />
                <path d="M53,25 Q53,5 45,2 Q50,15 53,26 Z" fill="#40916c" />
                <path d="M53,25 Q63,8 70,5 Q63,18 53,26 Z" fill="#40916c" />
            `;
            break;
        case 'bonsai':
            content = `
                <path d="M25,80 L75,80 L70,90 L30,90 Z" fill="#3d5a80" />
                <rect x="23" y="77" width="54" height="4" rx="2" fill="#293241" />
                <ellipse cx="50" cy="77" rx="23" ry="2" fill="#4a2c11" />
                <path d="M50,77 C42,65 65,58 48,45" fill="none" stroke="#5c3d2e" stroke-width="6" stroke-linecap="round" />
                <path d="M48,45 C38,40 32,48 26,45" fill="none" stroke="#5c3d2e" stroke-width="4" stroke-linecap="round" />
                <path d="M50,55 C60,50 68,52 74,48" fill="none" stroke="#5c3d2e" stroke-width="4" stroke-linecap="round" />
                <ellipse cx="24" cy="44" rx="10" ry="6" fill="#38b000" />
                <ellipse cx="26" cy="42" rx="7" ry="4" fill="#70e000" />
                <ellipse cx="74" cy="46" rx="9" ry="6" fill="#007200" />
                <ellipse cx="73" cy="44" rx="6" ry="4" fill="#38b000" />
                <ellipse cx="48" cy="40" rx="12" ry="7" fill="#008000" />
                <ellipse cx="49" cy="37" rx="8" ry="4" fill="#70e000" />
            `;
            break;
        default:
            content = `
                <rect x="47" y="70" width="6" height="20" fill="#8b5a2b" />
                <circle cx="50" cy="50" r="18" fill="#2e7d32" />
                <circle cx="45" cy="45" r="12" fill="#4caf50" />
            `;
    }
    
    return start + content + end;
}

/**
 * Festive Confetti System
 */
function createConfettiExplosion() {
    const colors = ['#52b788', '#2d6a4f', '#ffd700', '#ffb5a7', '#ffcad4', '#ffe5ec', '#4ea8de'];
    
    for (let i = 0; i < 60; i++) {
        const particle = document.createElement('div');
        particle.className = 'confetti-particle';
        
        // Random style setup
        const bg = colors[Math.floor(Math.random() * colors.length)];
        const left = Math.random() * 100; // random horizontal start position (vw)
        const rotation = Math.random() * 360;
        const scale = Math.random() * 0.6 + 0.4;
        
        particle.style.backgroundColor = bg;
        particle.style.left = `${left}vw`;
        // Initial positioning just above screen
        particle.style.top = `-20px`;
        particle.style.transform = `rotate(${rotation}deg) scale(${scale})`;
        particle.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
        
        document.body.appendChild(particle);
        
        // Animate downward falling
        const duration = Math.random() * 2000 + 2000; // 2-4 seconds fall
        const drift = Math.random() * 200 - 100; // horizontal drift
        
        const animation = particle.animate([
            { top: '-20px', transform: `rotate(${rotation}deg) scale(${scale})` },
            { top: '105vh', transform: `rotate(${rotation + 720}deg) translate(${drift}px) scale(${scale})` }
        ], {
            duration: duration,
            easing: 'cubic-bezier(0.1, 0.8, 0.3, 1)'
        });
        
        animation.onfinish = () => particle.remove();
    }
}
