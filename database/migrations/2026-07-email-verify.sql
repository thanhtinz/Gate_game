-- Nâng cấp cho bản cài trước tính năng xác minh email
-- mysql -u root -p gate_portal < database/migrations/2026-07-email-verify.sql
SET NAMES utf8mb4;

ALTER TABLE `users`
  ADD COLUMN `email_verified` TINYINT NOT NULL DEFAULT 0 AFTER `password`,
  ADD COLUMN `verify_token` VARCHAR(64) DEFAULT NULL AFTER `email_verified`,
  ADD COLUMN `verify_expires` DATETIME DEFAULT NULL AFTER `verify_token`,
  ADD COLUMN `verify_sent_at` DATETIME DEFAULT NULL AFTER `verify_expires`;

-- Tài khoản có sẵn coi như đã xác minh để không khoá nhầm người chơi cũ
UPDATE `users` SET `email_verified` = 1;

INSERT INTO `settings` (`k`,`v`) VALUES
('email_verify_required','0'),
('smtp_host',''),
('smtp_port','587'),
('smtp_encryption','tls'),
('smtp_user',''),
('smtp_pass',''),
('smtp_from',''),
('smtp_from_name','Gate Game')
ON DUPLICATE KEY UPDATE `k` = `k`;

-- Google OAuth
ALTER TABLE `users` ADD COLUMN `google_id` VARCHAR(30) DEFAULT NULL UNIQUE AFTER `email_verified`;
INSERT INTO `settings` (`k`,`v`) VALUES
('google_enabled','0'),('google_client_id',''),('google_client_secret','')
ON DUPLICATE KEY UPDATE `k` = `k`;

-- Icon tiền tệ theo item DB game
INSERT INTO `settings` (`k`,`v`) VALUES
('currency_items','[]'),('game_icon_base','[]')
ON DUPLICATE KEY UPDATE `k` = `k`;
