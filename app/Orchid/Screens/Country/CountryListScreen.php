<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Country;

use App\Models\Country;
use App\Orchid\Layouts\Country\CountryListLayout;
use Orchid\Screen\Action;
use Orchid\Screen\Screen;

class CountryListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'countries' => Country::orderBy('name')->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Страны';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return '';
    }

    public function permission(): ?iterable
    {
        return [];
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            CountryListLayout::class,
        ];
    }

}
