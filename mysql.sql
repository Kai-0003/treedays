-- 1. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    total_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Daily Quests Table (The template for available quests)
CREATE TABLE daily_quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    target_value INT NOT NULL, -- e.g., 5000 for steps
    points_reward INT NOT NULL
);

-- 3. User Quests Table (Tracking individual progress for the day)
CREATE TABLE user_quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quest_id INT NOT NULL,
    progress INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    quest_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES daily_quests(id) ON DELETE CASCADE
);

-- 4. Tree Shop Table (Available trees to buy)
CREATE TABLE tree_shop (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tree_name VARCHAR(100) NOT NULL,
    cost_points INT NOT NULL,
    image_url VARCHAR(255) NOT NULL
);

-- 5. User Garden Table (Inventory and placement of purchased trees)
CREATE TABLE user_garden (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tree_id INT NOT NULL,
    x_coordinate INT DEFAULT 0, -- For grid placement in UI
    y_coordinate INT DEFAULT 0,
    planted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tree_id) REFERENCES tree_shop(id) ON DELETE CASCADE
);

-- Seed Daily Quests
INSERT INTO daily_quests (title, description, target_value, points_reward) VALUES
('Morning Walk', 'Walk 5,000 steps today to boost your metabolism.', 5000, 50),
('Afternoon Jog', 'Run 2 kilometers (2000 meters) to improve stamina.', 2000, 100),
('Hydration Hero', 'Drink 8 glasses of water to stay hydrated throughout the day.', 8, 30),
('Zen Mindfulness', 'Complete 30 minutes of yoga or deep breathing exercises.', 30, 60);

-- Seed Tree Shop
INSERT INTO tree_shop (tree_name, cost_points, image_url) VALUES
('Oak Tree', 100, 'oak'),
('Pine Tree', 150, 'pine'),
('Cherry Blossom Tree', 250, 'cherry_blossom'),
('Palm Tree', 200, 'palm'),
('Bonsai Tree', 400, 'bonsai');