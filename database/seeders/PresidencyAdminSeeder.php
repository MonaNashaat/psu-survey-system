<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PresidencyAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'president@psu.edu.eg',
            ],
            [
                'name' => 'المكتب الفني لرئيس الجامعة',
                'password' => Hash::make('Psu@123456'),
                'role' => 'presidency_admin',
                'faculty_id' => null,
                'department_id' => null,
            ]
        );
    }
}