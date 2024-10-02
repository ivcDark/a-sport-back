<?php

namespace App\Parse\ApiFootball;

use Illuminate\Support\Facades\Http;

class Country
{
    private string $json;

    public function start(): static
    {
        $result = Http::withoutVerifying()
            ->get("https://apiv3.apifootball.com/?action=get_countries&APIkey=" . env('API_KEY_API_FOOTBALL'));

        $this->json = $result->body();

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
