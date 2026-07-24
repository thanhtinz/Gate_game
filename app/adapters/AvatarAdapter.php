<?php
/**
 * Adapter Avatar 2D (Lttt).
 * Schema game: bảng `users` (username, password = MD5, vnd, tongnap, active)
 * và bảng `players` (user_id, xu, luong, luong_khoa, xeng, is_online, level_main).
 * Mỗi tài khoản có đúng 1 nhân vật (1 dòng players).
 */
class AvatarAdapter implements GameAdapter
{
    public function label(): string
    {
        return 'Avatar 2D';
    }

    public function currencies(): array
    {
        return [
            'luong' => 'Lượng',
            'xu' => 'Xu',
            'xeng' => 'Xèng',
        ];
    }

    public function testConnection(PDO $db): array
    {
        try {
            $db->query('SELECT id FROM users LIMIT 1');
            $db->query('SELECT id FROM players LIMIT 1');
            $count = $db->query('SELECT COUNT(*) c FROM users')->fetch(PDO::FETCH_ASSOC);
            return [true, 'OK — ' . number_vn($count['c']) . ' tài khoản trong DB game'];
        } catch (Throwable $e) {
            return [false, 'Sai schema Avatar: ' . $e->getMessage()];
        }
    }

    public function accountExists(PDO $db, string $username): bool
    {
        $st = $db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $st->execute([$username]);
        return (bool)$st->fetch();
    }

    public function ensureAccount(PDO $db, string $username, string $plainPassword): array
    {
        try {
            if ($this->accountExists($db, $username)) {
                return [true, 'Tài khoản đã tồn tại'];
            }
            $db->beginTransaction();
            $st = $db->prepare("INSERT INTO users (username, password, gmail, vnd, tongnap, active, timeCreate) VALUES (?, ?, '', 0, 0, 1, NOW())");
            $st->execute([$username, md5($plainPassword)]);
            $uid = (int)$db->lastInsertId();
            // Tạo sẵn nhân vật (1 tài khoản = 1 nhân vật)
            $db->prepare('INSERT INTO players (user_id, scores) VALUES (?, 0)')->execute([$uid]);
            $db->commit();
            return [true, 'Đã tạo tài khoản game'];
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return [false, 'Không tạo được tài khoản: ' . $e->getMessage()];
        }
    }


    public function getCharacters(PDO $db, string $username): array
    {
        $st = $db->prepare(
            'SELECT p.id, u.username, p.level_main FROM players p
             JOIN users u ON u.id = p.user_id WHERE u.username = ? LIMIT 1'
        );
        $st->execute([$username]);
        $out = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[] = [
                'id' => (string)$row['id'],
                'name' => $row['username'],
                'info' => $row['level_main'] !== null ? 'Level ' . $row['level_main'] : '',
            ];
        }
        return $out;
    }

    public function isCharacterOnline(PDO $db, string $characterId): bool
    {
        $st = $db->prepare('SELECT is_online FROM players WHERE id = ? LIMIT 1');
        $st->execute([$characterId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['is_online'] === 1;
    }

    public function creditCurrency(PDO $db, string $characterId, string $currencyKey, int $amount, int $addTongnap = 0): array
    {
        $columns = ['xu' => 'xu', 'luong' => 'luong', 'xeng' => 'xeng'];
        if (!isset($columns[$currencyKey])) {
            return [false, 'Loại tiền tệ không hỗ trợ: ' . $currencyKey];
        }
        try {
            $db->beginTransaction();
            $st = $db->prepare('SELECT id, user_id, is_online FROM players WHERE id = ? LIMIT 1 FOR UPDATE');
            $st->execute([$characterId]);
            $p = $st->fetch(PDO::FETCH_ASSOC);
            if (!$p) {
                $db->rollBack();
                return [false, 'Không tìm thấy nhân vật'];
            }
            // Server Avatar ghi đè players khi lưu dữ liệu -> bắt buộc offline
            if ((int)$p['is_online'] === 1) {
                $db->rollBack();
                return [false, 'Nhân vật đang online. Vui lòng thoát game rồi quy đổi lại.'];
            }
            $col = $columns[$currencyKey];
            $db->prepare("UPDATE players SET `$col` = `$col` + ? WHERE id = ?")->execute([$amount, $characterId]);
            if ($addTongnap > 0) {
                // tongnap mở các mốc thưởng nạp trong game
                $db->prepare('UPDATE users SET tongnap = tongnap + ?, vnd = vnd + ? WHERE id = ?')
                   ->execute([$addTongnap, $addTongnap, $p['user_id']]);
            }
            $db->commit();
            return [true, 'Đã cộng ' . number_vn($amount) . ' ' . ($this->currencies()[$currencyKey] ?? $currencyKey)];
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return [false, 'Lỗi quy đổi: ' . $e->getMessage()];
        }
    }

    public function getRankings(PDO $db, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $sql = "SELECT u.username, p.level_main, p.exp_main, p.scores
                FROM players p JOIN users u ON u.id = p.user_id
                ORDER BY COALESCE(p.level_main, 0) DESC, p.exp_main DESC
                LIMIT $limit";
        $rows = [];
        foreach ($db->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $rows[] = [$r['username'], (string)(int)$r['level_main'], number_vn($r['exp_main']), number_vn($r['scores'])];
        }
        return ['columns' => ['Nhân vật', 'Level', 'EXP', 'Điểm'], 'rows' => $rows];
    }

    public function getItemIcon(PDO $db, int $itemId): ?array
    {
        try {
            $st = $db->prepare('SELECT icon, name FROM items WHERE id = ? LIMIT 1');
            $st->execute([$itemId]);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            return $r ? ['icon_id' => (int)$r['icon'], 'name' => (string)$r['name']] : null;
        } catch (Throwable $e) {
            return null;
        }
    }
}
