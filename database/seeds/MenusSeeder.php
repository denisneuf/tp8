<?php

use think\migration\Seeder;
use think\facade\Db;
use app\model\Menu;
use app\model\MenuColumn;
use app\model\MenuLink;

class MenusSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar si el seed ya fue ejecutado
        $seedName = 'MenusSeeder';
        if (Db::name('seeds')->where('seed_name', $seedName)->find()) {
            echo "$seedName ya fue ejecutado anteriormente.\n";
            return;
        }

        $menus = [
            'Mac' => [
                'Explora el Mac' => ['MacBook Air', 'MacBook Pro', 'iMac', 'Mac mini', 'Mac Studio', 'Mac Pro'],
                'Comprar un Mac' => ['Comprar', 'Financiamiento', 'Canjea tu dispositivo', 'Accesorios'],
                'Más allá del Mac' => ['macOS Sonoma', 'Privacidad', 'Apps de Apple'],
                'Soporte y ayuda' => ['Soporte para Mac', 'Manual del usuario', 'Comunidad de Apple'],
            ],
            'iPad' => [
                'Explora el iPad' => ['iPad Pro', 'iPad Air', 'iPad', 'iPad mini'],
                'Comprar un iPad' => ['Comprar', 'Financiamiento', 'Canjea tu dispositivo', 'Accesorios'],
                'Más allá del iPad' => ['iPadOS', 'Privacidad', 'Apps de Apple'],
                'Soporte y ayuda' => ['Soporte para iPad', 'Manual del usuario', 'Comunidad de Apple'],
            ],
            'iPhone' => [
                'Explora el iPhone' => ['iPhone 15 Pro', 'iPhone 15', 'iPhone 14', 'iPhone SE', 'Comparar modelos', 'Cambiar a iPhone'],
                'Comprar un iPhone' => ['Comprar', 'Financiamiento', 'Canjea tu dispositivo', 'Accesorios'],
                'Más allá del iPhone' => ['iOS 17', 'Privacidad', 'Apps de Apple', 'AirTag'],
                'Soporte y ayuda' => ['Soporte para iPhone', 'Manual del usuario', 'Comunidad de Apple'],
            ],
            'Watch' => [
                'Explora el Watch' => ['Apple Watch Series 9', 'Apple Watch SE', 'Apple Watch Ultra 2'],
                'Comprar un Watch' => ['Comprar', 'Financiamiento', 'Canjea tu dispositivo', 'Correas'],
                'Más allá del Watch' => ['watchOS', 'Salud y bienestar', 'Apps de Apple'],
                'Soporte y ayuda' => ['Soporte para Watch', 'Manual del usuario', 'Comunidad de Apple'],
            ],
            'Vision' => [
                'Explora Vision Pro' => ['Apple Vision Pro', 'Apps', 'Tech Specs'],
                'Comprar Vision Pro' => ['Comprar', 'Financiamiento'],
                'Más allá de Vision Pro' => ['visionOS', 'Privacidad'],
                'Soporte y ayuda' => ['Soporte para Vision Pro', 'Manual del usuario'],
            ],
            'AirPods' => [
                'Explora AirPods' => ['AirPods Pro', 'AirPods (3rd gen)', 'AirPods Max'],
                'Comprar AirPods' => ['Comprar', 'Financiamiento', 'Accesorios'],
                'Más allá de AirPods' => ['Audio Espacial', 'Privacidad'],
                'Soporte y ayuda' => ['Soporte para AirPods', 'Manual del usuario'],
            ],
            'TV & Home' => [
                'Explora TV y Home' => ['Apple TV 4K', 'HomePod', 'HomePod mini'],
                'Comprar TV y Home' => ['Comprar', 'Financiamiento'],
                'Más allá de TV y Home' => ['tvOS', 'Privacidad'],
                'Soporte y ayuda' => ['Soporte para TV y Home', 'Manual del usuario'],
            ],
            'Entretenimiento' => [
                'Servicios de Apple' => ['Apple One', 'Apple TV+', 'Apple Music', 'Apple Arcade', 'Apple Podcasts', 'Apple Books'],
                'Soporte y ayuda' => ['Soporte de servicios', 'Comunidad de Apple'],
            ],
            'Accesorios' => [
                'Explora Accesorios' => ['Mac Accessories', 'iPad Accessories', 'iPhone Accessories', 'Watch Accessories'],
                'Comprar Accesorios' => ['Comprar', 'Financiamiento'],
                'Soporte y ayuda' => ['Soporte de accesorios', 'Comunidad de Apple'],
            ],
            'Soporte' => [
                'Soporte general' => ['Soporte Overview', 'iPhone', 'Mac', 'iPad', 'Watch', 'Music', 'TV'],
                'Comunidad y ayuda' => ['Comunidad de Apple', 'Manual del usuario'],
            ],
        ];

        $order = 1;
        foreach ($menus as $menuTitle => $columns) {
            $menu = Menu::create([
                'title' => $menuTitle,
                'url' => '/' . strtolower(str_replace([' ', '&'], ['-', 'and'], $menuTitle)),
                'has_submenu' => true,
                'order' => $order++,
                'visible' => true,
            ]);

            $columnOrder = 1;
            foreach ($columns as $columnTitle => $links) {
                $column = MenuColumn::create([
                    'menu_id' => $menu->id,
                    'title' => $columnTitle,
                    'order' => $columnOrder++,
                ]);

                $linkOrder = 1;
                foreach ($links as $linkTitle) {
                    MenuLink::create([
                        'column_id' => $column->id,
                        'title' => $linkTitle,
                        'url' => '/' . strtolower(str_replace([' ', '&'], ['-', 'and'], $linkTitle)),
                        'order' => $linkOrder++,
                    ]);
                }
            }
        }

        // Registrar que este seed se ejecutó
        Db::name('seeds')->insert([
            'seed_name' => $seedName,
            'executed_at' => date('Y-m-d H:i:s'),
        ]);

        echo "$seedName ejecutado correctamente.\n";
    }
}
