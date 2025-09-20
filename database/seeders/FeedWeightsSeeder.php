<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeedWeightsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $weights = [
            ['experiment_name' => 'default', 'weight_type' => 'region', 'weight_value' => 2.0],
            ['experiment_name' => 'default', 'weight_type' => 'affinity', 'weight_value' => 1.5],
            ['experiment_name' => 'default', 'weight_type' => 'engagement', 'weight_value' => 1.0],
            ['experiment_name' => 'default', 'weight_type' => 'recency', 'weight_value' => 0.8],
            ['experiment_name' => 'default', 'weight_type' => 'spotlight', 'weight_value' => 3.0],
            ['experiment_name' => 'default', 'weight_type' => 'diversity', 'weight_value' => -0.5],
        ];

        foreach ($weights as $weight) {
            \DB::table('feed_weights')->updateOrInsert(
                ['experiment_name' => $weight['experiment_name'], 'weight_type' => $weight['weight_type']],
                $weight + ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
