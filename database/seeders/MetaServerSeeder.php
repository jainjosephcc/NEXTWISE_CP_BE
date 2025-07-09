<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MetaServer;
use Illuminate\Support\Facades\Hash; // If needed for any fields
use Illuminate\Support\Str; // If needed for any string manipulations

class MetaServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the meta servers to be inserted
        $metaServers = [
            [
                'company_name' => 'Alpha Server',
                'server_name' => 'Alpha Server',
                'server_ip' => '192.168.1.10',
                'server_port' => 8080,
                'api_url' => 'https://alpha.example.com/api',
                'server_type' => 'MT5',
                'ssl_enabled' => true,
                'manager_id' => 1, // Ensure this StaffUser ID exists
                'status' => 1, // Or use boolean/integer based on schema
                'description' => 'Primary production server.',
                'created_by' => 1, // StaffUser ID who created the record
                'updated_by' => 1, // StaffUser ID who last updated the record
            ],
            [
                'company_name' => 'BETA Server',
                'server_name' => 'BETA Server',
                'server_ip' => '192.168.1.10',
                'server_port' => 8080,
                'api_url' => 'https://alpha.example.com/api',
                'server_type' => 'MT5',
                'ssl_enabled' => true,
                'manager_id' => 1, // Ensure this StaffUser ID exists
                'status' => 1, // Or use boolean/integer based on schema
                'description' => 'Primary production server.',
                'created_by' => 1, // StaffUser ID who created the record
                'updated_by' => 1,
            ]
            // Add more entries as needed
        ];

        foreach ($metaServers as $server) {
            // Check if the meta server already exists to prevent duplicates
            if (!MetaServer::where('server_name', $server['server_name'])->exists()) {
                MetaServer::create($server);
                $this->command->info("Inserted MetaServer: {$server['server_name']}");
            } else {
                $this->command->warn("MetaServer already exists: {$server['server_name']}");
            }
        }
    }
}
