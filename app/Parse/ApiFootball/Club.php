<?php

namespace App\Parse\ApiFootball;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Club
{
    private string $json;
    private string|int $apiLeagueId;

    public function __construct(string|int $apiLeagueId)
    {
        $this->apiLeagueId = $apiLeagueId;
    }

    public function start(): static
    {
        $url = "https://apiv3.apifootball.com/?action=get_teams&league_id={$this->apiLeagueId}&APIkey=" . env('API_KEY_API_FOOTBALL');
        $result = Http::withoutVerifying()
            ->get($url);

        $this->json = $result->body();

        Log::channel('apiFootball')->info('CLUB :: Запрос: ' . $url . '. Ответ: ' . $result->body() . '. Статус: ' . $result->status());

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
