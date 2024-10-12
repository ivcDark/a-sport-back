<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $data = [
            'IT_T1',
            'IT_T2',
            'YNT1',
            'YNT2',
            'TMSD',
            'YN',
            'SCR9',
            'X3',
            'F1',
            'T1',
            'T2',
            'T3',
            'W',
            'WX',
            'X3Num2',
            'T',
            'X3T1',
            'SCR10',
            'X3T2',
            'SCR7',
            'SCR4',
            'SCR5',
            'X6',
            'SCR2',
            'YNNum',
            'YNT1Num',
            'YNT2Num',
            'SCR34',
            'T_T1',
            'T_T2',
        ];

        foreach ($data as $item) {
            \App\Models\BetAbbreviatedType::create(
                [
                    'name' => $item
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\BetAbbreviatedType::truncate();
    }
};
