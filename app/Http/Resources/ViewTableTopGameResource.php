<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViewTableTopGameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'game'      => new GameResource($this->game),
            'win_home'  => $this->win_home,
            'win_guest' => $this->win_guest,
            'draw'      => $this->draw,
        ];
    }
}
