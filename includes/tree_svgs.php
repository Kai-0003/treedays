<?php
/**
 * Render an SVG graphic for a given tree type.
 *
 * @param string $type The tree type identifier (oak, pine, cherry_blossom, palm, bonsai)
 * @return string The inline SVG markup
 */
function get_tree_svg($type) {
    $type = strtolower($type);
    
    $svgStart = '<svg viewBox="0 0 100 100" class="tree-svg" xmlns="http://www.w3.org/2000/svg">';
    $svgEnd = '</svg>';
    $content = '';
    
    switch ($type) {
        case 'oak':
            // Robust trunk with overlapping lush green circles for the canopy
            $content = '
                <!-- Oak Trunk -->
                <path d="M46,90 L54,90 L53,60 L47,60 Z" fill="#653b1b" />
                <path d="M48,65 L40,55 L43,53 L48,61 Z" fill="#653b1b" />
                <path d="M52,65 L60,53 L63,55 L52,61 Z" fill="#653b1b" />
                <!-- Oak Canopy -->
                <circle cx="50" cy="40" r="22" fill="#2d6a4f" />
                <circle cx="36" cy="48" r="16" fill="#1b4332" />
                <circle cx="64" cy="48" r="16" fill="#1b4332" />
                <circle cx="50" cy="30" r="16" fill="#40916c" />
                <circle cx="42" cy="40" r="12" fill="#52b788" />
                <circle cx="58" cy="40" r="12" fill="#52b788" />
            ';
            break;
            
        case 'pine':
            // Triangular layered branches of a classic evergreen pine
            $content = '
                <!-- Pine Trunk -->
                <rect x="47" y="75" width="6" height="15" fill="#4a2c11" />
                <!-- Pine Needles Layers -->
                <polygon points="50,15 25,50 75,50" fill="#143625" />
                <polygon points="50,30 20,62 80,62" fill="#1b4d3e" />
                <polygon points="50,45 15,75 85,75" fill="#2d6a4f" />
                <!-- Highlights -->
                <polygon points="50,15 50,50 75,50" fill="#1b4d3e" opacity="0.3" />
                <polygon points="50,30 50,62 80,62" fill="#2d6a4f" opacity="0.3" />
                <polygon points="50,45 50,75 85,75" fill="#52b788" opacity="0.3" />
            ';
            break;
            
        case 'cherry_blossom':
            // Sakura pink floral shapes with gnarled trunk and dynamic color shades
            $content = '
                <!-- Cherry Blossom Trunk -->
                <path d="M45,90 Q50,75 48,65 T53,50 L56,52 Q51,65 53,75 T50,90 Z" fill="#483226" />
                <path d="M48,65 Q38,58 35,48 L38,46 Q41,54 49,60 Z" fill="#483226" />
                <path d="M51,55 Q62,48 65,40 L68,42 Q63,52 52,58 Z" fill="#483226" />
                <!-- Sakura Blooms -->
                <circle cx="34" cy="44" r="12" fill="#ffb5a7" />
                <circle cx="66" cy="38" r="12" fill="#ffcad4" />
                <circle cx="50" cy="32" r="18" fill="#ffb5a7" />
                <circle cx="42" cy="24" r="14" fill="#ffcad4" />
                <circle cx="58" cy="24" r="14" fill="#ffe5ec" />
                <circle cx="48" cy="40" r="12" fill="#fcd5ce" />
                <!-- Falling Petals (ambient) -->
                <ellipse cx="28" cy="70" rx="2" ry="3" fill="#ffcad4" transform="rotate(15 28 70)" />
                <ellipse cx="72" cy="78" rx="3" ry="2" fill="#ffb5a7" transform="rotate(-25 72 78)" />
            ';
            break;
            
        case 'palm':
            // Curved tropical trunk with fan-like palm leaves/fronds spreading out
            $content = '
                <!-- Palm Trunk (segmented look) -->
                <path d="M45,90 Q48,70 51,50 Q53,35 55,25 L50,25 Q48,35 46,50 Q43,70 41,90 Z" fill="#8d5b2d" />
                <ellipse cx="43" cy="80" rx="3.5" ry="1.5" fill="#75481f" />
                <ellipse cx="46" cy="65" rx="3" ry="1.5" fill="#75481f" />
                <ellipse cx="49" cy="50" rx="2.5" ry="1.5" fill="#75481f" />
                <ellipse cx="51" cy="35" rx="2" ry="1" fill="#75481f" />
                <!-- Palm Fronds -->
                <!-- Left Fronds -->
                <path d="M53,25 Q35,28 20,20 Q35,38 52,26 Z" fill="#1b4d3e" />
                <path d="M53,25 Q30,15 15,30 Q30,40 52,26 Z" fill="#2d6a4f" />
                <!-- Right Fronds -->
                <path d="M53,25 Q70,20 85,15 Q75,32 54,26 Z" fill="#1b4d3e" />
                <path d="M53,25 Q75,30 88,45 Q70,45 54,26 Z" fill="#2d6a4f" />
                <!-- Top Fronds -->
                <path d="M53,25 Q53,5 45,2 Q50,15 53,26 Z" fill="#40916c" />
                <path d="M53,25 Q63,8 70,5 Q63,18 53,26 Z" fill="#40916c" />
            ';
            break;
            
        case 'bonsai':
            // Stylized miniature tree in an elegant geometric pot/tray
            $content = '
                <!-- Ceramic Pot -->
                <path d="M25,80 L75,80 L70,90 L30,90 Z" fill="#3d5a80" />
                <rect x="23" y="77" width="54" height="4" rx="2" fill="#293241" />
                <!-- Soil -->
                <ellipse cx="50" cy="77" rx="23" ry="2" fill="#4a2c11" />
                <!-- Gnarled Trunk -->
                <path d="M50,77 C42,65 65,58 48,45" fill="none" stroke="#5c3d2e" stroke-width="6" stroke-linecap="round" />
                <path d="M48,45 C38,40 32,48 26,45" fill="none" stroke="#5c3d2e" stroke-width="4" stroke-linecap="round" />
                <path d="M50,55 C60,50 68,52 74,48" fill="none" stroke="#5c3d2e" stroke-width="4" stroke-linecap="round" />
                <!-- Leaves Cushions -->
                <!-- Left Foliage -->
                <ellipse cx="24" cy="44" rx="10" ry="6" fill="#38b000" />
                <ellipse cx="26" cy="42" rx="7" ry="4" fill="#70e000" />
                <!-- Right Foliage -->
                <ellipse cx="74" cy="46" rx="9" ry="6" fill="#007200" />
                <ellipse cx="73" cy="44" rx="6" ry="4" fill="#38b000" />
                <!-- Crown Foliage -->
                <ellipse cx="48" cy="40" rx="12" ry="7" fill="#008000" />
                <ellipse cx="49" cy="37" rx="8" ry="4" fill="#70e000" />
            ';
            break;
            
        default:
            // Fallback: A basic generic green sapling
            $content = '
                <rect x="47" y="70" width="6" height="20" fill="#8b5a2b" />
                <circle cx="50" cy="50" r="18" fill="#2e7d32" />
                <circle cx="45" cy="45" r="12" fill="#4caf50" />
            ';
            break;
    }
    
    return $svgStart . $content . $svgEnd;
}
