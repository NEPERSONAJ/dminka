-- Create database
CREATE DATABASE IF NOT EXISTS gacha_accounts DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gacha_accounts;

-- Admins table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Games table
CREATE TABLE games (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name_en VARCHAR(100) NOT NULL,
    name_ru VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    seo_title VARCHAR(255),
    seo_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Heroes table
CREATE TABLE heroes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    game_id INT NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    name_ru VARCHAR(100) NOT NULL,
    type ENUM('legendary', 'epic') NOT NULL,
    image VARCHAR(255),
    seo_title VARCHAR(255),
    seo_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Accounts table
CREATE TABLE accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    game_id INT NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    resources TEXT,
    heroes JSON,
    images JSON,
    seo_title VARCHAR(255),
    seo_description TEXT,
    status ENUM('available', 'sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Blog posts table
CREATE TABLE blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title_en VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) NOT NULL,
    content_en TEXT NOT NULL,
    content_ru TEXT NOT NULL,
    image VARCHAR(255),
    seo_title VARCHAR(255),
    seo_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    account_id INT,
    rating TINYINT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL
);

-- Promocodes table
CREATE TABLE promocodes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title_en VARCHAR(255) NOT NULL,
    title_ru VARCHAR(255) NOT NULL,
    description_en TEXT,
    description_ru TEXT,
    code VARCHAR(50) UNIQUE NOT NULL,
    image VARCHAR(255),
    expiry_date DATETIME NOT NULL,
    seo_title VARCHAR(255),
    seo_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Activity log table
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert default settings
INSERT INTO settings (name, value) VALUES
('telegram_bot_token', ''),
('telegram_chat_id', ''),
('ai_api_key', ''),
('ai_api_endpoint', 'https://api.openai.com/v1/completions');