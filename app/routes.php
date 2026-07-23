<?php
/**
 * Bảng định tuyến. Mỗi route: 'METHOD /path' => [file controller, tên hàm].
 * {param} = tham số động, truyền vào hàm theo thứ tự.
 */
function routes(): array
{
    return [
        // Trang người chơi
        'GET /'                      => ['site/home.php', 'home_index'],
        'GET /game/{slug}'           => ['site/game.php', 'game_detail'],
        'GET /tin-tuc'               => ['site/news.php', 'news_index'],
        'GET /tin-tuc/{slug}'        => ['site/news.php', 'news_detail'],
        'GET /bxh'                   => ['site/ranking.php', 'ranking_index'],

        // Auth
        'GET /dang-nhap'             => ['site/auth.php', 'auth_login_form'],
        'POST /dang-nhap'            => ['site/auth.php', 'auth_login'],
        'GET /dang-ky'               => ['site/auth.php', 'auth_register_form'],
        'POST /dang-ky'              => ['site/auth.php', 'auth_register'],
        'GET /dang-xuat'             => ['site/auth.php', 'auth_logout'],
        'GET /tai-khoan'             => ['site/account.php', 'account_index'],
        'POST /doi-mat-khau'         => ['site/account.php', 'account_change_password'],

        // Nạp xu + quy đổi + giftcode
        'GET /nap-xu'                => ['site/topup.php', 'topup_index'],
        'POST /nap-xu'               => ['site/topup.php', 'topup_create'],
        'GET /nap-xu/don/{code}'     => ['site/topup.php', 'topup_order'],
        'GET /doi-xu'                => ['site/exchange.php', 'exchange_index'],
        'POST /doi-xu'               => ['site/exchange.php', 'exchange_submit'],
        'GET /giftcode'              => ['site/giftcode.php', 'giftcode_index'],
        'POST /giftcode'             => ['site/giftcode.php', 'giftcode_submit'],

        // AJAX API
        'GET /api/servers'           => ['site/api.php', 'api_servers'],
        'GET /api/characters'        => ['site/api.php', 'api_characters'],
        'GET /api/giftcode-info'     => ['site/api.php', 'api_giftcode_info'],
        'GET /api/order-status'      => ['site/api.php', 'api_order_status'],
        'POST /api/sepay-webhook'    => ['site/sepay.php', 'sepay_webhook'],

        // Admin
        'GET /admin'                 => ['admin/dashboard.php', 'admin_dashboard'],
        'GET /admin/games'           => ['admin/games.php', 'admin_games'],
        'POST /admin/games'          => ['admin/games.php', 'admin_games_save'],
        'GET /admin/servers'         => ['admin/servers.php', 'admin_servers'],
        'POST /admin/servers'        => ['admin/servers.php', 'admin_servers_save'],
        'GET /admin/coin-packages'   => ['admin/coin_packages.php', 'admin_coin_packages'],
        'POST /admin/coin-packages'  => ['admin/coin_packages.php', 'admin_coin_packages_save'],
        'GET /admin/exchange-packages'  => ['admin/exchange_packages.php', 'admin_exchange_packages'],
        'POST /admin/exchange-packages' => ['admin/exchange_packages.php', 'admin_exchange_packages_save'],
        'GET /admin/users'           => ['admin/users.php', 'admin_users'],
        'POST /admin/users'          => ['admin/users.php', 'admin_users_save'],
        'GET /admin/orders'          => ['admin/orders.php', 'admin_orders'],
        'GET /admin/exchanges'       => ['admin/orders.php', 'admin_exchanges'],
        'GET /admin/news'            => ['admin/news.php', 'admin_news'],
        'POST /admin/news'           => ['admin/news.php', 'admin_news_save'],
        'GET /admin/banners'         => ['admin/banners.php', 'admin_banners'],
        'POST /admin/banners'        => ['admin/banners.php', 'admin_banners_save'],
        'GET /admin/giftcodes'       => ['admin/giftcodes.php', 'admin_giftcodes'],
        'POST /admin/giftcodes'      => ['admin/giftcodes.php', 'admin_giftcodes_save'],
        'GET /admin/settings'        => ['admin/settings.php', 'admin_settings'],
        'POST /admin/settings'       => ['admin/settings.php', 'admin_settings_save'],
    ];
}

function dispatch(string $uri, string $method): void
{
    foreach (routes() as $route => $handler) {
        [$m, $pattern] = explode(' ', $route, 2);
        if ($m !== $method) {
            continue;
        }
        $regex = '#^' . preg_replace('/\{[a-z_]+\}/', '([^/]+)', $pattern) . '$#';
        if (preg_match($regex, $uri, $matches)) {
            array_shift($matches);
            [$file, $fn] = $handler;
            require APP_DIR . '/controllers/' . $file;
            $fn(...array_map('urldecode', $matches));
            return;
        }
    }
    http_response_code(404);
    if (function_exists('view')) {
        view('errors/404', ['title' => 'Không tìm thấy trang']);
    }
    exit('404 Not Found');
}
