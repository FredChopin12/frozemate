<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun demo untuk development. Ganti kata sandinya sebelum dipakai sungguhan.
     */
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@frozemate.test']);
        $admin->fill([
            'name' => 'Owner FrozeMate',
            'phone' => '0812-3456-7890',
            'password' => 'password',
        ]);
        $admin->role = User::ROLE_ADMIN;
        $admin->save();

        $customers = [
            ['Dinda Maharani', 'dinda@frozemate.test', '0813-1122-3344', 'Kos Melati No. 12, Jl. Dipatiukur, Coblong, Bandung'],
            ['Raka Pratama', 'raka@frozemate.test', '0857-2233-4455', 'Jl. Cikutra Baru III No. 8, Cibeunying Kaler, Bandung'],
            ['Ibu Sari Wulandari', 'sari@frozemate.test', '0821-3344-5566', 'Komplek Antapani Indah Blok C2 No. 5, Bandung'],
            ['Bayu Nugroho', 'bayu@frozemate.test', '0878-4455-6677', 'Jl. Sekeloa Tengah No. 21, Coblong, Bandung'],
        ];

        foreach ($customers as [$name, $email, $phone, $address]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'password' => 'password',
            ]);
        }
    }
}
