<?php
/**
 * Adapter Ngọc Rồng Online.
 * Schema game: bảng `account` (username, password PLAINTEXT, vnd, tongnap, last_time_login/logout)
 * và bảng `player` (account_id, name, data_inventory JSON: [0]=vàng, [1]=ngọc, [2]=ruby).
 */
class NroAdapter implements GameAdapter
{
    public function label(): string
    {
        return 'Ngọc Rồng Online';
    }

    public function currencies(): array
    {
        return [
            'vnd' => 'VNĐ (ví trong game)',
            'gold' => 'Vàng',
            'gem' => 'Ngọc',
            'ruby' => 'Hồng ngọc',
        ];
    }

    public function testConnection(PDO $db): array
    {
        try {
            $db->query('SELECT id FROM account LIMIT 1');
            $db->query('SELECT id FROM player LIMIT 1');
            $count = $db->query('SELECT COUNT(*) c FROM account')->fetch(PDO::FETCH_ASSOC);
            return [true, 'OK — ' . number_vn($count['c']) . ' tài khoản trong DB game'];
        } catch (Throwable $e) {
            return [false, 'Sai schema NRO: ' . $e->getMessage()];
        }
    }

    public function accountExists(PDO $db, string $username): bool
    {
        $st = $db->prepare('SELECT id FROM account WHERE username = ? LIMIT 1');
        $st->execute([$username]);
        return (bool)$st->fetch();
    }

    public function ensureAccount(PDO $db, string $username, string $plainPassword): array
    {
        try {
            if ($this->accountExists($db, $username)) {
                return [true, 'Tài khoản đã tồn tại'];
            }
            $st = $db->prepare("INSERT INTO account (username, password, email, token, xsrf_token, newpass) VALUES (?, ?, '', '', '', '')");
            $st->execute([$username, $plainPassword]);
            return [true, 'Đã tạo tài khoản game'];
        } catch (Throwable $e) {
            return [false, 'Không tạo được tài khoản: ' . $e->getMessage()];
        }
    }


    public function getCharacters(PDO $db, string $username): array
    {
        $st = $db->prepare(
            'SELECT p.id, p.name, p.head FROM player p
             JOIN account a ON a.id = p.account_id
             WHERE a.username = ? ORDER BY p.id'
        );
        $st->execute([$username]);
        $out = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[] = ['id' => (string)$row['id'], 'name' => $row['name'], 'info' => ''];
        }
        return $out;
    }

    public function isCharacterOnline(PDO $db, string $characterId): bool
    {
        // NRO không có cờ is_online trên player; xét theo account:
        // online nếu last_time_login mới hơn last_time_logout
        $st = $db->prepare(
            'SELECT a.last_time_login, a.last_time_logout FROM account a
             JOIN player p ON p.account_id = a.id WHERE p.id = ? LIMIT 1'
        );
        $st->execute([$characterId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['last_time_login'])) {
            return false;
        }
        if (empty($row['last_time_logout'])) {
            return true;
        }
        return strtotime($row['last_time_login']) > strtotime($row['last_time_logout']);
    }

    public function creditCurrency(PDO $db, string $characterId, string $currencyKey, int $amount, int $addTongnap = 0): array
    {
        try {
            $db->beginTransaction();
            $st = $db->prepare(
                'SELECT p.id, p.account_id, p.data_inventory, a.username FROM player p
                 JOIN account a ON a.id = p.account_id WHERE p.id = ? LIMIT 1 FOR UPDATE'
            );
            $st->execute([$characterId]);
            $p = $st->fetch(PDO::FETCH_ASSOC);
            if (!$p) {
                $db->rollBack();
                return [false, 'Không tìm thấy nhân vật'];
            }

            if ($currencyKey === 'vnd') {
                // Ví VND trong game: an toàn kể cả khi đang online
                $db->prepare('UPDATE account SET vnd = vnd + ?, tongnap = tongnap + ? WHERE id = ?')
                   ->execute([$amount, max($addTongnap, 0), $p['account_id']]);
                $db->commit();
                return [true, 'Đã cộng ' . number_vn($amount) . ' VNĐ vào ví game'];
            }

            // Vàng/ngọc/ruby ghi trực tiếp vào data_inventory:
            // phải offline nếu không server game sẽ ghi đè khi lưu dữ liệu
            if ($this->isCharacterOnline($db, $characterId)) {
                $db->rollBack();
                return [false, 'Nhân vật đang online. Vui lòng thoát game rồi quy đổi lại.'];
            }

            $indexMap = ['gold' => 0, 'gem' => 1, 'ruby' => 2];
            if (!isset($indexMap[$currencyKey])) {
                $db->rollBack();
                return [false, 'Loại tiền tệ không hỗ trợ: ' . $currencyKey];
            }
            $inv = json_decode((string)$p['data_inventory'], true);
            if (!is_array($inv)) {
                $db->rollBack();
                return [false, 'Dữ liệu túi đồ nhân vật không hợp lệ'];
            }
            $idx = $indexMap[$currencyKey];
            $inv[$idx] = (int)($inv[$idx] ?? 0) + $amount;
            $db->prepare('UPDATE player SET data_inventory = ? WHERE id = ?')
               ->execute([json_encode($inv), $characterId]);
            if ($addTongnap > 0) {
                $db->prepare('UPDATE account SET tongnap = tongnap + ? WHERE id = ?')
                   ->execute([$addTongnap, $p['account_id']]);
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
        $sql = "SELECT name,
                    CAST(JSON_UNQUOTE(JSON_EXTRACT(data_point, '$[1]')) AS SIGNED) AS sm,
                    COALESCE(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(pet, '$[1]')), ',', 2), ',', -1) AS SIGNED), 0) AS detu_sm
                FROM player
                ORDER BY (CAST(JSON_UNQUOTE(JSON_EXTRACT(data_point, '$[1]')) AS SIGNED)
                    + COALESCE(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(pet, '$[1]')), ',', 2), ',', -1) AS SIGNED), 0)) DESC
                LIMIT $limit";
        $rows = [];
        foreach ($db->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $total = (int)$r['sm'] + (int)$r['detu_sm'];
            $rows[] = [$r['name'], number_vn($r['sm']), number_vn($r['detu_sm']), number_vn($total)];
        }
        return ['columns' => ['Nhân vật', 'Sức mạnh', 'Đệ tử', 'Tổng'], 'rows' => $rows];
    }

}
