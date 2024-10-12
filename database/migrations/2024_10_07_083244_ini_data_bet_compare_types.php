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
            'Tb',
            'Tm',
            'N',
            'Y',
            'D',
            'T1',
            'T2',
            'KF',
            'X1',
            'X2',
            'X3',
            'X4',
            'X5',
            'X6',
            'Kf_F1',
            'Kf_F2',
            'NT1',
            'YT1',
            'NT2',
            'YT2',
        ];

        foreach ($data as $item) {
            \App\Models\BetCompareType::create(
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
        \App\Models\BetCompareType::truncate();
    }
};
