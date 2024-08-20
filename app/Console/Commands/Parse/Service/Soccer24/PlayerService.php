<?php

namespace App\Console\Commands\Parse\Service\Soccer24;

use App\Dto\PlayerDto;
use App\Models\Club;
use App\Models\Country;
use App\Models\ModelIntegration;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class PlayerService
{
    private string $html;
    private string $slug;
    private string $id;
    private PlayerDto $playerDto;
    private Player $modelPlayer;

    public function __construct(string $slug, string $id)
    {
        $this->slug = $slug;
        $this->id = $id;
    }

    public function downloadHtml(): PlayerService
    {
        $result = Http::withoutVerifying()
            ->get("https://www.soccer24.com/ru/player/{$this->slug}/{$this->id}/");

        $this->html = $result->body();

        return $this;
    }

    public function parseHtml(): PlayerService
    {
        $crawler = new Crawler($this->html);
        $imageSrc = $crawler->filter('._player_bnfqw_15 img')->attr('src');
        $fioPlayer = $crawler->filter('._webTypeHeading02_1pm1i_12')->text();
        $nodeCommand = $crawler->filter('._secondary_1cy23_33');
        $command = $nodeCommand->count() > 0 ? $nodeCommand->attr('title') : null;
        $urlCommand =  $nodeCommand->count() > 0 ? $nodeCommand->attr('href') : null;
        $birthday = str_replace(
            ['(', ')'],
            '',
            $crawler->filter('.playerInfoItem')->eq(0)
                ->filter('._simpleText_zkkvt_4')->eq(2)
                ->text()
        );
        $dataCommand = explode('/', $urlCommand);
        $slugCommand = $urlCommand != null ? $dataCommand[3] : null;
        $idCommand = $urlCommand != null ? $dataCommand[4] : null;
        $player = [
            'imageSrc' => $imageSrc,
            'fio' => $fioPlayer,
            'id' => $this->id,
            'slug' => $this->slug,
            'birthday' => $birthday,
            'nameCommand' => $command,
            'slugCommand' => $slugCommand,
            'idCommand' => $idCommand,
        ];
        $this->playerDto = new PlayerDto($player);

        return $this;
    }

    public function insert(): PlayerService
    {
        $service = new \App\Service\PlayerService();
        $this->modelPlayer = $service->create($this->playerDto);

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->modelPlayer;
    }
}
