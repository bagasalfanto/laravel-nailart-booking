<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seedUsers = [
            [
                'role' => 'admin',
                'full_name' => 'Bagas Suryanto',
                'username' => 'bagas_admin',
                'email' => 'bagas@nailart.com',
                'phone_number' => '081234567890',
                'google_id' => 'google_admin_bagas_123',
                'kode_admin' => 'ADM-001',
            ],
            [
                'role' => 'admin',
                'full_name' => 'Dewi Maharani',
                'username' => 'dewi_admin',
                'email' => 'dewi@nailart.com',
                'phone_number' => '082345678901',
                'google_id' => null,
                'kode_admin' => 'ADM-002',
            ],
            [
                'role' => 'nailist',
                'full_name' => 'Siti Nurhaliza',
                'username' => 'siti_nailist',
                'email' => 'siti@nailart.com',
                'phone_number' => '089876543210',
                'google_id' => 'google_nailist_siti_123',
                'specialty' => 'Korean Style 3D',
            ],
            [
                'role' => 'nailist',
                'full_name' => 'Rina Gunawan',
                'username' => 'rina_nailist',
                'email' => 'rina@nailart.com',
                'phone_number' => '085432109876',
                'google_id' => 'google_nailist_rina_456',
                'specialty' => 'Geometric Minimalist',
            ],
            [
                'role' => 'nailist',
                'full_name' => 'Aulia Pramesti',
                'username' => 'aulia_nailist',
                'email' => 'aulia@nailart.com',
                'phone_number' => '081112223334',
                'google_id' => null,
                'specialty' => 'Bridal Glam & Soft Gel',
            ],
            [
                'role' => 'customers',
                'full_name' => 'Putri Ayu',
                'username' => 'putri_customer',
                'email' => 'putri@gmail.com',
                'phone_number' => '085555555555',
                'google_id' => 'google_cust_putri_123',
            ],
            [
                'role' => 'customers',
                'full_name' => 'Risa Nur',
                'username' => 'risa_customer',
                'email' => 'risa@yahoo.com',
                'phone_number' => '085666666666',
                'google_id' => null,
            ],
            [
                'role' => 'customers',
                'full_name' => 'Diana Safitri',
                'username' => 'diana_customer',
                'email' => 'diana.safitri@email.com',
                'phone_number' => '085777777777',
                'google_id' => 'google_cust_diana_789',
            ],
            [
                'role' => 'customers',
                'full_name' => 'Nadia Kusuma',
                'username' => 'nadia_customer',
                'email' => 'nadia.kusuma@email.com',
                'phone_number' => '085888888888',
                'google_id' => null,
            ],
            [
                'role' => 'customers',
                'full_name' => 'Tasya Putri',
                'username' => 'tasya_customer',
                'email' => 'tasya.putri@email.com',
                'phone_number' => '085999999999',
                'google_id' => 'google_cust_tasya_321',
            ],
        ];

        foreach ($seedUsers as $seedUser) {
            $user = User::updateOrCreate(
                ['email' => $seedUser['email']],
                [
                    'full_name' => $seedUser['full_name'],
                    'username' => $seedUser['username'],
                    'phone_number' => $seedUser['phone_number'],
                    'google_id' => $seedUser['google_id'],
                    'password' => 'password123',
                ]
            );

            if ($seedUser['role'] === 'admin') {
                $user->admin()->updateOrCreate([], [
                    'kode_admin' => $seedUser['kode_admin'],
                ]);
            }

            if ($seedUser['role'] === 'nailist') {
                $user->nailist()->updateOrCreate([], [
                    'specialty' => $seedUser['specialty'],
                ]);
            }

            if ($seedUser['role'] === 'customers') {
                $user->customer()->firstOrCreate();
            }
        }

        $this->command->info('User seeded successfully (2 Admin, 3 Nailist, 5 Customers).');
    }
}
