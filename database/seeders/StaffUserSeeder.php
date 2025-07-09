<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffUser;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the users to be inserted
        $staffUsers = [
            [
                'staff_name'    => 'IT Support',
                'email'         => 'itsupport@dev.com',
                'password'      => Hash::make('Dinken@01'),
                'contact_no'    => '123-456-7890',
                'group_id'      => 1, // Ensure this group_id exists in tb_staff_groups
                'active'        => true,
                'is_deleted'    => false,
            ],
            [
                'staff_name'    => 'Lesly Bitrage',
                'email'         => 'lesly.bitrage@dev.com',
                'password'      => Hash::make('Dinken@01'),
                'contact_no'    => '234-567-8901',
                'group_id'      => 1,
                'active'        => true,
                'is_deleted'    => false,
            ],
            [
                'staff_name'    => 'Arya Bitrage',
                'email'         => 'arya.bitrage@dev.com',
                'password'      => Hash::make('Dinken@01'),
                'contact_no'    => '345-678-9012',
                'group_id'      => 1,
                'active'        => true,
                'is_deleted'    => false,
            ],
        ];

        // Insert each user into the database
        foreach ($staffUsers as $user) {
            // Check if the user already exists to prevent duplicates
            if (!StaffUser::where('email', $user['email'])->exists()) {
                StaffUser::create($user);
                $this->command->info("Inserted StaffUser: {$user['email']}");
            } else {
                $this->command->warn("StaffUser already exists: {$user['email']}");
            }
        }
    }
}
