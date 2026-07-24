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

    public function getItemName(PDO $db, int $itemId): ?string
    {
        try {
            $st = $db->prepare('SELECT name FROM items WHERE id = ? LIMIT 1');
            $st->execute([$itemId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            return (string)$row['name'];
        } catch (Throwable $e) {
            return null;
        }
    }

    public function searchItems(PDO $db, string $q = '', int $limit = 50): array
    {
        $limit = max(1, min(100, $limit));
        try {
            $q = trim($q);
            if ($q === '') {
                $st = $db->query("SELECT id, name FROM items ORDER BY id LIMIT $limit");
            } elseif (ctype_digit($q)) {
                $st = $db->prepare("SELECT id, name FROM items WHERE id = ? OR name LIKE ? ORDER BY id LIMIT $limit");
                $st->execute([(int)$q, '%' . $q . '%']);
            } else {
                $st = $db->prepare("SELECT id, name FROM items WHERE name LIKE ? ORDER BY id LIMIT $limit");
                $st->execute(['%' . $q . '%']);
            }
            $out = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $out[] = ['id' => (int)$r['id'], 'name' => (string)$r['name']];
            }
            return $out;
        } catch (Throwable $e) {
            return [];
        }
    }

    public function giveItem(PDO $db, string $characterId, int $itemId, int $quantity): array
    {
        if ($quantity < 1) {
            return [false, 'Số lượng không hợp lệ'];
        }
        try {
            $db->beginTransaction();
            $st = $db->prepare('SELECT id, is_online, chests FROM players WHERE id = ? LIMIT 1 FOR UPDATE');
            $st->execute([$characterId]);
            $p = $st->fetch(PDO::FETCH_ASSOC);
            if (!$p) {
                $db->rollBack();
                return [false, 'Không tìm thấy nhân vật'];
            }
            // Server Avatar ghi đè players khi lưu -> bắt buộc offline
            if ((int)$p['is_online'] === 1) {
                $db->rollBack();
                return [false, 'Nhân vật đang online. Vui lòng thoát game rồi nhận vật phẩm lại.'];
            }
            $chests = json_decode((string)$p['chests'], true);
            if (!is_array($chests)) {
                $db->rollBack();
                return [false, 'Dữ liệu rương nhân vật không hợp lệ'];
            }
            // Mỗi ô là object {expired, quantity, id}; ô trống quantity = 0
            $slot = -1;
            foreach ($chests as $i => $c) {
                if (is_array($c) && (int)($c['quantity'] ?? 0) === 0) {
                    $slot = $i;
                    break;
                }
            }
            if ($slot < 0) {
                $db->rollBack();
                return [false, 'Rương nhân vật đã đầy. Vui lòng dọn rương rồi nhận lại.'];
            }
            $chests[$slot] = ['expired' => -1, 'quantity' => $quantity, 'id' => $itemId];
            $db->prepare('UPDATE players SET chests = ? WHERE id = ?')
               ->execute([json_encode(array_values($chests)), $characterId]);
            $db->commit();
            return [true, 'Đã gửi vật phẩm vào rương nhân vật'];
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return [false, 'Lỗi giao vật phẩm: ' . $e->getMessage()];
        }
    }

}
