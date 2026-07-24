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

## Đăng ký / đổi mật khẩu ngay trong game

Game server gọi API cổng hộ người chơi (đã patch sẵn trong repo `Nro` và `Lttt`):

- `POST /api/game-auth/register` (username, password, email) — đăng ký từ form trong game, cổng tạo tài khoản + gửi mail xác minh, người chơi xác minh xong quay lại game đăng nhập.
  - Avatar: form đăng ký in-game có sẵn ô email (cmd `-25`).
  - NRO: RegisterScreen của client Unity đã đổi ô "Mã giới thiệu" thành ô Email (cmd `42`) — cần build lại client Unity.
- `POST /api/game-auth/change-password` (username, old_password, new_password) — đổi mật khẩu trong game (Avatar cmd `-62`), hiệu lực cho web + mọi game; tài khoản cũ chưa lên cổng thì tự fallback đổi trong DB game như trước.

## Đăng nhập Google

- Admin → **Cấu hình** → mục *Đăng nhập Google*: bật + điền Client ID/Secret (Google Cloud Console → Credentials → OAuth Client, loại Web; redirect URI hiển thị sẵn trong admin).
- Người mới đăng nhập Google lần đầu sẽ chọn **username + mật khẩu game** ở bước hoàn tất (game đăng nhập bằng username/mật khẩu). Email Google được tính là đã xác minh.
- Đăng nhập Google với email trùng tài khoản có sẵn sẽ tự liên kết.

## Xác minh email (thay cơ chế kích hoạt tài khoản của game)

- Đăng ký bắt buộc nhập email → cổng gửi mail chứa link kích hoạt (hiệu lực 24h, gửi lại tối đa 1 lần/phút).
- Admin → **Cấu hình** → mục *Email/SMTP*: điền SMTP (Gmail App Password, Zoho, SendGrid...), có nút gửi mail test; bật **"Bắt buộc xác minh email"** khi muốn áp dụng.
- Khi bật bắt buộc: tài khoản chưa xác minh **không đăng nhập được game** (API trả `unverified`, game hiện thông báo về web xác minh) và không nạp/đổi xu/nhập giftcode được.
- Admin → Người dùng: thấy trạng thái ✔ và có nút **Duyệt mail** xác minh thủ công.
- Bản cài cũ chạy: `mysql gate_portal < database/migrations/2026-07-email-verify.sql` (user có sẵn được coi là đã xác minh).

## Kết nối DB game

Admin → **Server game** → thêm/sửa server với host/port/db/user/pass của DB game đó, bấm **Kiểm tra** để test kết nối + schema:

- **Ngọc Rồng**: DB dạng `team2026` (bảng `account`, `player`).
- **Avatar 2D**: DB dạng `avatar_2x` (bảng `users`, `players`).

Mỗi server là một DB riêng → mở thêm server chỉ cần thêm dòng mới.

Lưu ý khi quy đổi tiền tệ ghi trực tiếp vào nhân vật (ngọc/vàng NRO, lượng/xu Avatar): cổng **bắt buộc nhân vật offline** để server game không ghi đè dữ liệu khi lưu. Riêng ví VNĐ trong game (NRO `account.vnd`) cộng được cả khi online.

### Icon tiền tệ (admin tự upload)

Ở trang Đổi xu, mỗi gói hiện icon tiền tệ game. Admin → **Gói quy đổi** → mục *Icon tiền tệ game*: mỗi loại tiền tệ (ngọc/vàng/lượng/xu...) có ô **upload ảnh** (jpg/png/webp/gif) — upload icon bạn muốn hiện. Có nút xoá để quay lại ảnh mặc định. Chưa upload thì dùng ảnh mặc định trung tính.

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
