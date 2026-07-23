# 🎮 Gate Game — Cổng game đa tựa

Cổng game trung tâm cho nhiều game (hiện có **Ngọc Rồng Online** và **Avatar 2D**), thay cho việc mỗi game một website riêng.

## Tính năng

**Người chơi**
- **Auth tập trung**: tài khoản chỉ nằm ở DB cổng (bcrypt). Game server xác thực đăng nhập qua API `/api/game-auth/verify` của cổng — không còn lưu/đồng bộ mật khẩu trong DB từng game. Đổi mật khẩu trên web có hiệu lực ngay với mọi game.
- Trang chủ: banner, danh sách game, tin tức, sự kiện. Trang chi tiết game kèm link tải.
- Nạp xu web qua **SePay** (QR chuyển khoản, tự cộng xu qua webhook).
- Đổi xu: chọn game → chọn server → chọn nhân vật → chọn gói quy đổi ra tiền tệ game (vàng/ngọc NRO, lượng/xu Avatar...).
- Giftcode: chọn game → server → nhân vật → nhập mã; icon vật phẩm hiện ngay dưới ô nhập; mỗi user nhập 1 lần, nhập xong code tự ẩn với user đó.
- Bảng xếp hạng: chọn game → chọn server → hiện BXH đọc trực tiếp từ DB game.

**Admin (`/admin`)**
- Quản lý game (mô tả, thumbnail, banner, link tải), server từng game (thông tin DB + nút kiểm tra kết nối).
- Quản lý gói nạp xu, gói quy đổi từng game, đơn nạp, lịch sử quy đổi.
- Quản lý người dùng (khoá, cộng/trừ xu, reset mật khẩu).
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
# sửa config.php: thông tin DB kết nối cổng game

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

## Auth tập trung cho game server

Tài khoản là của **cổng**; DB game chỉ chứa dữ liệu nhân vật. Khi người chơi đăng nhập game:

1. Game server gọi `POST /api/game-auth/verify` (header `X-Auth-Key`) tới cổng.
2. Cổng trả `ok` → game server tự tạo/cập nhật dòng account trong DB game rồi cho vào game.
3. `wrong_password` / `locked` → từ chối ngay.
4. `not_found` (tài khoản cũ chưa có trên cổng) hoặc cổng lỗi/tắt → **fallback** kiểm tra DB game như cũ, người chơi cũ không bị ảnh hưởng.

Cách bật (2 game đã được patch sẵn trong repo `Nro` và `Lttt`):

1. Admin cổng → **Cấu hình** → đặt `Central Auth Key` (chuỗi ngẫu nhiên 32+ ký tự).
2. Sửa config của từng game server rồi khởi động lại:
   - NRO: `server/Config.properties` — `auth.enabled=true`, `auth.url=https://domain/api/game-auth/verify`, `auth.key=<key>`
   - Avatar: `server/config.properties` — 3 dòng tương tự.

Đăng ký tài khoản mới chỉ thực hiện trên web. Web chặn đăng ký username trùng với tài khoản in-game cũ (tránh chiếm tài khoản fallback của người khác).

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

- Mật khẩu chỉ lưu **bcrypt** ở DB cổng. (DB game vẫn còn mật khẩu của các tài khoản in-game cũ theo format cũ — chỉ dùng cho chế độ fallback.)
- Giữ `config.php` ngoài git (đã có `.gitignore`); đặt `Central Auth Key` và API key SePay đủ mạnh.
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
  core/                # DB, Auth, GameDB, Settings, Csrf, helpers
  adapters/            # tích hợp từng game (NRO, Avatar, ...)
  controllers/site/    # trang người chơi + API + webhook SePay
  controllers/admin/   # trang quản trị
  views/               # giao diện (layout, trang, admin)
```
