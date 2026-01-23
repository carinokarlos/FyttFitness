CREATE DATABASE IF NOT EXISTS fytt_fitness;
USE fytt_fitness;

-- ==========================================
-- 1. CORE ADMIN & STAFF
-- ==========================================
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('SuperAdmin', 'FrontDesk', 'Sales') DEFAULT 'FrontDesk',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE coaches (
    coach_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    bio TEXT,
    specialties VARCHAR(255), -- Comma separated e.g. "Boxing, Muay Thai"
    is_active BOOLEAN DEFAULT TRUE
);

-- ==========================================
-- 2. LEGAL (Waiver Loophole Fix)
-- ==========================================
CREATE TABLE waiver_versions (
    version_id INT AUTO_INCREMENT PRIMARY KEY,
    version_name VARCHAR(50) NOT NULL, -- e.g. "v2026.1"
    legal_text TEXT NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 3. MEMBERS
-- ==========================================
CREATE TABLE members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    card_uid VARCHAR(50) UNIQUE, -- The RFID Chip ID
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255),
    photo_url VARCHAR(255),
    skill_level ENUM('Beginner', 'Advanced', 'Fighter') DEFAULT 'Beginner',
    status ENUM('Active', 'Banned', 'Archived') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tracks which specific waiver version a member signed
CREATE TABLE signed_waivers (
    sign_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT,
    version_id INT,
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (version_id) REFERENCES waiver_versions(version_id)
);

-- ==========================================
-- 4. MEMBERSHIP PRODUCTS (The Double-Dip Fix)
-- ==========================================
CREATE TABLE membership_plans (
    plan_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL, -- e.g. "10 Class Boxing Pack"
    category ENUM('General_Gym', 'Boxing', 'Muay_Thai', 'All_Access') NOT NULL,
    
    -- LOOPHOLE FIX #1: Access Control Logic
    grants_door_access BOOLEAN DEFAULT FALSE, -- Does this open the front door?
    requires_booking BOOLEAN DEFAULT TRUE, -- Does this require a class slot?
    
    credits_given INT DEFAULT NULL, -- NULL = Unlimited
    duration_days INT NOT NULL, -- e.g. 30 days
    price DECIMAL(10, 2) NOT NULL
);

-- ==========================================
-- 5. THE WALLET (Subscriptions)
-- ==========================================
CREATE TABLE member_subscriptions (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    plan_id INT NOT NULL,
    
    remaining_credits INT DEFAULT NULL, -- NULL means Unlimited
    start_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    
    status ENUM('Active', 'Expired', 'Frozen', 'Depleted') DEFAULT 'Active',
    
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (plan_id) REFERENCES membership_plans(plan_id)
);

-- LOOPHOLE FIX #2: Freeze Logs
CREATE TABLE freeze_logs (
    freeze_id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    frozen_on DATE NOT NULL,
    unfrozen_on DATE DEFAULT NULL,
    reason VARCHAR(255),
    days_extended INT DEFAULT 0, -- How many days added to expiry
    FOREIGN KEY (subscription_id) REFERENCES member_subscriptions(subscription_id)
);

-- ==========================================
-- 6. ACCESS CONTROL (RFID Taps)
-- ==========================================
CREATE TABLE access_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    subscription_id INT, -- Which pack paid for this entry?
    tapped_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    location VARCHAR(50) DEFAULT 'Front Desk',
    access_granted BOOLEAN DEFAULT FALSE,
    denial_reason VARCHAR(100), -- e.g. "Expired", "No Credits"
    
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (subscription_id) REFERENCES member_subscriptions(subscription_id)
);

-- ==========================================
-- 7. CLASSES & BOOKINGS
-- ==========================================
CREATE TABLE class_schedule (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    coach_id INT,
    title VARCHAR(100) NOT NULL, -- e.g. "6PM Muay Thai"
    class_type ENUM('Boxing', 'Muay_Thai', 'Strength') NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    max_capacity INT DEFAULT 20,
    FOREIGN KEY (coach_id) REFERENCES coaches(coach_id)
);

CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    schedule_id INT NOT NULL,
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Confirmed', 'Cancelled', 'NoShow', 'Attended') DEFAULT 'Confirmed',
    
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (schedule_id) REFERENCES class_schedule(schedule_id)
);

-- ==========================================
-- 8. E-COMMERCE (Inventory Fix)
-- ==========================================
-- Parent Product
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL, -- e.g. "FYTT Pro Gloves"
    description TEXT,
    category VARCHAR(50), -- Gear, Apparel, Supplements
    base_image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE
);

-- LOOPHOLE FIX #3: Variants (SKUs)
CREATE TABLE product_variants (
    variant_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    sku VARCHAR(50) UNIQUE, -- e.g. "TEE-BLK-L"
    size VARCHAR(20), -- S, M, L, 14oz, 16oz
    color VARCHAR(30), -- Black, Red
    stock_quantity INT DEFAULT 0,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE shop_orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Paid', 'Fulfilled', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id)
);

CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    variant_id INT NOT NULL, -- Links to specific size/color
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES shop_orders(order_id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id)
);

-- ==========================================
-- 9. PAYMENTS (The Central Ledger)
-- ==========================================
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    
    -- Can pay for an Order OR a Subscription
    order_id INT DEFAULT NULL,
    subscription_id INT DEFAULT NULL,
    
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50), -- Cash, Stripe, GCash
    transaction_ref VARCHAR(100), -- Reference number
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (order_id) REFERENCES shop_orders(order_id),
    FOREIGN KEY (subscription_id) REFERENCES member_subscriptions(subscription_id)
);