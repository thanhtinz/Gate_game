-- =====================================================
-- GATE GAME PORTAL - Database schema
-- DB cổng game (tách riêng với DB của từng game)
-- Import: mysql -u root gate_portal < database/schema.sql
-- =====================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `gate_portal` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gate_portal`;

-- Người dùng cổng game (tài khoản dùng chung cho mọi game)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(20) NOT NULL UNIQUE,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(15) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,            -- bcrypt (nguồn auth duy nhất cho web + mọi game)
  `email_verified` TINYINT NOT NULL DEFAULT 0, -- 1 = đã xác minh email (thay cơ chế active của game)
  `google_id` VARCHAR(30) DEFAULT NULL UNIQUE,   -- Google sub khi đăng nhập bằng Google
  `verify_token` VARCHAR(64) DEFAULT NULL,     -- sha256 của token gửi qua mail
  `verify_expires` DATETIME DEFAULT NULL,
  `verify_sent_at` DATETIME DEFAULT NULL,      -- chống spam gửi lại mail
  `xu` BIGINT NOT NULL DEFAULT 0,              -- số dư xu trên web
  `tong_nap` BIGINT NOT NULL DEFAULT 0,        -- tổng tiền đã nạp (VND)
  `role` TINYINT NOT NULL DEFAULT 0,           -- 0: user, 1: admin
  `status` TINYINT NOT NULL DEFAULT 1,         -- 1: hoạt động, 0: khoá
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Danh sách game trên cổng
CREATE TABLE IF NOT EXISTS `games` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `adapter` VARCHAR(30) NOT NULL,              -- 'nro' | 'avatar' | adapter game mới sau này
  `short_desc` VARCHAR(255) DEFAULT NULL,
  `description` MEDIUMTEXT,                    -- HTML trang chi tiết
  `thumbnail` VARCHAR(255) DEFAULT NULL,
  `banner` VARCHAR(255) DEFAULT NULL,
  `download_links` TEXT,                       -- JSON: [{"label":"Android","url":"..."},...]
  `status` TINYINT NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Server của từng game (mỗi server = 1 database MySQL riêng của game đó)
CREATE TABLE IF NOT EXISTS `game_servers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `db_host` VARCHAR(100) NOT NULL DEFAULT '127.0.0.1',
  `db_port` INT NOT NULL DEFAULT 3306,
  `db_name` VARCHAR(64) NOT NULL,
  `db_user` VARCHAR(64) NOT NULL DEFAULT 'root',
  `db_pass` VARCHAR(255) NOT NULL DEFAULT '',
  `status` TINYINT NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `note` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Banner trang chủ
CREATE TABLE IF NOT EXISTS `banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tin tức & sự kiện
CREATE TABLE IF NOT EXISTS `news` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT DEFAULT NULL,                  -- NULL = tin chung của cổng
  `type` ENUM('news','event') NOT NULL DEFAULT 'news',
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `thumbnail` VARCHAR(255) DEFAULT NULL,
  `summary` VARCHAR(500) DEFAULT NULL,
  `content` MEDIUMTEXT,
  `pinned` TINYINT NOT NULL DEFAULT 0,
  `status` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gói nạp xu web (VND -> xu)
CREATE TABLE IF NOT EXISTS `coin_packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `price_vnd` INT NOT NULL,
  `xu` INT NOT NULL,
  `bonus_xu` INT NOT NULL DEFAULT 0,
  `status` TINYINT NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đơn nạp xu (SePay)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `package_id` INT DEFAULT NULL,
  `code` VARCHAR(30) NOT NULL UNIQUE,          -- nội dung chuyển khoản, vd: GATE123
  `amount_vnd` INT NOT NULL,
  `xu` INT NOT NULL,
  `bonus_xu` INT NOT NULL DEFAULT 0,
  `method` VARCHAR(20) NOT NULL DEFAULT 'sepay',
  `sepay_tx_id` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gói quy đổi xu web -> tiền tệ trong game (theo từng game)
CREATE TABLE IF NOT EXISTS `exchange_packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `xu_cost` INT NOT NULL,
  `currency_key` VARCHAR(20) NOT NULL,         -- theo adapter: nro: vnd/gold/gem | avatar: xu/luong/xeng
  `currency_amount` BIGINT NOT NULL,
  `bonus_amount` BIGINT NOT NULL DEFAULT 0,
  `add_tongnap` INT NOT NULL DEFAULT 0,        -- cộng thêm vào tongnap trong DB game (mở mốc thưởng nạp in-game)
  `status` TINYINT NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lịch sử quy đổi
CREATE TABLE IF NOT EXISTS `exchanges` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `game_id` INT NOT NULL,
  `server_id` INT NOT NULL,
  `package_id` INT NOT NULL,
  `character_id` VARCHAR(30) NOT NULL,
  `character_name` VARCHAR(50) NOT NULL,
  `xu_cost` INT NOT NULL,
  `currency_key` VARCHAR(20) NOT NULL,
  `currency_amount` BIGINT NOT NULL,
  `status` ENUM('completed','failed') NOT NULL,
  `message` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Giftcode trên web (hiển thị icon vật phẩm, thưởng tiền tệ qua adapter)
CREATE TABLE IF NOT EXISTS `giftcodes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `items` TEXT,                                -- JSON hiển thị: [{"icon":"/uploads/x.png","name":"Đá quý","qty":10}]
  `rewards` TEXT,                              -- JSON thưởng thực nhận: [{"currency_key":"gem","amount":100}]
  `max_uses` INT NOT NULL DEFAULT 0,           -- 0 = không giới hạn
  `used_count` INT NOT NULL DEFAULT 0,
  `status` TINYINT NOT NULL DEFAULT 1,
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_game_code` (`game_id`, `code`),
  FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lịch sử nhập giftcode (mỗi user 1 lần / code -> nhập xong tự ẩn với user đó)
CREATE TABLE IF NOT EXISTS `giftcode_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `giftcode_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `game_id` INT NOT NULL,
  `server_id` INT NOT NULL,
  `character_id` VARCHAR(30) NOT NULL,
  `character_name` VARCHAR(50) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_code` (`giftcode_id`, `user_id`),
  FOREIGN KEY (`giftcode_id`) REFERENCES `giftcodes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cấu hình website (key-value)
CREATE TABLE IF NOT EXISTS `settings` (
  `k` VARCHAR(50) PRIMARY KEY,
  `v` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log webhook SePay (đối soát)
CREATE TABLE IF NOT EXISTS `sepay_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `raw` TEXT,
  `order_code` VARCHAR(30) DEFAULT NULL,
  `result` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DỮ LIỆU MẶC ĐỊNH
-- =====================================================

-- Admin mặc định: admin / admin123  (ĐỔI MẬT KHẨU NGAY SAU KHI CÀI)
INSERT INTO `users` (`username`,`password`,`role`) VALUES
('admin', '$2y$12$w9/lf3nBfzAPpTJCh1u2p.wadp2uKsa/rDXM4ZTqT7mkQxXQ1.aAC', 1)
ON DUPLICATE KEY UPDATE `role` = 1;

-- Cấu hình mặc định
INSERT INTO `settings` (`k`,`v`) VALUES
('site_name', 'Gate Game'),
('site_desc', 'Cổng game giải trí'),
('site_logo', ''),
('site_keywords', 'game, nap the, cong game'),
('xu_rate', '1'),                       -- 1000 VND = ? xu * (price/1000) — chỉ hiển thị, gói quyết định
('sepay_enabled', '1'),
('sepay_bank', 'MBBank'),
('sepay_account', ''),
('sepay_account_name', ''),
('sepay_prefix', 'GATE'),
('sepay_api_key', ''),
('central_auth_key', ''),               -- key để game server gọi API /api/game-auth/verify
-- Xác minh email + SMTP
('email_verify_required', '0'),         -- 1 = bắt buộc xác minh email mới được vào game/nạp/đổi
('smtp_host', ''),
('smtp_port', '587'),
('smtp_encryption', 'tls'),             -- tls | ssl | none
('smtp_user', ''),
('smtp_pass', ''),
('smtp_from', ''),                      -- email người gửi (mặc định = smtp_user)
('smtp_from_name', 'Gate Game'),
('currency_items', '[]'),      -- gan tien te game voi item_id trong DB game
('game_icon_base', '[]'),
-- Đăng nhập Google (OAuth 2.0)
('google_enabled', '0'),
('google_client_id', ''),
('google_client_secret', '')
ON DUPLICATE KEY UPDATE `k` = `k`;

-- 2 game mặc định (sửa lại thông tin trong admin)
INSERT INTO `games` (`slug`,`name`,`adapter`,`short_desc`,`description`,`download_links`,`sort_order`) VALUES
('ngoc-rong', 'Ngọc Rồng Online', 'nro', 'Game nhập vai 7 viên ngọc rồng', '<p>Đang cập nhật...</p>', '[{"label":"Android APK","url":"#"},{"label":"iOS","url":"#"},{"label":"PC (Windows)","url":"#"}]', 1),
('avatar', 'Avatar 2D', 'avatar', 'Game nông trại - thời trang Avatar', '<p>Đang cập nhật...</p>', '[{"label":"Android APK","url":"#"},{"label":"iOS","url":"#"}]', 2)
ON DUPLICATE KEY UPDATE `slug` = `slug`;

-- Server mẫu (sửa thông tin DB thật trong admin)
INSERT INTO `game_servers` (`game_id`,`name`,`db_host`,`db_port`,`db_name`,`db_user`,`db_pass`,`sort_order`)
SELECT g.id, 'Server 1', '127.0.0.1', 3306, 'team2026', 'root', '', 1 FROM `games` g WHERE g.slug='ngoc-rong'
  AND NOT EXISTS (SELECT 1 FROM game_servers s WHERE s.game_id = g.id);
INSERT INTO `game_servers` (`game_id`,`name`,`db_host`,`db_port`,`db_name`,`db_user`,`db_pass`,`sort_order`)
SELECT g.id, 'Server 1', '127.0.0.1', 3306, 'avatar_2x', 'root', '', 1 FROM `games` g WHERE g.slug='avatar'
  AND NOT EXISTS (SELECT 1 FROM game_servers s WHERE s.game_id = g.id);

-- Gói xu mẫu
INSERT INTO `coin_packages` (`name`,`price_vnd`,`xu`,`bonus_xu`,`sort_order`) VALUES
('Gói 20K', 20000, 20000, 0, 1),
('Gói 50K', 50000, 50000, 2500, 2),
('Gói 100K', 100000, 100000, 10000, 3),
('Gói 200K', 200000, 200000, 30000, 4),
('Gói 500K', 500000, 500000, 100000, 5);
