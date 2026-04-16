CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS profile_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_code VARCHAR(50) NOT NULL UNIQUE,
    role_label VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_profiles (
    user_id INT PRIMARY KEY,
    phone VARCHAR(40) NOT NULL DEFAULT '',
    organisation VARCHAR(120) NOT NULL DEFAULT '',
    city VARCHAR(80) NOT NULL DEFAULT '',
    biography TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_categories_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fundraiser_user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(160) NOT NULL,
    service_type VARCHAR(80) NOT NULL,
    story TEXT NOT NULL,
    funding_goal DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    current_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    start_date DATE NOT NULL,
    end_date DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_campaigns_fundraiser FOREIGN KEY (fundraiser_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_campaigns_category FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_campaigns_fundraiser (fundraiser_user_id),
    INDEX idx_campaigns_category (category_id),
    INDEX idx_campaigns_status (status),
    INDEX idx_campaigns_service (service_type)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS favourites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_user_id INT NOT NULL,
    campaign_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_favourites_donor FOREIGN KEY (donor_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favourites_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    UNIQUE KEY uq_donor_campaign (donor_user_id, campaign_id),
    INDEX idx_favourites_campaign (campaign_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS campaign_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    viewer_user_id INT NULL,
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_views_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    CONSTRAINT fk_views_viewer FOREIGN KEY (viewer_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_views_campaign (campaign_id),
    INDEX idx_views_date (viewed_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    donor_user_id INT NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    message VARCHAR(255) NOT NULL DEFAULT '',
    donated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_donations_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    CONSTRAINT fk_donations_donor FOREIGN KEY (donor_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_donations_donor (donor_user_id),
    INDEX idx_donations_date (donated_at)
) ENGINE=InnoDB;
