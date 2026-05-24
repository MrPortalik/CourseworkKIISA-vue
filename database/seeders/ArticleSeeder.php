<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::firstOrCreate(
            ['email' => 'author@kiisa.test'],
            [
                'name' => 'Тестовый автор',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $samples = [
            'Фантастика' => [
                'Объект 12: Сигнал с орбиты',
                'Портал в секторе Gamma',
                'Хроники нулевого цикла',
            ],
            'Детектив' => [
                'Дело о пропавшем архиве',
                'Тени на станции Aurora',
                'Последний протокол допроса',
            ],
            'Приключения' => [
                'Экспедиция к краю карты',
                'Семь ключей от храма',
                'Шторм в долине ветров',
            ],
            'Драма' => [
                'Письмо из старого сектора',
                'Две линии одной судьбы',
                'Тишина после вспышки',
            ],
            'Справочник' => [
                'Классификация объектов: введение',
                'Глоссарий терминов КИИСА',
                'Правила публикации материалов',
            ],
        ];

        foreach ($samples as $categoryName => $titles) {
            $category = Category::where('name', $categoryName)->first();
            if (! $category) {
                continue;
            }

            foreach ($titles as $index => $title) {
                $slug = Str::slug($title);
                if (Article::where('slug', $slug)->exists()) {
                    $slug .= '-'.($index + 1);
                }

                Article::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => $title,
                        'content' => '<p>Тестовая статья категории «'.$categoryName.'». '.$title.'.</p>',
                        'user_id' => $author->id,
                        'category_id' => $category->id,
                        'is_published' => true,
                        'is_publishable' => true,
                        'published_at' => now()->subDays($index),
                        'is_new' => $index === 0,
                        'is_hit' => $index === 1,
                    ]
                );
            }
        }
    }
}
