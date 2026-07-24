<?php
/**
 * Icon tiền tệ game — admin tự upload ảnh cho từng (game, currency_key).
 * Lưu trong settings key 'currency_icons' dạng JSON {gameId: {currency_key: url}}.
 */
class CurrencyIcon
{
    private static ?array $icons = null;

    private static function icons(): array
    {
        if (self::$icons === null) {
            $data = json_decode(Settings::get('currency_icons', '') ?: '[]', true);
            self::$icons = is_array($data) ? $data : [];
        }
        return self::$icons;
    }

    private static function fallback(): string
    {
        return url('/assets/currency/default.png');
    }

    /** URL icon đã upload cho 1 loại tiền tệ (fallback ảnh mặc định nếu chưa có) */
    public static function url(int $gameId, string $key): string
    {
        $u = self::icons()[$gameId][$key] ?? '';
        return $u !== '' ? url($u) : self::fallback();
    }

    /** URL thô đã lưu (chưa qua url()), '' nếu chưa upload */
    public static function raw(int $gameId, string $key): string
    {
        return (string)(self::icons()[$gameId][$key] ?? '');
    }

    public static function set(int $gameId, string $key, string $url): void
    {
        $icons = self::icons();
        if ($url === '') {
            unset($icons[$gameId][$key]);
        } else {
            $icons[$gameId][$key] = $url;
        }
        self::$icons = $icons;
        Settings::set('currency_icons', json_encode($icons, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** Map {gameId: {currency_key: iconUrl}} để render (theo currencies của adapter) */
    public static function mapFor(array $games): array
    {
        $map = [];
        foreach ($games as $g) {
            $gid = (int)$g['id'];
            try {
                $currencies = AdapterRegistry::forGame($g['adapter'])->currencies();
            } catch (Throwable $e) {
                $currencies = [];
            }
            foreach ($currencies as $key => $label) {
                $map[$gid][$key] = self::url($gid, $key);
            }
        }
        return $map;
    }
}
