<?php

function home_index(): void
{
    $banners = DB::all('SELECT * FROM banners WHERE status = 1 ORDER BY sort_order');
    $games = DB::all('SELECT * FROM games WHERE status = 1 ORDER BY sort_order');
    $news = DB::all(
        "SELECT n.*, g.name AS game_name FROM news n LEFT JOIN games g ON g.id = n.game_id
         WHERE n.status = 1 AND n.type = 'news' ORDER BY n.pinned DESC, n.created_at DESC LIMIT 6"
    );
    $events = DB::all(
        "SELECT n.*, g.name AS game_name FROM news n LEFT JOIN games g ON g.id = n.game_id
         WHERE n.status = 1 AND n.type = 'event' ORDER BY n.pinned DESC, n.created_at DESC LIMIT 6"
    );
    view('home', [
        'title' => '',
        'banners' => $banners,
        'games' => $games,
        'news' => $news,
        'events' => $events,
    ]);
}
