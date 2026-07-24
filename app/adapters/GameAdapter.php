<?php
/**
 * Interface tích hợp 1 game vào cổng.
 * Mỗi game mới chỉ cần viết 1 adapter implement interface này
 * rồi đăng ký trong AdapterRegistry.
 */
interface GameAdapter
{
    /** Tên hiển thị của adapter */
    public function label(): string;

    /** Danh sách loại tiền tệ game hỗ trợ quy đổi: key => nhãn hiển thị */
    public function currencies(): array;

    /** Kiểm tra DB game có đúng schema không. Trả về [ok(bool), message] */
    public function testConnection(PDO $db): array;

    /** Tài khoản đã tồn tại trong DB game? */
    public function accountExists(PDO $db, string $username): bool;

    /** Tạo tài khoản game nếu chưa có. Trả về [ok, message] */
    public function ensureAccount(PDO $db, string $username, string $plainPassword): array;


    /** Danh sách nhân vật của tài khoản: [{id, name, info}] */
    public function getCharacters(PDO $db, string $username): array;

    /** Nhân vật có đang online trong game không */
    public function isCharacterOnline(PDO $db, string $characterId): bool;

    /**
     * Cộng tiền tệ cho nhân vật. $addTongnap: cộng thêm vào tổng nạp trong game (VND).
     * Trả về [ok, message]
     */
    public function creditCurrency(PDO $db, string $characterId, string $currencyKey, int $amount, int $addTongnap = 0): array;

    /** Bảng xếp hạng: ['columns' => [nhãn cột...], 'rows' => [[giá trị...], ...]] */
    public function getRankings(PDO $db, int $limit = 50): array;

    /** Tên vật phẩm theo item id (để webshop hiển thị + đối chiếu). null nếu không có */
    public function getItemName(PDO $db, int $itemId): ?string;

    /**
     * Tìm/duyệt danh mục vật phẩm đọc trực tiếp từ DB game (đồng bộ webshop).
     * $q rỗng = liệt kê từ đầu. Trả về [['id'=>int,'name'=>string], ...]
     */
    public function searchItems(PDO $db, string $q = '', int $limit = 50): array;

    /**
     * Giao vật phẩm vào rương nhân vật (webshop). Bắt buộc nhân vật offline.
     * Trả về [ok, message]
     */
    public function giveItem(PDO $db, string $characterId, int $itemId, int $quantity): array;
}
