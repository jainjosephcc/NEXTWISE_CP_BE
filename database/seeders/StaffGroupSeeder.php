<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffGroup;

class StaffGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the default groups
        $groups = [
            [
                'group_name' => 'Admin',
                'description' => 'Administrators with full access.',
            ],
            [
                'group_name' => 'Manager',
                'description' => 'Managers with elevated permissions.',
            ],
            [
                'group_name' => 'Support',
                'description' => 'Support staff with limited access.',
            ],
        ];

        foreach ($groups as $group) {
            // Use firstOrCreate to avoid duplicates
            StaffGroup::firstOrCreate(
                ['group_name' => $group['group_name']],
                ['description' => $group['description']]
            );

            $this->command->info("Ensured StaffGroup: {$group['group_name']}");
        }
    }
}
