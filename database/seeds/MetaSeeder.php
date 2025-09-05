<?php

use think\migration\Seeder;
use think\facade\Db;
use app\model\Meta;

class MetaSeeder extends Seeder
{
    public function run(): void
    {
        $metaModel = new Meta();
        $data = [
            [
                'page'        => 'home',
                'title'       => 'Página de Inicio',
                'metatitle'   => 'Inicio - Mi Sitio',
                'description' => 'Bienvenido a la página de inicio',
                'keywords'    => 'inicio,mi sitio,landing',
            ],
            [
                'page'        => 'about',
                'title'       => 'Acerca de Nosotros',
                'metatitle'   => 'Sobre Nosotros - Mi Sitio',
                'description' => 'Información sobre nuestra empresa',
                'keywords'    => 'empresa,información,nosotros',
            ],
        ];

        foreach ($data as $item) {
            // Comprueba si existe el registro por 'page'
            $existing = $metaModel->where('page', $item['page'])->find();
            if ($existing) {
                $metaModel->where('page', $item['page'])->update($item);
            } else {
                $metaModel->save($item);
            }
        }

        // Registrar que este seed se ejecutó
        $seedName = 'MetaSeeder';
        if (!Db::name('seeds')->where('seed_name', $seedName)->find()) {
            Db::name('seeds')->insert([
                'seed_name'   => $seedName,
                'executed_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo "$seedName ejecutado correctamente.\n";
    }
}
