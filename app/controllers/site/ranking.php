<?php

function ranking_index(): void
{
    $games = DB::all('SELECT id, slug, name, adapter FROM games WHERE status = 1 ORDER BY sort_order');
    $gameId = (int)get('game_id') ?: (int)($games[0]['id'] ?? 0);
    $serverId = (int)get('server_id');

    $servers = $gameId
        ? DB::all('SELECT id, name FROM game_servers WHERE game_id = ? AND status = 1 ORDER BY sort_order', [$gameId])
        : [];
    if (!$serverId && $servers) {
        $serverId = (int)$servers[0]['id'];
    }

    $ranking = null;
    $error = null;
    if ($gameId && $serverId) {
        $game = DB::one('SELECT * FROM games WHERE id = ?', [$gameId]);
        $server = DB::one('SELECT * FROM game_servers WHERE id = ? AND game_id = ?', [$serverId, $gameId]);
        if ($game && $server) {
            try {
                $adapter = AdapterRegistry::forGame($game['adapter']);
                $ranking = $adapter->getRankings(GameDB::forServer($server), 50);
            } catch (Throwable $e) {
                $error = 'Không lấy được bảng xếp hạng (server đang bảo trì?).';
            }
        }
    }

    view('ranking', [
        'title' => 'Bảng xếp hạng',
        'games' => $games,
        'servers' => $servers,
        'gameId' => $gameId,
        'serverId' => $serverId,
        'ranking' => $ranking,
        'error' => $error,
    ]);
}
