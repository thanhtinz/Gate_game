# 🎮 Gate Game — Cổng game đa tựa

Cổng game trung tâm cho nhiều game (hiện có **Ngọc Rồng Online** và **Avatar 2D**), thay cho việc mỗi game một website riêng.

## Tính năng

**Người chơi**
- Tài khoản dùng chung: đăng ký 1 lần trên web → tự tạo tài khoản trong DB của **tất cả game/server**; đổi mật khẩu trên web đồng bộ xuống mọi game.
- Trang chủ: banner, danh sách game, tin tức, sự kiện. Trang chi tiết game kèm link tải.
- Nạp xu web qua **SePay** (QR chuyển khoản, tự cộng xu qua webhook).
- Đổi xu: chọn game → chọn server → chọn nhân vật → chọn gói quy đổi ra tiền tệ game (vàng/ngọc NRO, lượng/xu Avatar...).
- Giftcode: chọn game → server → nhân vật → nhập mã; icon vật phẩm hiện ngay dưới ô nhập; mỗi user nhập 1 lần, nhập xong code tự ẩn với user đó.
- Bảng xếp hạng: chọn game → chọn server → hiện BXH đọc trực tiếp từ DB game.

**Admin (`/admin`)**
- Quản lý game (mô tả, thumbnail, banner, link tải), server từng game (thông tin DB + nút kiểm tra kết nối).
- Quản lý gói nạp xu, gói quy đổi từng game, đơn nạp, lịch sử quy đổi.
- Quản lý người dùng (khoá, cộng/trừ xu, reset mật khẩu có đồng bộ game).
- Quản lý tin tức/sự kiện, banner, giftcode (kèm icon vật phẩm hiển thị).
- Cấu hình website + SePay.

## Yêu cầu

- PHP >= 8.0 (PDO MySQL, OpenSSL), MySQL/MariaDB, Apache (mod_rewrite) hoặc Nginx.
- DB của các game phải truy cập được từ máy chạy web (cùng máy hoặc mở port MySQL nội bộ).

## Cài đặt

```bash
# 1. Tạo DB cổng game
mysql -u root -p < database/schema.sql

# 2. Cấu hình
cp config.example.php config.php
# sửa config.php: thông tin DB + app_key (chuỗi ngẫu nhiên 32+ ký tự, KHÔNG đổi sau khi có user)

# 3. Trỏ document root vào thư mục public/ (Apache đã có sẵn .htaccess)
# Dev nhanh:
php -S 0.0.0.0:8080 -t public router.php
```

Đăng nhập admin mặc định: **admin / admin123** → đổi mật khẩu ngay.

### Nginx

```nginx
root /path/to/Gate_game/public;
index index.php;
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ \.php$ { include fastcgi_params; fastcgi_pass unix:/run/php/php8.2-fpm.sock; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; }
```

## Cấu hình SePay

1. Admin → **Cấu hình**: điền ngân hàng, số tài khoản, tên chủ TK, tiền tố nội dung CK (mặc định `GATE`), API key.
2. Trên [my.sepay.vn](https://my.sepay.vn) → Webhook: trỏ về `https://domain-cua-ban/api/sepay-webhook`, kiểu xác thực **API Key** với đúng key ở bước 1.
3. Người chơi chọn gói → chuyển khoản đúng nội dung `GATE<mã đơn>` → webhook tự cộng xu (log tại bảng `sepay_logs`).

## Kết nối DB game

Admin → **Server game** → thêm/sửa server với host/port/db/user/pass của DB game đó, bấm **Kiểm tra** để test kết nối + schema:

- **Ngọc Rồng**: DB dạng `team2026` (bảng `account`, `player`).
- **Avatar 2D**: DB dạng `avatar_2x` (bảng `users`, `players`).

Mỗi server là một DB riêng → mở thêm server chỉ cần thêm dòng mới.

Lưu ý khi quy đổi tiền tệ ghi trực tiếp vào nhân vật (ngọc/vàng NRO, lượng/xu Avatar): cổng **bắt buộc nhân vật offline** để server game không ghi đè dữ liệu khi lưu. Riêng ví VNĐ trong game (NRO `account.vnd`) cộng được cả khi online.

## Thêm game mới

1. Viết adapter mới trong `app/adapters/` implement `GameAdapter` (mẫu: `NroAdapter.php`, `AvatarAdapter.php`): kiểm tra schema, tạo tài khoản, danh sách nhân vật, cộng tiền tệ, BXH.
2. Đăng ký 1 dòng trong `AdapterRegistry::$map`.
3. Admin → Game → thêm game chọn adapter mới + thêm server.

## Lưu ý bảo mật

- Game NRO lưu mật khẩu **plaintext**, Avatar lưu **MD5** trong DB game — web phải giữ mật khẩu gốc (mã hoá AES bằng `app_key`) để đồng bộ tài khoản. Vì vậy: giữ `config.php` ngoài git (đã có `.gitignore`), đặt `app_key` mạnh và không thay đổi, hạn chế quyền truy cập DB.
- Đổi mật khẩu admin mặc định ngay sau khi cài.
- Nên chạy HTTPS toàn bộ site (webhook SePay yêu cầu HTTPS).

## Cấu trúc thư mục

```
config.example.php     # mẫu cấu hình -> copy thành config.php
database/schema.sql    # schema + dữ liệu mặc định
public/                # document root (index.php, assets, uploads)
app/
  bootstrap.php        # khởi động ứng dụng
  routes.php           # bảng định tuyến
  core/                # DB, Auth, GameDB, Settings, Csrf, Crypto, helpers
  adapters/            # tích hợp từng game (NRO, Avatar, ...)
  controllers/site/    # trang người chơi + API + webhook SePay
  controllers/admin/   # trang quản trị
  views/               # giao diện (layout, trang, admin)
```
