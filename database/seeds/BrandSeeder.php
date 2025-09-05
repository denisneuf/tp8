<?php
declare(strict_types=1);

use think\migration\Seeder;
use app\model\Brand;
use think\facade\Db;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $seedName = 'BrandSeeder';

        if (Db::name('seeds')->where('seed_name', $seedName)->find()) {
            echo "$seedName ya se ejecutó.\n";
            return;
        }

        Brand::create([
            'id' => '1',
            'pic' => 'autel-ZviKqk.jpg',
            'brand_en' => 'Autel',
            'brand_cn' => '道通',
            'slug' => 'autel',
            'meta_title' => 'LeadTech > Marca Autel',
            'meta_description' => 'LeadTech comercializa productos Autel especialmente las gamas Autolink',
            'keywords' => 'Maxysis',
            'txt_description' => 'en sus versiones Mini',
            'block_description' => 'Pro y Elite y los modelos gama profesional MaxiDas. Compra tus productos originales Autel con LeadTech',
            'block_pic' => 'Autel diagnostico',
            'email' => 'DIY',
            'telephone' => 'usuarios',
            'direccion' => 'diagnostico',
            'fax' => 'multimarca',
            'web' => 'eobd',
        ]);

        Brand::create([
            'id' => '2',
            'pic' => 'launch-zbshKl.jpg',
            'brand_en' => 'Launch',
            'brand_cn' => '元征',
            'slug' => 'launch',
            'meta_title' => 'LeadTech > Marca Launch',
            'meta_description' => 'LeadTech comercializa productos Launch',
            'keywords' => 'las series CRP 123',
            'txt_description' => 'CRP 129 y C Reader series. Los tablets de gama alta X431 V* y V. Oferta para talleres.',
            'block_description' => 'X431',
            'block_pic' => 'launch',
            'email' => 'diagnostico',
            'telephone' => 'multimarca',
            'direccion' => 'code reader',
            'fax' => 'profesional',
            'web' => '<p><b>Launch Tech Co.',
        ]);

        Brand::create([
            'id' => '3',
            'pic' => 'thinkcar-VreoHD.jpg',
            'brand_en' => 'Thinkcar',
            'brand_cn' => '星卡',
            'slug' => 'thinkcar',
            'meta_title' => 'LeadTech >> Thinkcar',
            'meta_description' => 'ThinkDiag is designed and implemented by ThinkCar. ThinkCar was established in Los Angeles',
            'keywords' => 'California. It was founded by a group of car enthusiasts who have a passion for car modification and are proficient in car maintenance.',
            'txt_description' => 'thinkcar thinkdiag thinkobd 100 think driver think tool',
            'block_description' => '<p><b>ThinkDiag</b> by <i>Thinkcar</i> está diseñado e implementado por ThinkCar. ThinkCar se estableció en Los Ángeles',
            'block_pic' => 'California. Fue fundada por un grupo de entusiastas de los automóviles que sienten pasión por la modificación de automóviles y son expertos en el mantenimiento de automóviles.</p>',
            'email' => 'Thinkcar',
            'telephone' => 'thinkcar-block-572x378-hqHriK.jpg',
            'direccion' => null,
            'fax' => null,
            'web' => null,
        ]);

        Brand::create([
            'id' => '4',
            'pic' => 'topdon-FZqAlu.jpg',
            'brand_en' => 'Topdon',
            'brand_cn' => '顶匠',
            'slug' => 'topdon',
            'meta_title' => 'Leadtech >> Topdon',
            'meta_description' => 'topdon Obd Product',
            'keywords' => 'topdon Obd Product',
            'txt_description' => '<p><b>TOPDON</b> es una marca de tecnología que es sinónimo de innovación y alto rendimiento para técnicos automotrices',
            'block_description' => 'y respaldada por un equipo profesional para ofrecer productos y servicios de alta calidad en la industria. Se centra en modelos de diagnosis OBD y también en productos para la medición y arranque de baterías.</p>',
            'block_pic' => 'TOPDON es una marca de tecnología que es sinónimo de innovación y alto rendimiento para técnicos automotrices',
            'email' => 'sssssss',
            'telephone' => 'topdon-block-572x378-mnxyMH.jpg',
            'direccion' => null,
            'fax' => null,
            'web' => null,
        ]);


        Db::name('seeds')->insert([

            'seed_name' => $seedName,

            'executed_at' => date('Y-m-d H:i:s'),

        ]);

        echo "$seedName ejecutado correctamente.\n";

    }

}
