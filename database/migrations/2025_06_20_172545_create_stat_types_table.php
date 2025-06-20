<?php

use App\Models\StatType;
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
        Schema::create('stat_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('category')->nullable();
            $table->timestamps();
        });

        $types = [
            ['name' => 'Expected Goals (xG)', 'category' => 'Top stats'],
            ['name' => 'Ball Possession', 'category' => 'Top stats'],
            ['name' => 'Total shots', 'category' => 'Top stats'],
            ['name' => 'Shots on target', 'category' => 'Top stats'],
            ['name' => 'Corner Kicks', 'category' => 'Top stats'],
            ['name' => 'Passes', 'category' => 'Top stats'],
            ['name' => 'Yellow Cards', 'category' => 'Top stats'],
            ['name' => 'Expected Goals (xG)', 'category' => 'Shots'],
            ['name' => 'xG on target (xGOT)', 'category' => 'Shots'],
            ['name' => 'Total shots', 'category' => 'Shots'],
            ['name' => 'Shots on target', 'category' => 'Shots'],
            ['name' => 'Shots off target', 'category' => 'Shots'],
            ['name' => 'Blocked Shots', 'category' => 'Shots'],
            ['name' => 'Shots inside the Box', 'category' => 'Shots'],
            ['name' => 'Shots outside the Box', 'category' => 'Shots'],
            ['name' => 'Corner Kicks', 'category' => 'Attack'],
            ['name' => 'Touches in opposition box', 'category' => 'Attack'],
            ['name' => 'Accurate through passes', 'category' => 'Attack'],
            ['name' => 'Offsides', 'category' => 'Attack'],
            ['name' => 'Free Kicks', 'category' => 'Attack'],
            ['name' => 'Passes', 'category' => 'Passes'],
            ['name' => 'Long passes', 'category' => 'Passes'],
            ['name' => 'Passes in final third', 'category' => 'Passes'],
            ['name' => 'Crosses', 'category' => 'Passes'],
            ['name' => 'Expected assists (xA)', 'category' => 'Passes'],
            ['name' => 'Throw-ins', 'category' => 'Passes'],
            ['name' => 'Fouls', 'category' => 'Defense'],
            ['name' => 'Tackles', 'category' => 'Defense'],
            ['name' => 'Duels won', 'category' => 'Defense'],
            ['name' => 'Clearances', 'category' => 'Defense'],
            ['name' => 'Interceptions', 'category' => 'Defense'],
            ['name' => 'Goalkeeper Saves', 'category' => 'Goalkeeping'],
            ['name' => 'xGOT faced', 'category' => 'Goalkeeping'],
            ['name' => 'Goals prevented', 'category' => 'Goalkeeping'],
        ];

        foreach ($types as $type) {
            StatType::create([
                'name' => $type['name'],
                'category' => $type['category'],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stat_types');
    }
};
