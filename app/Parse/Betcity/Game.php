<?php

namespace App\Parse\Betcity;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Game
{
    private string $json;
    private string|int $betcityLeagueId;

    public function __construct(string|int $betcityLeagueId)
    {
        $this->betcityLeagueId = $betcityLeagueId;
    }

    public function start(): static
    {
        $url = "https://ad.betcity.ru/d/off/events?rev=6&ver=14&lng=1&csn=ooca9s";
        $bodyRequest = [
            'ids' => $this->betcityLeagueId
        ];
        $result = Http::withoutVerifying()
            ->post($url, $bodyRequest);

        $this->json = $result->body();

        Log::channel('Betcity')->info('Список матчей :: Запрос: ' . $url . '. Ответ: ' . $result->body() . '. Статус: ' . $result->status());

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
