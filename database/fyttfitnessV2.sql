CREATE DATABASE IF NOT EXISTS fytt_fitness;
USE fytt_fitness;

-- ==========================================
-- 1. ADMIN & STAFF
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
    specialties VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE
);

-- ==========================================
-- 2. MEMBERS (Updated with Emergency Contact)
-- ==========================================
CREATE TABLE members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    card_uid VARCHAR(50) UNIQUE, -- The RFID Chip ID
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    
    -- NEW: Safety First
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),

    password_hash VARCHAR(255),
    photo_url VARCHAR(255),
    skill_level ENUM('Beginner', 'Advanced', 'Fighter') DEFAULT 'Beginner',
    status ENUM('Active', 'Banned', 'Archived') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE waiver_versions (
    version_id INT AUTO_INCREMENT PRIMARY KEY,
    version_name VARCHAR(50) NOT NULL,
    legal_text TEXT NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
-- 3. MEMBERSHIP & WALLETS
-- ==========================================
CREATE TABLE membership_plans (
    plan_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('General_Gym', 'Boxing', 'Muay_Thai', 'All_Access') NOT NULL,
    grants_door_access BOOLEAN DEFAULT FALSE,
    requires_booking BOOLEAN DEFAULT TRUE,
    credits_given INT DEFAULT NULL, 
    duration_days INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL
);

CREATE TABLE member_subscriptions (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    plan_id INT NOT NULL,
    remaining_credits INT DEFAULT NULL,
    start_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    status ENUM('Active', 'Expired', 'Frozen', 'Depleted') DEFAULT 'Active',
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (plan_id) REFERENCES membership_plans(plan_id)
);

CREATE TABLE freeze_logs (
    freeze_id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    frozen_on DATE NOT NULL,
    unfrozen_on DATE DEFAULT NULL,
    reason VARCHAR(255),
    days_extended INT DEFAULT 0,
    FOREIGN KEY (subscription_id) REFERENCES member_subscriptions(subscription_id)
);

-- ==========================================
-- 4. FACILITY (NEW: Lockers & Rentals)
-- ==========================================
CREATE TABLE lockers (
    locker_id INT AUTO_INCREMENT PRIMARY KEY,
    locker_number VARCHAR(20) NOT NULL UNIQUE, -- e.g. "A-101"
    size ENUM('Small', 'Large') DEFAULT 'Small',
    status ENUM('Available', 'Occupied', 'Broken') DEFAULT 'Available'
);

CREATE TABLE locker_rentals (
    rental_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    locker_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    monthly_price DECIMAL(10, 2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (locker_id) REFERENCES lockers(locker_id)
);

-- ==========================================
-- 5. CLASSES & WAITLISTS (Updated)
-- ==========================================
CREATE TABLE class_schedule (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    coach_id INT,
    title VARCHAR(100) NOT NULL,
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

-- NEW: Waitlist Logic
CREATE TABLE class_waitlist (
    waitlist_id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    member_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_promoted BOOLEAN DEFAULT FALSE, -- True if they got a spot
    FOREIGN KEY (schedule_id) REFERENCES class_schedule(schedule_id),
    FOREIGN KEY (member_id) REFERENCES members(member_id)
);

-- ==========================================
-- 6. ACCESS LOGS
-- ==========================================
CREATE TABLE access_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    subscription_id INT,
    tapped_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    location VARCHAR(50) DEFAULT 'Front Desk',
    access_granted BOOLEAN DEFAULT FALSE,
    denial_reason VARCHAR(100),
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (subscription_id) REFERENCES member_subscriptions(subscription_id)
);

-- ==========================================
-- 7. E-COMMERCE
-- ==========================================
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    base_image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE product_variants (
    variant_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    sku VARCHAR(50) UNIQUE,
    size VARCHAR(20),
    color VARCHAR(30),
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
    variant_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES shop_orders(order_id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id)
);

-- ==========================================
-- 8. PAYMENTS
-- ==========================================
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    order_id INT DEFAULT NULL,
    subscription_id INT DEFAULT NULL,
    rental_id INT DEFAULT NULL, -- NEW: Payment for Locker Rental
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    transaction_ref VARCHAR(100),
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id),
    FOREIGN KEY (order_id) REFERENCES shop_orders(order_id),
    FOREIGN KEY (subscription_id) REFERENCES member_subscriptions(subscription_id),
    FOREIGN KEY (rental_id) REFERENCES locker_rentals(rental_id)
);