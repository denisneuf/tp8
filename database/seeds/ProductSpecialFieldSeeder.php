
<?php

use think\migration\Seeder;
use think\facade\Db;
use app\model\ProductSpecialField;

class ProductSpecialFieldSeeder extends Seeder
{
    public function run(): void
    {
        $seedName = 'ProductSpecialFieldSeeder';

        // Verificar si ya se ejecutó
        if (Db::name('seeds')->where('seed_name', $seedName)->find()) {
            echo "$seedName ya se ejecutó.";
            return;
        }

        $monitorFields = [
            ['name' => 'Inches', 'slug' => 'inches', 'unit' => 'in', 'data_type' => 'float', 'required' => true, 'product_type_id' => 2],
            ['name' => 'Resolution', 'slug' => 'resolution', 'unit' => 'px', 'data_type' => 'string', 'required' => true, 'product_type_id' => 2],
            ['name' => 'Panel Type', 'slug' => 'panel_type', 'unit' => null, 'data_type' => 'string', 'required' => false, 'product_type_id' => 2],
            ['name' => 'Refresh Rate', 'slug' => 'refresh_rate', 'unit' => 'Hz', 'data_type' => 'integer', 'required' => false, 'product_type_id' => 2],
            ['name' => 'Brightness', 'slug' => 'brightness', 'unit' => 'cd/m²', 'data_type' => 'integer', 'required' => false, 'product_type_id' => 2],
            ['name' => 'Ports', 'slug' => 'ports', 'unit' => null, 'data_type' => 'string', 'required' => false, 'product_type_id' => 2],
            ['name' => 'Is Curved', 'slug' => 'is_curved', 'unit' => null, 'data_type' => 'boolean', 'required' => false, 'product_type_id' => 2],
            ['name' => 'Response Time', 'slug' => 'response_time', 'unit' => 'ms', 'data_type' => 'float', 'required' => false, 'product_type_id' => 2],
        ];

        $computerFields = [
            ['name' => 'Processor', 'slug' => 'processor', 'unit' => null, 'data_type' => 'string', 'required' => true, 'product_type_id' => 1],
            ['name' => 'Processor Brand', 'slug' => 'processor_brand', 'unit' => null, 'data_type' => 'string', 'required' => false, 'product_type_id' => 1],
            ['name' => 'RAM', 'slug' => 'ram', 'unit' => 'GB', 'data_type' => 'integer', 'required' => true, 'product_type_id' => 1],
            ['name' => 'Storage', 'slug' => 'storage', 'unit' => 'GB/TB', 'data_type' => 'string', 'required' => true, 'product_type_id' => 1],
            ['name' => 'GPU', 'slug' => 'gpu', 'unit' => null, 'data_type' => 'string', 'required' => false, 'product_type_id' => 1],
            ['name' => 'GPU Brand', 'slug' => 'gpu_brand', 'unit' => null, 'data_type' => 'string', 'required' => false, 'product_type_id' => 1],
            ['name' => 'Operating System', 'slug' => 'os', 'unit' => null, 'data_type' => 'string', 'required' => false, 'product_type_id' => 1],
            ['name' => 'Ports', 'slug' => 'ports', 'unit' => null, 'data_type' => 'string', 'required' => false, 'product_type_id' => 1],
            ['name' => 'Power Supply', 'slug' => 'power_supply', 'unit' => 'W', 'data_type' => 'integer', 'required' => false, 'product_type_id' => 1],
        ];

        foreach (array_merge($monitorFields, $computerFields) as $field) {
            ProductSpecialField::create($field);
        }

        // Registrar ejecución en la tabla seeds
        Db::name('seeds')->insert([
            'seed_name' => $seedName,
            'executed_at' => date('Y-m-d H:i:s'),
        ]);

        echo "$seedName ejecutado correctamente.";
    }
}
