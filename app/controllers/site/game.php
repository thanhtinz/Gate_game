<?php

function game_detail(string $slug): void
{
    $game = DB::one('SELECT * FROM games WHERE slug = ? AND status = 1', [$slug]);
    if (!$game) {
        http_response_code(404);
        view('errors/404', ['title' => 'Không tìm thấy game']);
    }
    $servers = DB::all('SELECT id, name, note FROM game_servers WHERE game_id = ? AND status = 1 ORDER BY sort_order', [$game['id']]);
    $news = DB::all(
        'SELECT * FROM news WHERE status = 1 AND game_id = ? ORDER BY pinned DESC, created_at DESC LIMIT 8',
        [$game['id']]
    );
    $downloads = json_decode((string)$game['download_links'], true) ?: [];
    view('game_detail', [
        'title' => $game['name'],
        'metaDesc' => $game['short_desc'],
        'game' => $game,
        'servers' => $servers,
        'news' => $news,
        'downloads' => $downloads,
    ]);
}
