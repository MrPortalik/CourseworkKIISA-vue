<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Фантастика', 'description' => 'Научная и художественная фантастика вселенной КИИСА'],
            ['name' => 'Детектив', 'description' => 'Расследования и загадочные сюжетные линии'],
            ['name' => 'Приключения', 'description' => 'Экспедиции, путешествия и открытия'],
            ['name' => 'Драма', 'description' => 'Личные истории и конфликты персонажей'],
            ['name' => 'Справочник', 'description' => 'Официальные материалы и описания объектов'],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description']]
            );
        }
    }
}
