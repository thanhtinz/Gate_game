<?php
/** Cấu hình website lưu trong bảng settings */
class Settings
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (DB::all('SELECT k, v FROM settings') as $row) {
                self::$cache[$row['k']] = $row['v'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, $default = ''): string
    {
        return self::all()[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        DB::query('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$key, $value]);
        self::$cache[$key] = $value;
    }
}
