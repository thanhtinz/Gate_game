<?php
/** Quản lý người dùng */

function admin_users(): void
{
    $q = trim((string)get('q'));
    $page = max(1, (int)get('page', 1));
    $perPage = 50;

    $where = '';
    $params = [];
    if ($q !== '') {
        $where = 'WHERE username LIKE ?';
        $params[] = '%' . $q . '%';
    }

    $total = (int)(DB::one("SELECT COUNT(*) c FROM users $where", $params)['c'] ?? 0);
    $pages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $pages);
    $offset = ($page - 1) * $perPage;

    $users = DB::all(
        "SELECT id, username, email, xu, tong_nap, role, status, created_at
         FROM users $where ORDER BY id DESC LIMIT $perPage OFFSET $offset",
        $params
    );

    admin_view('users', [
        'title' => 'Người dùng',
        'users' => $users,
        'q' => $q,
        'page' => $page,
        'pages' => $pages,
        'total' => $total,
    ]);
}

function admin_users_save(): void
{
    Csrf::check();
    $action = post('action');
    $id = (int)post('id');
    $me = Auth::user();

    $back = '/admin/users';
    $qs = [];
    if (post('q') !== '') { $qs[] = 'q=' . urlencode((string)post('q')); }
    if ((int)post('page', 1) > 1) { $qs[] = 'page=' . (int)post('page'); }
    if ($qs) { $back .= '?' . implode('&', $qs); }

    $user = DB::one('SELECT * FROM users WHERE id = ?', [$id]);
    if (!$user) {
        flash_set('error', 'Người dùng không tồn tại.');
        redirect($back);
    }

    switch ($action) {
        case 'toggle_status':
            if ($me && (int)$me['id'] === $id) {
                flash_set('error', 'Không thể tự khoá tài khoản của chính mình.');
                break;
            }
            $new = (int)$user['status'] === 1 ? 0 : 1;
            DB::update('users', ['status' => $new], 'id = ?', [$id]);
            flash_set('success', $new === 1 ? "Đã mở khoá tài khoản {$user['username']}." : "Đã khoá tài khoản {$user['username']}.");
            break;

        case 'toggle_role':
            if ($me && (int)$me['id'] === $id) {
                flash_set('error', 'Không thể tự thay đổi quyền của chính mình.');
                break;
            }
            $new = (int)$user['role'] === 1 ? 0 : 1;
            DB::update('users', ['role' => $new], 'id = ?', [$id]);
            flash_set('success', $new === 1 ? "Đã cấp quyền quản trị cho {$user['username']}." : "Đã gỡ quyền quản trị của {$user['username']}.");
            break;

        case 'adjust_xu':
            $amount = (int)post('amount');
            if ($amount === 0) {
                flash_set('error', 'Số xu điều chỉnh phải khác 0 (âm để trừ, dương để cộng).');
                break;
            }
            $newXu = (int)$user['xu'] + $amount;
            if ($newXu < 0) {
                flash_set('error', 'Không thể trừ quá số dư hiện có (' . number_vn($user['xu']) . ' xu).');
                break;
            }
            DB::update('users', ['xu' => $newXu], 'id = ?', [$id]);
            flash_set('success', ($amount > 0 ? 'Đã cộng ' : 'Đã trừ ') . number_vn(abs($amount)) . " xu cho {$user['username']}. Số dư mới: " . number_vn($newXu) . ' xu.');
            break;

        case 'reset_password':
            $newPass = (string)post('new_password');
            [$ok, $msg, $warnings] = Auth::changePassword($id, $newPass);
            if ($ok) {
                $extra = $warnings ? ' Cảnh báo: ' . implode(' | ', $warnings) : '';
                flash_set('success', "Đã đặt lại mật khẩu cho {$user['username']}." . $extra);
            } else {
                flash_set('error', $msg);
            }
            break;

        default:
            flash_set('error', 'Hành động không hợp lệ.');
    }

    redirect($back);
}
