<?php
/** Helper dùng chung */

function config(string $key, $default = null)
{
    return $GLOBALS['config'][$key] ?? $default;
}

function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(config('base_path', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function json_out($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Render view trong layout người chơi */
function view(string $name, array $data = []): void
{
    extract($data);
    require APP_DIR . '/views/layout/header.php';
    require APP_DIR . '/views/' . $name . '.php';
    require APP_DIR . '/views/layout/footer.php';
    exit;
}

/** Render view trong layout admin */
function admin_view(string $name, array $data = []): void
{
    extract($data);
    require APP_DIR . '/views/admin/layout/header.php';
    require APP_DIR . '/views/admin/' . $name . '.php';
    require APP_DIR . '/views/admin/layout/footer.php';
    exit;
}

/** Flash message */
function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function post(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

function get(string $key, $default = '')
{
    return $_GET[$key] ?? $default;
}

function number_vn($n): string
{
    return number_format((float)$n, 0, ',', '.');
}

function slugify(string $text): string
{
    $map = [
        'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
        'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
        'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
        'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
        'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y','đ'=>'d',
    ];
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/** Upload ảnh đơn giản, trả về đường dẫn tương đối hoặc null */
function handle_upload(string $field, string $folder = 'uploads'): ?string
{
    if (empty($_FILES[$field]['tmp_name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        return null;
    }
    $info = @getimagesize($_FILES[$field]['tmp_name']);
    if ($info === false) {
        return null;
    }
    $dir = BASE_DIR . '/public/' . $folder;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (move_uploaded_file($_FILES[$field]['tmp_name'], $dir . '/' . $name)) {
        return '/' . $folder . '/' . $name;
    }
    return null;
}
