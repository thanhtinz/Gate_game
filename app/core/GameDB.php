<?php
/** Kết nối tới DB của từng server game (cache theo server_id) */
class GameDB
{
    /** @var PDO[] */
    private static array $pool = [];

    /** Lấy PDO tới DB của 1 server game */
    public static function forServer(array $server): PDO
    {
        $key = $server['id'] ?? ($server['db_host'] . ':' . $server['db_name']);
        if (!isset(self::$pool[$key])) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $server['db_host'],
                (int)$server['db_port'],
                $server['db_name']
            );
            self::$pool[$key] = new PDO($dsn, $server['db_user'], $server['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 5,
            ]);
        }
        return self::$pool[$key];
    }

    /** Thử kết nối, trả về [ok, message] */
    public static function test(array $server): array
    {
        try {
            $pdo = self::forServer($server);
            $pdo->query('SELECT 1');
            return [true, 'Kết nối thành công'];
        } catch (Throwable $e) {
            return [false, 'Lỗi kết nối: ' . $e->getMessage()];
        }
    }
}
