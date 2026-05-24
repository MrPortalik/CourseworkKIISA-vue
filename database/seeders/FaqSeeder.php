<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'question' => 'Как предложить статью к публикации?',
                'answer' => 'Создайте статью и отметьте «Предложить к публикации». После проверки администратор опубликует материал.',
                'sort_order' => 1,
            ],
            [
                'question' => 'Что такое объекты в названиях статей?',
                'answer' => 'Статьи об объектах нумеруются, например «Объект 1». В боковом меню можно перейти к диапазонам номеров.',
                'sort_order' => 2,
            ],
        ];

        foreach ($items as $item) {
            Faq::firstOrCreate(['question' => $item['question']], $item);
        }
    }
}
