<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slave;
use App\Models\Master;
use App\Models\StaffUser;

class SlaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch the master with ID 1
        $master = Master::find(1);

        if (!$master) {
            $this->command->error('Master with ID 1 does not exist. Please ensure the MasterSeeder has been run.');
            return;
        }

        // Define the slaves to be inserted
        $slaves = [
            [
                'master_id' => $master->id,
                'sl_mt5_id' => 1001,
                'server_id' => 1, // Ensure Master with ID 2 exists
                'mapped_by' => 1, // Ensure StaffUser with ID 1 exists
                'is_config_unique' => true,
                'risk_approach' => 'FIXL',
                'lot_size' => 1.0,
                'multiplier' => 2.0,
                'fixed_balance' => 5000.00,
                'copy_sl' => true,
                'copy_tp' => false,
                'is_reverse' => false,
                'status' => true,
                'is_live' => true,
            ],
            [
                'master_id' => $master->id,
                'sl_mt5_id' => 1002,
                'server_id' => 1, // Ensure Master with ID 3 exists
                'mapped_by' => 2, // Ensure StaffUser with ID 2 exists
                'is_config_unique' => false,
                'risk_approach' => 'LMUL',
                'lot_size' => 1.5,
                'multiplier' => 3.0,
                'fixed_balance' => 7000.00,
                'copy_sl' => false,
                'copy_tp' => true,
                'is_reverse' => true,
                'status' => false,
                'is_live' => false,
            ],
        ];

        foreach ($slaves as $slaveData) {
            // Check for duplicate based on sl_mt5_id
            if (!Slave::where('sl_mt5_id', $slaveData['sl_mt5_id'])->exists()) {
                // Ensure that server_id exists
                $server = Master::find($slaveData['server_id']);
                if (!$server) {
                    $this->command->warn("Server with ID {$slaveData['server_id']} does not exist. Skipping Slave with sl_mt5_id {$slaveData['sl_mt5_id']}.");
                    continue;
                }

                // Ensure that mapped_by (StaffUser) exists
                $staffUser = StaffUser::find($slaveData['mapped_by']);
                if (!$staffUser) {
                    $this->command->warn("StaffUser with ID {$slaveData['mapped_by']} does not exist. Skipping Slave with sl_mt5_id {$slaveData['sl_mt5_id']}.");
                    continue;
                }

                Slave::create($slaveData);
                $this->command->info("Inserted Slave: sl_mt5_id {$slaveData['sl_mt5_id']}");
            } else {
                $this->command->warn("Slave with sl_mt5_id {$slaveData['sl_mt5_id']} already exists.");
            }
        }
    }
}
