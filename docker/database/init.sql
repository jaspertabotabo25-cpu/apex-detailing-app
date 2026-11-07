-- Schema and seed data for Apex Custom Detailing
USE apex_detailing;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT NOT NULL,
    email VARCHAR(180) NOT NULL,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    address LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    color VARCHAR(30) NOT NULL,
    license_plate VARCHAR(20) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX IDX_1FCE69FAA76ED395 (user_id),
    PRIMARY KEY(id),
    CONSTRAINT FK_1FCE69FAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT DEFAULT 1 NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    vehicle_id INT DEFAULT NULL,
    service_type VARCHAR(255) NOT NULL,
    appointment_date DATETIME NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    location LONGTEXT DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'pending' NOT NULL,
    duration_minutes INT DEFAULT 60 NOT NULL,
    buffer_minutes INT DEFAULT 30 NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX IDX_6A41727AA76ED395 (user_id),
    INDEX IDX_6A41727A545317D1 (vehicle_id),
    PRIMARY KEY(id),
    CONSTRAINT FK_6A41727AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT FK_6A41727A545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE SET NULL
) DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS inspections (
    id INT AUTO_INCREMENT NOT NULL,
    appointment_id INT NOT NULL,
    type VARCHAR(10) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    notes LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX IDX_86254990E5B533F9 (appointment_id),
    PRIMARY KEY(id),
    CONSTRAINT FK_86254990E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointments (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 0 NOT NULL,
    low_stock_threshold INT DEFAULT 5 NOT NULL,
    last_updated DATETIME NOT NULL,
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS portfolio (
    id INT AUTO_INCREMENT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4;

-- Seed Default Admin User: admin@apexcustomdetail.ph / ApexSecure@2026!
INSERT INTO users (email, roles, password, name, phone, address, created_at)
VALUES ('admin@apexcustomdetail.ph', '["ROLE_ADMIN", "ROLE_USER"]', '$2y$13$RVaWn3.Ty42hE/QAQ2ISAOo0mLSHYfNQHHWEOT47ZrjrWx6tqxNMS', 'System Administrator', '+63 912 345 6789', 'Apex Detailing Studio, Manila', NOW())
ON DUPLICATE KEY UPDATE roles='["ROLE_ADMIN", "ROLE_USER"]';

-- Seed Default Services
INSERT IGNORE INTO services (name, is_active, created_at) VALUES
('Standard Exterior Wash', 1, NOW()),
('Interior Deep Detailing', 1, NOW()),
('Full Premium Detailing Package', 1, NOW()),
('Custom Rim & Ceramic Coating', 1, NOW()),
('Motorcycle Custom Detailing', 1, NOW());

-- Seed Initial Inventory
INSERT IGNORE INTO inventory (item_name, quantity, low_stock_threshold, last_updated) VALUES
('Ceramic Coating Pro (50ml)', 8, 3, NOW()),
('Microfiber Towels Pack (10x)', 25, 5, NOW()),
('pH-Neutral Snow Foam (5L)', 4, 3, NOW()),
('Iron Fallout Remover (1L)', 6, 2, NOW()),
('Leather Conditioner & Shield', 12, 4, NOW()),
('Clay Bar Detailing Pack', 3, 4, NOW());

-- Seed Initial Portfolio Showcase
INSERT IGNORE INTO portfolio (image_path, description, created_at) VALUES
('/assets/work-premium-detailing.jpg', 'Multi-stage paint correction and 5-year ceramic protection on BMW M4.', NOW()),
('/assets/work-interior-detailing.jpg', 'Complete interior rejuvenation with steam decontamination and leather conditioning.', NOW()),
('/assets/work-custom-rim.jpg', 'Custom wheel deep decontamination, caliber polish, and high-temp ceramic shield.', NOW()),
('/assets/work-full-body-wash.jpg', 'Touchless foam pre-wash and dual-bucket swirl-free maintenance wash.', NOW());

