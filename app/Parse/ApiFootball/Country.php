<?php

namespace App\Parse\ApiFootball;

use Illuminate\Support\Facades\Http;

class Country
{
    private string $json;

    public function start(): static
    {
        $result = Http::withoutVerifying()
            ->get("https://apiv3.apifootball.com/?action=get_countries&APIkey=9c29b5bad926929a9ce145d6b5c082097c6d02c89172fbb0252bc4860b0c32ae");

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
