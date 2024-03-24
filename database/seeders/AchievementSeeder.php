<?php

namespace Database\Seeders;

use App\Http\Enums\AchievementTypeEnum;
use App\Models\Achievement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'title' => 'Big Boss',
                'description' => 'Кол-во проведенных минут на карте',
                'levels' => [10,30,60,100,200,500,1000,2000,5000,10000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
            [
                'title' => 'Kangaroo',
                'description' => 'Прыжки',
                'levels' => [50,100,200,500,1000,3000,6000,10000,30000,100000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
            [
                'title' => 'Skill machine',
                'description' => 'Уничтожить противников',
                'levels' => [50,100,200,500,1000,3000,6000,10000,30000,100000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
            [
                'title' => 'The miner',
                'description' => 'Уничтожить врагов с помощью оружия ближнего боя',
                'levels' => [5,10,20,40,70,100,1500,300,600,1000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
            [
                'title' => 'Master Builder',
                'description' => 'Установить построек',
                'levels' => [50,100,200,500,1000,3000,6000,10000,30000,100000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
            [
                'title' => 'Champion',
                'description' => 'Выиграть матчи',
                'levels' => [5,10,20,40,70,100,1500,300,600,1000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
            [
                'title' => 'Headshot Eliminator',
                'description' => 'Устранить игроков в голову',
                'levels' => [5,10,20,40,70,100,1500,300,600,1000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
            [
                'title' => 'Forrest gump',
                'description' => 'Пробежать метров',
                'levels' => [1000,2000,5000,10000,20000,50000,10000,20000,40000,100000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
            [
                'title' => 'Double Pump',
                'description' => 'Устранить игроков с помощью дробовика',
                'levels' => [5,10,20,40,70,100,1500,300,600,1000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
            [
                'title' => 'Spray',
                'description' => 'Устранить игроков с помощью SMG',
                'levels' => [5,10,20,40,70,100,1500,300,600,1000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
            [
                'title' => 'Dancer',
                'description' => 'Использовать эмоцию в течение секунд',
                'levels' => [60,200,400,1000,2000,5000,10000,30000,70000,100000],
                'type' => AchievementTypeEnum::PROGRESSIVE->value
            ],
        ];

        foreach ($data as $item) {
            Achievement::create($item);
        }
    }
}
