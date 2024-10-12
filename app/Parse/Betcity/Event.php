<?php

namespace App\Parse\Betcity;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Event
{
    private string $json;
    private string|int $betcityGameId;

    public function __construct(string|int $betcityGameId)
    {
        $this->betcityGameId = $betcityGameId;
    }

    public function start(): static
    {
        $url = "https://ad.betcity.ru/d/off/ext?rev=3&ids={$this->betcityGameId}&ver=14&lng=1&csn=ooca9s";
        $headers = [
            'Cookie' => 'cud=F2/It2bp2jtWVZL4A7I9Ag==; __zzatgib-w-betcity=MDA0dBA=Fz2+aQ==; _ym_uid=1726601792724647404; _ym_d=1726601792; xq_icm=10000; acceptCookies=true; lang=1; _gid=GA1.2.1703710472.1728046107; _ga=GA1.1.1019729254.1726601791; dev=6f72b8f33f2d254586ffa25aa7ef219b; _ym_isad=2; _gat=1; cfidsgib-w-betcity=WTDsTMLo1zc1Oz9kZK3UTo1ilkHvQuYq84WcPCt6FyQ0JOOf1tSAdbjzZsnLgHmvZqEkduw3EeW0OrvyGuFGS9SU+znsfKrWi7R62mDpXm0J3FNIXfmgOqbRxW24NrhsBOLqXp5FjwdT8NzMlpTgkD7qR43Xg3ekwT1r8Oc=; gsscgib-w-betcity=b634sYGurYtrabWQjBQyTyQLZVkbZpeP3ywvt7O/tjRL7Tu6HO9xo63vNOXG2VbcmHdNE8Hq6YFvM5f2OyFD3s68Is89EmpbAZytc28jTxfioQB//ZthcQN46ftigAuLQnzAqA1LUJNMP7MlL4XjTBZLkWOAmY1yYBGKhNw4tfRhHy8CeC1ALwaKbwYlzWwT9bXhdsHWBYmykTyxWiFgewVwoBZviAHwzwCi2lKxRptAXgiA9JevPwS36lKmAA==; _ga_J8WNGM7JHF=GS1.2.1728046112.12.1.1728046556.50.0.0; _ga_WQEG57XEGJ=GS1.1.1728046109.11.1.1728046556.51.0.0'
        ];
        $result = Http::withoutVerifying()->withHeaders($headers)->get($url);

        $this->json = $result->body();

        Log::channel('Betcity')->info('Список событий матча :: Запрос: ' . $url . '. Ответ: ' . $result->body() . '. Статус: ' . $result->status());

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
