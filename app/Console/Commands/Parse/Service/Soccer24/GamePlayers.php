<?php

namespace App\Console\Commands\Parse\Service\Soccer24;

use App\Models\Game;
use Illuminate\Support\Facades\Http;

class GamePlayers
{
    public static function start(Game $game)
    {
        $html = self::loadHtml($game);
        $data = self::parseStr($html);
    }

    public static function loadHtml(Game $game): string
    {
        $result = Http::withoutVerifying()
            ->get("https://1023.ds.lsapp.eu/pq_graphql?_hash=dlie&eventId={$game->soccer24Id}&projectId=1023");

        return $result->body();
    }

    public static function parseStr(string $str)
    {
        $all = json_decode($str, true);

        foreach ($all['data']['findEventById']['eventParticipants'] as $command) {
            $players = $command['lineup']['players'];
            dd($players);
        }

        dd($all['data']['findEventById']['eventParticipants']);
    }
}
