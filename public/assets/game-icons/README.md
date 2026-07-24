# Bộ icon tiền tệ / vật phẩm của game

Đặt icon đã export từ client game vào đây theo cấu trúc:

    game-icons/<adapter>/<icon_id>.png

- `<adapter>`: nro, avatar, ... (theo cột adapter của game)
- `<icon_id>`: mã icon của item trong DB game (item_template.icon_id của NRO,
  items.icon của Avatar). Xem/gán trong Admin → Gói quy đổi → Icon tiền tệ game.

Icon của game nằm trong texture Unity (NRO) hoặc file .av (Avatar) nên phải
export một lần rồi bỏ file PNG vào đây. Chưa có file thì trang Đổi xu hiện
ảnh mặc định /assets/currency/default.png.

Có thể trỏ sang CDN riêng qua ô "Đường dẫn bộ icon" trong admin thay vì để ở đây.
