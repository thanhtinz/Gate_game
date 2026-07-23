<?php

/**
 * Webhook SePay: cấu hình trong SePay dashboard trỏ về /api/sepay-webhook
 * kèm header "Authorization: Apikey <sepay_api_key>".
 */
function sepay_webhook(): void
{
    $raw = file_get_contents('php://input') ?: '';

    // Xác thực API key
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $expected = Settings::get('sepay_api_key');
    $given = trim(preg_replace('/^Apikey\s+/i', '', $auth));
    if ($expected === '' || !hash_equals($expected, $given)) {
        sepay_log($raw, null, 'unauthorized');
        json_out(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        sepay_log($raw, null, 'invalid json');
        json_out(['success' => false, 'message' => 'Invalid payload'], 400);
    }

    // Chỉ xử lý tiền vào
    $type = $data['transferType'] ?? '';
    if ($type !== 'in') {
        sepay_log($raw, null, 'skip: transferType=' . $type);
        json_out(['success' => true, 'message' => 'Skipped']);
    }

    $amount = (int)($data['transferAmount'] ?? 0);
    $txId = (string)($data['id'] ?? '');
    $content = (string)(($data['content'] ?? '') . ' ' . ($data['code'] ?? '') . ' ' . ($data['description'] ?? ''));

    // Tìm mã đơn trong nội dung chuyển khoản
    $prefix = strtoupper(Settings::get('sepay_prefix', 'GATE'));
    if (!preg_match('/' . preg_quote($prefix, '/') . '(\d+)/i', $content, $m)) {
        sepay_log($raw, null, 'no order code in content');
        json_out(['success' => true, 'message' => 'No order code']);
    }
    $code = $prefix . $m[1];

    $pdo = DB::pdo();
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare('SELECT * FROM orders WHERE code = ? LIMIT 1 FOR UPDATE');
        $st->execute([$code]);
        $order = $st->fetch();
        if (!$order) {
            $pdo->rollBack();
            sepay_log($raw, $code, 'order not found');
            json_out(['success' => true, 'message' => 'Order not found']);
        }
        if ($order['status'] !== 'pending') {
            $pdo->rollBack();
            sepay_log($raw, $code, 'order already ' . $order['status']);
            json_out(['success' => true, 'message' => 'Already processed']);
        }
        if ($amount < (int)$order['amount_vnd']) {
            $pdo->rollBack();
            sepay_log($raw, $code, "amount $amount < {$order['amount_vnd']}");
            json_out(['success' => true, 'message' => 'Amount mismatch']);
        }

        $totalXu = (int)$order['xu'] + (int)$order['bonus_xu'];
        $pdo->prepare("UPDATE orders SET status = 'completed', sepay_tx_id = ?, completed_at = NOW() WHERE id = ?")
            ->execute([$txId, $order['id']]);
        $pdo->prepare('UPDATE users SET xu = xu + ?, tong_nap = tong_nap + ? WHERE id = ?')
            ->execute([$totalXu, (int)$order['amount_vnd'], $order['user_id']]);
        $pdo->commit();
        sepay_log($raw, $code, 'completed +' . $totalXu . ' xu');
        json_out(['success' => true, 'message' => 'OK']);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sepay_log($raw, $code, 'error: ' . $e->getMessage());
        json_out(['success' => false, 'message' => 'Server error'], 500);
    }
}

function sepay_log(string $raw, ?string $code, string $result): void
{
    try {
        DB::insert('sepay_logs', [
            'raw' => mb_substr($raw, 0, 60000),
            'order_code' => $code,
            'result' => mb_substr($result, 0, 100),
        ]);
    } catch (Throwable $e) {
        // log lỗi không được phép làm hỏng webhook
    }
}
