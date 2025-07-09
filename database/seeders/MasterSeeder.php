<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Master;
use App\Models\StaffUser;
use App\Models\MetaServer;
use Illuminate\Support\Facades\Hash; // If needed for any fields
use Illuminate\Support\Str; // If needed for any string manipulations

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the masters to be inserted
        $masters = [
            [
                'mc_name' => 'Master Alpha',
                'mc_mt5_id' => '1000',
                'server_id' => 1, // Ensure this MetaServer ID exists
                'mapped_by' => 1, // Ensure this StaffUser ID exists
                'performance_matrix' => 0,
                'risk_factor' => 0,
                'is_config_identical' => 0,
                'risk_approach' => 'LMUL',
                'lot_size' => NULL,
                'multiplier' => 100,
                'fixed_balance' => NULL,
                'copy_sl' => false,
                'copy_tp' => false,
                'is_reverse' => false,
                'status' => 1, // Or use boolean/integer based on schema
                'is_live' => true,
                'created_by' => 1, // StaffUser ID who created the master
                'updated_by' => 1, // StaffUser ID who last updated the master
            ],
            // Add more entries as needed
        ];

        foreach ($masters as $master) {
            // Check if the master already exists to prevent duplicates
            if (!Master::where('mc_mt5_id', $master['mc_mt5_id'])->exists()) {
                Master::create($master);
                $this->command->info("Inserted Master: {$master['mc_name']}");
            } else {
                $this->command->warn("Master already exists: {$master['mc_name']}");
            }
        }
    }
}
