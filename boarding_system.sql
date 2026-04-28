-- ============================================================
--  BOARDING HOUSE MANAGEMENT SYSTEM
--  Database: boarding_system
--  Compatible: MySQL 5.7+ / MariaDB 10.3+
--  Red #e63946 · Navy #1a1a2e theme
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================
-- CREATE & SELECT DATABASE
-- ============================================================
CREATE DATABASE IF NOT EXISTS `boarding_system`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `boarding_system`;

-- ============================================================
-- TABLE: admins
-- ============================================================
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `username`   VARCHAR(60)  NOT NULL UNIQUE,
  `email`      VARCHAR(150) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `phone`      VARCHAR(20)  DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: users  (tenants)
-- ============================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL UNIQUE,
  `phone`      VARCHAR(20)  DEFAULT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: rooms
-- ============================================================
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `id`          INT(11)       NOT NULL AUTO_INCREMENT,
  `room_number` VARCHAR(20)   NOT NULL UNIQUE,
  `room_type`   VARCHAR(60)   NOT NULL,
  `floor`       INT(3)        NOT NULL DEFAULT 1,
  `capacity`    INT(3)        NOT NULL DEFAULT 1,
  `price`       DECIMAL(10,2) NOT NULL,
  `amenities`   VARCHAR(300)  DEFAULT NULL,
  `description` TEXT          DEFAULT NULL,
  `status`      ENUM('available','reserved','occupied','maintenance')
                              NOT NULL DEFAULT 'available',
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: bookings
-- ============================================================
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id`         INT(11)       NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11)       NOT NULL,
  `room_id`    INT(11)       NOT NULL,
  `check_in`   DATE          NOT NULL,
  `check_out`  DATE          NOT NULL,
  `amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`     ENUM('pending','confirmed','cancelled')
                             NOT NULL DEFAULT 'pending',
  `notes`      TEXT          DEFAULT NULL,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_room` FOREIGN KEY (`room_id`)
    REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA — Admins
-- Passwords are bcrypt hashes of "admin123"
-- ============================================================
INSERT INTO `admins` (`name`, `username`, `email`, `password`, `phone`) VALUES
('Super Admin',  'admin',   'admin@boardinghouse.lk',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+94 77 000 0001'),
('Manager',      'manager', 'manager@boardinghouse.lk','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+94 77 000 0002');

-- ============================================================
-- SEED DATA — Tenants (users)
-- Passwords are bcrypt hashes of "password123"
-- ============================================================
INSERT INTO `users` (`name`, `email`, `phone`, `password`) VALUES
('Arjun Mehta',    'arjun@email.com',   '+94 77 111 1001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Priya Sharma',   'priya@email.com',   '+94 77 111 1002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Rohan Das',      'rohan@email.com',   '+94 77 111 1003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Anita Rao',      'anita@email.com',   '+94 77 111 1004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Vikram Nair',    'vikram@email.com',  '+94 77 111 1005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Sunita Pillai',  'sunita@email.com',  '+94 77 111 1006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Karan Joshi',    'karan@email.com',   '+94 77 111 1007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Meena Iyer',     'meena@email.com',   '+94 77 111 1008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================================
-- SEED DATA — Rooms
-- ============================================================
INSERT INTO `rooms` (`room_number`, `room_type`, `floor`, `capacity`, `price`, `amenities`, `description`, `status`) VALUES
-- Floor 1
('101', 'Single Room',  1, 1,  4500.00, 'WiFi, Fan',              'Cozy single room on ground floor. Quiet and well-ventilated.', 'available'),
('102', 'Double Room',  1, 2,  7000.00, 'WiFi, AC, Attached Bathroom', 'Spacious double room with private bathroom and AC.', 'occupied'),
('103', 'Single Room',  1, 1,  4500.00, 'WiFi, Fan',              'Standard single room near entrance. Easy access.', 'reserved'),
('104', 'Triple Room',  1, 3,  9000.00, 'WiFi, Fan, Shared Bathroom', 'Shared triple room ideal for students.', 'available'),
-- Floor 2
('201', 'Suite',        2, 2, 12000.00, 'WiFi, AC, Attached Bathroom, TV, Wardrobe', 'Premium suite with all amenities and city view.', 'available'),
('202', 'Double Room',  2, 2,  7000.00, 'WiFi, AC, Fan',          'Well-furnished double room on second floor.', 'occupied'),
('203', 'Single Room',  2, 1,  4500.00, 'WiFi, Fan',              'Compact single room with natural lighting.', 'occupied'),
('204', 'Double Room',  2, 2,  7500.00, 'WiFi, AC, Balcony',      'Double room with private balcony and AC.', 'available'),
-- Floor 3
('301', 'Single Room',  3, 1,  5000.00, 'WiFi, AC',               'Single room with air conditioning on third floor.', 'available'),
('302', 'Double Room',  3, 2,  7500.00, 'WiFi, AC, TV',           'Double room with television and city view.', 'available'),
('303', 'Triple Room',  3, 3,  9500.00, 'WiFi, AC, Shared Bathroom', 'Large triple room suitable for family or friends.', 'occupied'),
('304', 'Suite',        3, 2, 13000.00, 'WiFi, AC, TV, Balcony, Attached Bathroom', 'Luxury suite with balcony and premium furnishings.', 'available'),
-- Floor 4
('401', 'Single Room',  4, 1,  5000.00, 'WiFi, AC',               'Top floor single room, bright and airy.', 'available'),
('402', 'Double Room',  4, 2,  8000.00, 'WiFi, AC, TV, Wardrobe', 'Spacious double room with wardrobe and TV.', 'available');

-- ============================================================
-- SEED DATA — Bookings
-- ============================================================
INSERT INTO `bookings` (`user_id`, `room_id`, `check_in`, `check_out`, `amount`, `status`, `notes`) VALUES
(1, 2,  '2025-04-01', '2025-06-30',  21000.00, 'confirmed', 'Long-term stay, needs parking.'),
(2, 6,  '2025-04-05', '2025-07-04',  21000.00, 'confirmed', NULL),
(3, 7,  '2025-04-10', '2025-07-09',  13500.00, 'confirmed', 'Student — requires quiet environment.'),
(4, 1,  '2025-05-01', '2025-07-31',   9000.00, 'pending',   'Prefers ground floor.'),
(5, 3,  '2025-04-20', '2025-05-19',   4500.00, 'cancelled', 'Cancelled due to personal reasons.'),
(6, 4,  '2025-05-15', '2025-08-14',  27000.00, 'pending',   NULL),
(7, 5,  '2025-06-01', '2025-08-31',  36000.00, 'pending',   'Interested in suite on floor 2.'),
(8, 11, '2025-04-25', '2025-07-24',  28500.00, 'confirmed', 'Three persons, family booking.');

-- ============================================================
-- USEFUL VIEWS
-- ============================================================

-- View: all bookings with tenant and room info
CREATE OR REPLACE VIEW `v_bookings_full` AS
SELECT
  b.id           AS booking_id,
  b.status       AS booking_status,
  b.check_in,
  b.check_out,
  b.amount,
  b.notes,
  b.created_at,
  u.id           AS user_id,
  u.name         AS tenant_name,
  u.email        AS tenant_email,
  u.phone        AS tenant_phone,
  r.id           AS room_id,
  r.room_number,
  r.room_type,
  r.floor,
  r.price        AS room_price,
  r.amenities,
  r.status       AS room_status
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN rooms  r ON b.room_id  = r.id;

-- View: occupancy summary
CREATE OR REPLACE VIEW `v_occupancy_summary` AS
SELECT
  status,
  COUNT(*) AS room_count,
  ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM rooms), 1) AS percentage
FROM rooms
GROUP BY status;

-- ============================================================
-- END OF FILE
-- ============================================================
