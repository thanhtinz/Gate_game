<?php
/**
 * Icon tiền tệ game — lấy theo item trong DB game.
 *
 * Cơ chế:
 *  - Admin gán mỗi (game, currency_key) với 1 item_id trong bảng item của game.
 *  - Web đọc icon_id của item đó từ DB game (adapter->getItemIcon).
 *  - Ảnh icon lấy từ bộ icon đã export/host: {icon_base}{icon_id}.png
 *    (mặc định /assets/game-icons/{adapter}/, có thể trỏ CDN riêng trong admin).
 *
 * Lưu cấu hình trong settings:
 *  - currency_items : JSON {gameId: {currency_key: itemId}}
 *  - game_icon_base : JSON {gameId: baseUrl}
 */
class CurrencyIcon
{
    private static ?array $items = null;
    private static ?array $bases = null;

    private static function items(): array
    {
        if (self::$items === null) {
            $data = json_decode(Settings::get('currency_items', '') ?: '[]', true);
            self::$items = is_array($data) ? $data : [];
        }
        return self::$items;
    }

    private static function bases(): array
    {
        if (self::$bases === null) {
            $data = json_decode(Settings::get('game_icon_base', '') ?: '[]', true);
            self::$bases = is_array($data) ? $data : [];
        }
        return self::$bases;
    }

    public static function itemId(int $gameId, string $key): ?int
    {
        $v = self::items()[$gameId][$key] ?? null;
        return $v !== null && $v !== '' ? (int)$v : null;
    }

    public static function iconBase(int $gameId, string $adapter): string
    {
        $b = self::bases()[$gameId] ?? '';
        if ($b !== '') {
            return rtrim($b, '/') . '/';
        }
        return '/assets/game-icons/' . preg_replace('/[^a-z0-9_]/', '', $adapter) . '/';
    }

    public static function setItem(int $gameId, string $key, string $itemId): void
    {
        $items = self::items();
        if ($itemId === '') {
            unset($items[$gameId][$key]);
        } else {
            $items[$gameId][$key] = (int)$itemId;
        }
        self::$items = $items;
        Settings::set('currency_items', json_encode($items, JSON_UNESCAPED_UNICODE));
    }

    public static function setBase(int $gameId, string $url): void
    {
        $bases = self::bases();
        if ($url === '') {
            unset($bases[$gameId]);
        } else {
            $bases[$gameId] = $url;
        }
        self::$bases = $bases;
        Settings::set('game_icon_base', json_encode($bases, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private static function fallback(): string
    {
        return '/assets/currency/default.png';
    }

    /**
     * URL icon của 1 loại tiền tệ. Cần PDO tới DB game để đọc icon_id.
     * Nếu chưa gán item hoặc không đọc được -> ảnh fallback trung tính.
     */
    public static function url(int $gameId, string $adapter, string $key, ?PDO $gameDb): string
    {
        $itemId = self::itemId($gameId, $key);
        if ($itemId === null || !$gameDb) {
            return self::fallback();
        }
        try {
            $info = AdapterRegistry::forGame($adapter)->getItemIcon($gameDb, $itemId);
        } catch (Throwable $e) {
            $info = null;
        }
        if (!$info) {
            return self::fallback();
        }
        return self::iconBase($gameId, $adapter) . $info['icon_id'] . '.png';
    }

    /**
     * Map {gameId: {currency_key: iconUrl}} — kết nối server đầu tiên của mỗi game để đọc icon_id.
     * $games: mảng game có id, adapter.
     */
    public static function mapFor(array $games): array
    {
        $map = [];
        foreach ($games as $g) {
            $gid = (int)$g['id'];
            $adapter = $g['adapter'];
            try {
                $currencies = AdapterRegistry::forGame($adapter)->currencies();
            } catch (Throwable $e) {
                $currencies = [];
            }
            // Server đầu tiên đang hoạt động của game (icon_id giống nhau giữa các server)
            $server = DB::one(
                'SELECT * FROM game_servers WHERE game_id = ? AND status = 1 ORDER BY sort_order, id LIMIT 1',
                [$gid]
            );
            $gameDb = null;
            if ($server) {
                try {
                    $gameDb = GameDB::forServer($server);
                } catch (Throwable $e) {
                    $gameDb = null;
                }
            }
            foreach ($currencies as $key => $label) {
                $map[$gid][$key] = self::url($gid, $adapter, $key, $gameDb);
            }
        }
        return $map;
    }

    /** Preview icon cho admin: item name + icon url theo item hiện gán */
    public static function adminInfo(int $gameId, string $adapter, string $key, ?PDO $gameDb): array
    {
        $itemId = self::itemId($gameId, $key);
        $out = ['item_id' => $itemId, 'name' => '', 'url' => self::fallback()];
        if ($itemId === null || !$gameDb) {
            return $out;
        }
        try {
            $info = AdapterRegistry::forGame($adapter)->getItemIcon($gameDb, $itemId);
        } catch (Throwable $e) {
            $info = null;
        }
        if ($info) {
            $out['name'] = $info['name'];
            $out['url'] = self::iconBase($gameId, $adapter) . $info['icon_id'] . '.png';
        }
        return $out;
    }
}
