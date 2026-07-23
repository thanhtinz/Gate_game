<?php
/**
 * Đăng ký adapter. Thêm game mới: viết adapter mới + thêm 1 dòng vào $map.
 */
class AdapterRegistry
{
    private static array $map = [
        'nro' => NroAdapter::class,
        'avatar' => AvatarAdapter::class,
    ];

    private static array $instances = [];

    public static function available(): array
    {
        $out = [];
        foreach (self::$map as $key => $class) {
            $out[$key] = self::forGame($key)->label();
        }
        return $out;
    }

    public static function forGame(string $adapterKey): GameAdapter
    {
        if (!isset(self::$map[$adapterKey])) {
            throw new RuntimeException("Adapter không tồn tại: $adapterKey");
        }
        if (!isset(self::$instances[$adapterKey])) {
            self::$instances[$adapterKey] = new self::$map[$adapterKey]();
        }
        return self::$instances[$adapterKey];
    }
}
