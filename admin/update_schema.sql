-- Create Vehicles Table
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    color VARCHAR(30) NOT NULL,
    license_plate VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Inventory Table
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    low_stock_threshold INT NOT NULL DEFAULT 5,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Alter Appointments Table
-- Using separate statements to avoid errors if columns already exist in some MariaDB/MySQL versions,
-- though MySQL doesn't natively support IF NOT EXISTS on ADD COLUMN easily without stored procedures.
-- For simplicity in this script, we'll run standard ALTER TABLE, assuming it's the first time running.
-- In a real prod migration, we'd check first.
ALTER TABLE appointments 
    ADD COLUMN vehicle_id INT DEFAULT NULL,
    ADD COLUMN duration_minutes INT NOT NULL DEFAULT 60,
    ADD COLUMN buffer_minutes INT NOT NULL DEFAULT 30,
    ADD FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL;

-- Create Inspections Table
CREATE TABLE IF NOT EXISTS inspections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    type ENUM('pre', 'post') NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);
