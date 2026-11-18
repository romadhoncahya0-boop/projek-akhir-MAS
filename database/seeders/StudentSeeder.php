<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['nisn' => '0068912345', 'name' => 'Muhammad Rizky Pratama', 'class' => 'XII MIPA 1', 'entry_year' => '2023/2024', 'create_login' => true],
            ['nisn' => '0068912346', 'name' => 'Annisa Nurul Aini', 'class' => 'XII MIPA 2', 'entry_year' => '2023/2024'],
            ['nisn' => '0068912347', 'name' => 'Dimas Wahyu Pratama', 'class' => 'XII IPS 1', 'entry_year' => '2023/2024'],
            ['nisn' => '0068912348', 'name' => 'Clarissa Aurelia', 'class' => 'XI MIPA 3', 'entry_year' => '2024/2025'],
            ['nisn' => '0068912349', 'name' => 'Kevin Jonathan', 'class' => 'XII MIPA 4', 'entry_year' => '2023/2024'],
            ['nisn' => '0051234598', 'name' => 'Fajar Nugroho', 'class' => 'XII PPLG 2', 'entry_year' => '2023/2024'],
            ['nisn' => '0051234533', 'name' => 'Salsa Maharani', 'class' => 'XII PPLG 2', 'entry_year' => '2023/2024'],
            ['nisn' => '0051234571', 'name' => 'Bagus Triatmojo', 'class' => 'XII RPL 2', 'entry_year' => '2023/2024'],
        ];

        foreach ($students as $s) {
            $createLogin = $s['create_login'] ?? false;
            unset($s['create_login']);
            $s['status'] = 'aktif';

            $student = Student::create($s);

            if ($createLogin) {
                $user = User::create([
                    'name' => $s['name'],
                    'email' => 'rizky.pratama@siswa.mas.sch.id',
                    'password' => bcrypt('password'),
                    'role' => 'siswa',
                ]);
                $student->update(['user_id' => $user->id]);
            }
        }
    }
}
