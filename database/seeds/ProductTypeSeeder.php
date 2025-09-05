<?php

declare(strict_types=1);

use think\migration\Seeder;
use app\model\ProductType;
use think\facade\Db;

class ProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        $seedName = 'ProductTypeSeeder';

        if (Db::name('seeds')->where('seed_name', $seedName)->find()) {
            echo "$seedName ya se ejecutó.";
            return;
        }

        $items = [
            [
                'bs_icon'     => 'desktop',
                'txt_short'   => 'Ordenador',
                'txt_long'    => 'Equipos de cómputo personales y profesionales',
                'slug'        => 'ordenador',
                'description' => 'Ordenadores de escritorio y portátiles',
                'pic'         => 'ordenador.jpg',
                'bg'          => 'bg-ordenador.jpg',
                'visible'     => 1,
            ],
            [
                'bs_icon'     => 'tv',
                'txt_short'   => 'Monitor',
                'txt_long'    => 'Pantallas para ordenadores',
                'slug'        => 'monitor',
                'description' => 'Monitores LED, LCD y más',
                'pic'         => 'monitor.jpg',
                'bg'          => 'bg-monitor.jpg',
                'visible'     => 1,
            ],
            [
                'bs_icon'     => 'bolt',
                'txt_short'   => 'Fuente',
                'txt_long'    => 'Fuentes de alimentación',
                'slug'        => 'fuente-alimentacion',
                'description' => 'PSU para ordenadores',
                'pic'         => 'fuente.jpg',
                'bg'          => 'bg-fuente.jpg',
                'visible'     => 1,
            ],
            [
                'bs_icon'     => 'box',
                'txt_short'   => 'Caja',
                'txt_long'    => 'Cajas para PC',
                'slug'        => 'caja-pc',
                'description' => 'Gabinetes para computadoras',
                'pic'         => 'caja.jpg',
                'bg'          => 'bg-caja.jpg',
                'visible'     => 1,
            ],
            [
                'bs_icon'     => 'battery-full',
                'txt_short'   => 'SAI',
                'txt_long'    => 'Sistemas de alimentación ininterrumpida',
                'slug'        => 'sai',
                'description' => 'UPS para protección eléctrica',
                'pic'         => 'sai.jpg',
                'bg'          => 'bg-sai.jpg',
                'visible'     => 1,
            ],
        ];

        foreach ($items as $item) {
            ProductType::create($item);
        }

        Db::name('seeds')->insert([
            'seed_name'   => $seedName,
            'executed_at' => date('Y-m-d H:i:s'),
        ]);

        echo "$seedName ejecutado correctamente.";
    }
}
