<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Country;

use App\Models\Country;
use Carbon\Carbon;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CountryListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'countries';

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id', 'ID')
                ->cantHide()
                ->render(fn (Country $country) => $country->id),

            TD::make('name', 'Наименование')
                ->cantHide()
                ->render(fn (Country $country) => $country->name),

            TD::make('code', 'Код')
                ->render(fn (Country $country) => $country->code),

            TD::make('created_at', 'Дата создания')
                ->render(fn (Country $country) => Carbon::parse($country->created_at)->format('d.m.Y H:i')),
        ];
    }
}
