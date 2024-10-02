<?php

namespace App\Parse\ApiFootball;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Game
{
    private string $json;
    private string|int $apiLeagueId;
    private array $dates;

    public function __construct(string|int $apiLeagueId, array $dates)
    {
        $this->apiLeagueId = $apiLeagueId;
        $this->dates = $dates;
    }

    public function start(): static
    {
        $url = "https://apiv3.apifootball.com/?action=get_events&from={$this->dates['from']}&to={$this->dates['to']}&league_id={$this->apiLeagueId}&APIkey=" . env('API_KEY_API_FOOTBALL') . "&timezone=UTC";
        $result = Http::withoutVerifying()
            ->get($url);

        $this->json = $result->body();

        Log::channel('apiFootball')->info('GAME :: Запрос: ' . $url . '. Ответ: ' . $result->body() . '. Статус: ' . $result->status());

        return $this;
    }

    public function json(): string
    {
        return $this->json;
    }

    public function toArray()
    {
        return json_decode($this->json(), true);
    }

    public function toObject()
    {
        return json_decode($this->json());
    }

}
