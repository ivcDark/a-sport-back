<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'fio'        => $this->fio,
            'field_name' => $this->fieldName,
            'number'     => $this->number,
            'birthday'   => Carbon::createFromTimestamp($this->birthday)->addHour()->format('d.m.Y'),
            'position'   => $this->position,
            'avatar'     => $this->image,
            'parent'       => ClubResource::collection($this->clubs),
        ];
    }
}
