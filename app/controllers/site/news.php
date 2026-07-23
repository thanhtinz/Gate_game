<?php

function news_index(): void
{
    $type = get('type') === 'event' ? 'event' : (get('type') === 'news' ? 'news' : '');
    $gameId = (int)get('game_id');
    $page = max(1, (int)get('page', 1));
    $perPage = 12;

    $where = 'n.status = 1';
    $params = [];
    if ($type) {
        $where .= ' AND n.type = ?';
        $params[] = $type;
    }
    if ($gameId) {
        $where .= ' AND n.game_id = ?';
        $params[] = $gameId;
    }
    $total = (int)(DB::one("SELECT COUNT(*) c FROM news n WHERE $where", $params)['c'] ?? 0);
    $offset = ($page - 1) * $perPage;
    $items = DB::all(
        "SELECT n.*, g.name AS game_name FROM news n LEFT JOIN games g ON g.id = n.game_id
         WHERE $where ORDER BY n.pinned DESC, n.created_at DESC LIMIT $perPage OFFSET $offset",
        $params
    );
    $games = DB::all('SELECT id, name FROM games WHERE status = 1 ORDER BY sort_order');
    view('news_index', [
        'title' => $type === 'event' ? 'Sự kiện' : 'Tin tức',
        'items' => $items,
        'games' => $games,
        'type' => $type,
        'gameId' => $gameId,
        'page' => $page,
        'pages' => (int)ceil($total / $perPage),
    ]);
}

function news_detail(string $slug): void
{
    $item = DB::one(
        'SELECT n.*, g.name AS game_name, g.slug AS game_slug FROM news n
         LEFT JOIN games g ON g.id = n.game_id WHERE n.slug = ? AND n.status = 1',
        [$slug]
    );
    if (!$item) {
        http_response_code(404);
        view('errors/404', ['title' => 'Không tìm thấy bài viết']);
    }
    $related = DB::all(
        'SELECT title, slug, created_at FROM news WHERE status = 1 AND id != ? AND (game_id = ? OR game_id IS NULL)
         ORDER BY created_at DESC LIMIT 5',
        [$item['id'], $item['game_id']]
    );
    view('news_detail', [
        'title' => $item['title'],
        'metaDesc' => $item['summary'],
        'item' => $item,
        'related' => $related,
    ]);
}
