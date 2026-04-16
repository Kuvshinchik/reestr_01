<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tablodata;

class TablodataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Способ 1: Создать 50 случайных записей
        Tablodata::factory()->count(50)->create();
        
        // Способ 2: Создать записи с определенными параметрами
        Tablodata::factory()->count(10)->create([
            'type' => 'admin',
            'height' => 180,
        ]);
        
        // Способ 3: Использование кастомных состояний
        Tablodata::factory()
            ->count(5)
            ->admin() // Используем состояние "admin" из фабрики
            ->create();
            
        Tablodata::factory()
            ->count(3)
            ->tall() // Используем состояние "tall" из фабрики
            ->create();
        
        // Способ 4: Создание конкретных записей вручную (для тестирования)
        Tablodata::create([
            'ip' => '192.168.1.100',
            'height' => 175,
            'width' => 70,
            'type' => 'superadmin',
            'yearbirthday' => 1985,
            'yearbeginworking' => 2010,
            'foto' => '/images/admin.jpg',
            'qrcode' => 'SUPERADMIN123456789',
        ]);
        
        // Способ 5: Создание с определенным годом рождения
        Tablodata::factory()
            ->count(7)
            ->bornInYear(1990)
            ->create();
        
        $this->command->info('Таблица tablodata заполнена тестовыми данными!');
        $this->command->info('Всего записей: ' . Tablodata::count());
    }
}