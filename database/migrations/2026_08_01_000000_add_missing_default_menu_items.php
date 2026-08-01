<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $items =
            [
                [
                    'title'         => 'Ayarlar',
                    'route'         => 'settings.index',
                    'icon'          => '<i class="ki-outline ki-setting-2 fs-2"></i>',
                    'menu_id'       => 1,
                    'order'         => 5,
                    'dynamic_route' => 1,
                    'main'          => 1,
                ],
                [
                    'title'         => 'Geçmiş',
                    'route'         => 'logs.index',
                    'icon'          => '<i class="bi bi-clock-history fs-2"></i>',
                    'menu_id'       => 1,
                    'order'         => 6,
                    'dynamic_route' => 1,
                    'main'          => 1,
                ],
                [
                    'title'         => 'Media',
                    'route'         => 'media.index',
                    'icon'          => '<i class="bi bi-images fs-2"></i>',
                    'menu_id'       => 1,
                    'order'         => 7,
                    'dynamic_route' => 1,
                    'main'          => 1,
                ]
            ];

        foreach ($items as $item)
        {
            $exists = DB::table('menu_items')->where('route', $item['route'])->exists();

            if (!$exists)
            {
                DB::table('menu_items')->insert(
                    $item +
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {

    }
};
