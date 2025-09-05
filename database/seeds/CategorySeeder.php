<?php
declare(strict_types=1);

use think\migration\Seeder;
use app\model\Category;
use think\facade\Db;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $seedName = 'CategorySeeder';

        if (Db::name('seeds')->where('seed_name', $seedName)->find()) {
            echo "$seedName ya se ejecutó.\n";
            return;
        }

        Category::create([
            'bs_icon'     => 'laptop',
            'txt_short'   => 'Info',
            'txt_long'    => 'Informática',
            'slug'        => 'informatica',
            'description' => 'Productos relacionados con computadoras y tecnología',
            'pic'         => 'informatica.jpg',
            'bg'          => 'bg-informatica.jpg',
            'visible'     => 1,
        ]);

        Category::create([
            'bs_icon'     => 'cash-register',
            'txt_short'   => 'POS',
            'txt_long'    => 'Punto de Venta',
            'slug'        => 'punto-de-venta',
            'description' => 'Equipos y accesorios para puntos de venta',
            'pic'         => 'pos.jpg',
            'bg'          => 'bg-pos.jpg',
            'visible'     => 1,
        ]);

        Category::create([
            'bs_icon'     => 'house',
            'txt_short'   => 'Hogar',
            'txt_long'    => 'Tecnología Hogar',
            'slug'        => 'hogar',
            'description' => 'Tecnología para el hogar y domótica',
            'pic'         => 'hogar.jpg',
            'bg'          => 'bg-hogar.jpg',
            'visible'     => 1,
        ]);

        Db::name('seeds')->insert([
            'seed_name'   => $seedName,
            'executed_at' => date('Y-m-d H:i:s'),
        ]);

        echo "$seedName ejecutado correctamente.\n";
    }
}
