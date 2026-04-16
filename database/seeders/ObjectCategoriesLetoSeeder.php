<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObjectCategoriesLetoSeeder extends Seeder
{
    public function run()
    {
        // Родительские разделы
        $parents = [
            1 => 'Здания вокзалов',
            2 => 'Системы кондиционирования воздуха',
            3 => 'Пешеходные мосты',
            4 => 'Пешеходные тоннели',
            5 => 'Пассажирские платформы',
        ];

        $parentIds = [];

        foreach ($parents as $order => $name) {
            $parentIds[$order] = DB::table('object_categories_leto')->insertGetId([
                'name'       => $name,
                'parent_id'  => null,
                'unit'       => null,
                'sort_order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Дочерние виды работ (ед. изм. можно потом уточнить)
        $children = [
            // 1. Здания вокзалов
            ['parent' => 1, 'name' => 'Кровля', 'unit' => null, 'sort' => 1],
            ['parent' => 1, 'name' => 'Входные группы', 'unit' => null, 'sort' => 2],
            ['parent' => 1, 'name' => 'Оконные блоки', 'unit' => null, 'sort' => 3],
            ['parent' => 1, 'name' => 'Системы вентиляции', 'unit' => null, 'sort' => 4],
            ['parent' => 1, 'name' => 'Источники резервного питания', 'unit' => null, 'sort' => 5],

            // 2. Системы кондиционирования воздуха
            ['parent' => 2, 'name' => 'На балансе ДЖВ, обслуживаемые собственными силами', 'unit' => null, 'sort' => 1],
            ['parent' => 2, 'name' => 'На балансе ДЖВ, обслуживаемые по договору', 'unit' => null, 'sort' => 2],

            // 3. Пешеходные мосты
            ['parent' => 3, 'name' => 'На балансе ДЖВ, обслуживаемые собственными силами', 'unit' => null, 'sort' => 1],
            ['parent' => 3, 'name' => 'На балансе ДЖВ, обслуживаемые по договору', 'unit' => null, 'sort' => 2],
            ['parent' => 3, 'name' => 'На балансе НТЭ, обслуживаемые по наряд заказу / регламенту', 'unit' => null, 'sort' => 3],

            // 4. Пешеходные тоннели
            ['parent' => 4, 'name' => 'На балансе ДЖВ, обслуживаемые собственными силами', 'unit' => null, 'sort' => 1],
            ['parent' => 4, 'name' => 'На балансе ДЖВ, обслуживаемые по договору', 'unit' => null, 'sort' => 2],

            // 5. Пассажирские платформы
            ['parent' => 5, 'name' => 'На балансе ДЖВ, обслуживаемые собственными силами', 'unit' => null, 'sort' => 1],
            ['parent' => 5, 'name' => 'На балансе ДЖВ, обслуживаемые по договору / наряд заказу через НТЭ', 'unit' => null, 'sort' => 2],
        ];

        foreach ($children as $child) {
            DB::table('object_categories_leto')->insert([
                'name'       => $child['name'],
                'parent_id'  => $parentIds[$child['parent']],
                'unit'       => $child['unit'],
                'sort_order' => $child['sort'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
