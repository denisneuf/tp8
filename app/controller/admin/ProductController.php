<?php
namespace app\controller\admin;

use think\facade\View;
use think\Request;
use app\model\Product;
use app\model\Brand;
use app\model\Category;
use app\model\ProductType;
use app\model\ProductSpecialField;
use app\model\ProductSpecialValue;
use app\validate\ProductFormValidator;
use think\facade\Session;
use think\facade\Filesystem;
use think\Image;

class ProductController extends AdminMenuController
{
    public function index()
    {


        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');


        /*
        $products = Product::with(['brand', 'category', 'productType'])
            ->order('create_time', 'desc')
            ->paginate(20);
        */

        $products = Product::with(['brand', 'category', 'productType'])->select();    

        //dump($products);

        View::assign('products', $products);
        View::assign('success', $successMessage);
        View::assign('error', $errorMessage);

        return View::fetch('admin/product/list');
    }

    public function create()
    {
        $brands = Brand::order('brand_en')->select();
        $categories = Category::order('txt_short')->select();
        $productTypes = ProductType::order('name')->select();

        View::assign([
            'brands' => $brands,
            'categories' => $categories,
            'productTypes' => $productTypes,
            'specialFields' => collect(), // Campos vacíos inicialmente
            'specialValues' => [] // Valores vacíos inicialmente
        ]);

        return View::fetch('admin/product/create');
    }

    /*
    public function create()
    {
        $brands = Brand::order('brand_en')->select();
        $categories = Category::order('txt_short')->select();
        $productTypes = ProductType::order('name')->select();

        View::assign([
            'brands' => $brands,
            'categories' => $categories,
            'productTypes' => $productTypes,
        ]);

        return View::fetch('admin/product/create');
    }
    */


    public function getSpecialFields(Request $request)
    {
        $productTypeId = $request->get('product_type_id');
        
        if (!$productTypeId) {
            return '<p class="text-muted">Selecciona un tipo de producto</p>';
        }
        
        $fields = ProductSpecialField::where('product_type_id', $productTypeId)->select();
        
        if ($fields->isEmpty()) {
            return '<p class="text-muted">Este tipo de producto no tiene campos especializados</p>';
        }
        
        return View::fetch('admin/product/special_fields_form', [
            'fields' => $fields,
            'values' => [] // Valores vacíos para creación
        ]);
    }


    public function save(Request $request)
    {
        $data = $request->post();

        // Validación del formulario
        $validate = new ProductFormValidator();
        if (!$validate->check($data)) {
            Session::flash('error', $validate->getError());
            return redirect('/admin/product/create');
        }

        $file = $request->file('pic');

        if ($file) {
            try {
                // Validación básica del archivo
                $fileValidate = validate([
                    'pic' => [
                        'file', 
                        'fileExt' => 'jpg,jpeg,png,gif,webp',
                        'fileSize' => 5242880, // 5MB para imágenes grandes
                    ]
                ]);
                
                if (!$fileValidate->check(['pic' => $file])) {
                    Session::flash('error', 'Error de validación de imagen: ' . $fileValidate->getError());
                    return redirect('/admin/product/create');
                }

                // Validación avanzada de dimensiones
                $tempPath = $file->getRealPath();
                list($width, $height) = getimagesize($tempPath);
                
                // Validar que sea cuadrada
                if ($width !== $height) {
                    Session::flash('error', 'La imagen debe ser cuadrada (mismo ancho y alto)');
                    return redirect('/admin/product/create');
                }
                
                // Validar tamaño mínimo
                $minSize = 1500;
                if ($width < $minSize || $height < $minSize) {
                    Session::flash('error', "La imagen debe ser de al menos {$minSize}x{$minSize} píxeles");
                    return redirect('/admin/product/create');
                }

                // Verificar y crear directorios
                $basePath = app()->getRootPath() . 'public/static/img/';
                $topicPath = $basePath . 'product/';
                
                if (!is_dir($basePath)) mkdir($basePath, 0755, true);
                if (!is_dir($topicPath)) mkdir($topicPath, 0755, true);
                if (!is_writable($topicPath)) chmod($topicPath, 0755);

                // Generar nombre único basado en ASIN
                $extension = strtolower($file->getOriginalExtension());
                $filename = $data['asin'] . '.' . $extension;
                $originalPath = $topicPath . $filename;

                // Mover el archivo original
                $file->move($topicPath, $filename);

                // Crear miniaturas en diferentes tamaños
                $sizes = [
                    'large' => [800, 800],      // Página de producto
                    'medium' => [350, 350],     // Fichas de productos
                    'small' => [35, 35],        // Listados admin
                    'thumb' => [150, 150]       // Miniaturas generales
                ];
                
                foreach ($sizes as $sizeName => $dimensions) {
                    $sizeFilename = $sizeName . '_' . $filename;
                    $sizePath = $topicPath . $sizeFilename;
                    
                    $image = Image::open($originalPath);
                    $image->thumb($dimensions[0], $dimensions[1], Image::THUMB_CENTER)
                          ->save($sizePath);
                }

                // Guardar solo el nombre del archivo original en la BD
                $data['pic'] = $filename;

            } catch (\Exception $e) {
                Session::flash('error', 'Error al procesar la imagen: ' . $e->getMessage());
                return redirect('/admin/product/create');
            }
        }

        // Guardar el producto en la base de datos
        try {
            
            Product::create($data);


            // Guardar atributos especiales si existen
            $special = $request->post('special', []);
            $productId = Product::getLastInsID(); // Obtener el ID del producto recién creado

            foreach ($special as $fieldId => $value) {
                if (!empty($value)) {
                    ProductSpecialValue::create([
                        'product_id' => $productId,
                        'special_field_id' => $fieldId,
                        'value' => $value
                    ]);
                }
            }



            Session::flash('success', 'Producto creado correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al guardar el producto: ' . $e->getMessage());
        }

        return redirect('/admin/product/index');
    }

    /*
    public function save(Request $request)
    {
        $data = $request->post();

        // Validación del formulario
        $validate = new ProductFormValidator();
        if (!$validate->check($data)) {
            Session::flash('error', $validate->getError());
            return redirect('/admin/product/create');
        }

        $file = $request->file('pic');

        if ($file) {
            try {
                // Validación básica del archivo
                $fileValidate = validate([
                    'pic' => [
                        'file', 
                        'fileExt' => 'jpg,jpeg,png,gif,webp',
                        'fileSize' => 5242880, // 5MB para imágenes grandes
                    ]
                ]);
                
                if (!$fileValidate->check(['pic' => $file])) {
                    Session::flash('error', 'Error de validación de imagen: ' . $fileValidate->getError());
                    return redirect('/admin/product/create');
                }

                // Validación avanzada de dimensiones
                $tempPath = $file->getRealPath();
                list($width, $height) = getimagesize($tempPath);
                
                // Validar que sea cuadrada
                if ($width !== $height) {
                    Session::flash('error', 'La imagen debe ser cuadrada (mismo ancho y alto)');
                    return redirect('/admin/product/create');
                }
                
                // Validar tamaño mínimo
                $minSize = 1500;
                if ($width < $minSize || $height < $minSize) {
                    Session::flash('error', "La imagen debe ser de al menos {$minSize}x{$minSize} píxeles");
                    return redirect('/admin/product/create');
                }

                // Verificar y crear directorios
                $basePath = app()->getRootPath() . 'public/static/img/';
                $topicPath = $basePath . 'product/';
                
                if (!is_dir($basePath)) mkdir($basePath, 0755, true);
                if (!is_dir($topicPath)) mkdir($topicPath, 0755, true);
                if (!is_writable($topicPath)) chmod($topicPath, 0755);

                // Generar nombre único basado en ASIN
                $extension = strtolower($file->getOriginalExtension());
                $filename = $data['asin'] . '.' . $extension;
                $originalPath = $topicPath . $filename;

                // Mover el archivo original
                $file->move($topicPath, $filename);

                // Crear miniaturas en diferentes tamaños
                $sizes = [
                    'large' => [800, 800],      // Página de producto
                    'medium' => [350, 350],     // Fichas de productos
                    'small' => [35, 35],        // Listados admin
                    'thumb' => [150, 150]       // Miniaturas generales
                ];
                
                foreach ($sizes as $sizeName => $dimensions) {
                    $sizeFilename = $sizeName . '_' . $filename;
                    $sizePath = $topicPath . $sizeFilename;
                    
                    $image = Image::open($originalPath);
                    $image->thumb($dimensions[0], $dimensions[1], Image::THUMB_CENTER)
                          ->save($sizePath);
                }

                // Guardar solo el nombre del archivo original en la BD
                $data['pic'] = $filename;

            } catch (\Exception $e) {
                Session::flash('error', 'Error al procesar la imagen: ' . $e->getMessage());
                return redirect('/admin/product/create');
            }
        }

        // Guardar el producto en la base de datos
        try {
            Product::create($data);
            Session::flash('success', 'Producto creado correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al guardar el producto: ' . $e->getMessage());
        }

        return redirect('/admin/product/index');
    }
    */

    public function edit($id)
    {


        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        $product = Product::with(['brand', 'category', 'productType'])->find($id);
        $brands = Brand::order('brand_en')->select();
        $categories = Category::order('txt_short')->select();
        $productTypes = ProductType::order('name')->select();

        //dump($product);

        if (!$product) {
            return View::fetch('error/404');
        }


        //dump($product->product_type_id);
        // Obtener campos especiales según el tipo de producto
        $specialFields = ProductSpecialField::where('product_type_id', $product->product_type_id)->select();

        //dump($specialFields);

        // Obtener valores actuales del producto
        $specialValues = ProductSpecialValue::where('product_id', $id)->column('value', 'special_field_id');

        //dump($specialValues);


        View::assign('product', $product);
        View::assign('brands', $brands);
        View::assign('categories', $categories);
        View::assign('productTypes', $productTypes);


        View::assign('specialFields', $specialFields);
        View::assign('specialValues', $specialValues);

        View::assign('success', $successMessage);
        View::assign('error', $errorMessage);
        return View::fetch('admin/product/edit');
    }


public function update(Request $request, $id)
{
    $data = $request->post();

    $validate = new ProductFormValidator();
    if (!$validate->check($data)) {
        Session::flash('error', $validate->getError());
        return redirect('/admin/product/edit?id=' . $id);
    }

    // Verificar archivo de forma segura sin usar file() directamente
    $file = null;
    if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
        $file = $request->file('pic');
    }

    $product = Product::find($id);
    $oldFilename = $product->pic;

    try {
        // Procesar imagen solo si se subió un archivo válido
        if ($file) {
            try {
                // Validación básica del archivo
                $fileValidate = validate([
                    'pic' => [
                        'file', 
                        'fileExt' => 'jpg,jpeg,png,gif,webp',
                        'fileSize' => 5242880,
                    ]
                ]);
                
                if (!$fileValidate->check(['pic' => $file])) {
                    Session::flash('error', 'Error de validación de imagen: ' . $fileValidate->getError());
                    return redirect('/admin/product/edit?id=' . $id);
                }

                // Validación avanzada de dimensiones
                $tempPath = $file->getRealPath();
                list($width, $height) = getimagesize($tempPath);
                
                if ($width !== $height) {
                    Session::flash('error', 'La imagen debe ser cuadrada');
                    return redirect('/admin/product/edit?id=' . $id);
                }
                
                $minSize = 1500;
                if ($width < $minSize || $height < $minSize) {
                    Session::flash('error', "La imagen debe ser de al menos {$minSize}x{$minSize} píxeles");
                    return redirect('/admin/product/edit?id=' . $id);
                }

                // Directorios
                $basePath = app()->getRootPath() . 'public/static/img/';
                $topicPath = $basePath . 'product/';
                
                if (!is_dir($topicPath)) {
                    mkdir($topicPath, 0755, true);
                }

                // Generar nombre basado en ASIN
                $extension = strtolower($file->getOriginalExtension());
                $newFilename = $data['asin'] . '.' . $extension;
                $originalPath = $topicPath . $newFilename;

                // Eliminar imágenes antiguas si existen
                if ($oldFilename) {
                    $this->deleteProductImages($oldFilename, $topicPath);
                }

                // Mover nueva imagen
                $file->move($topicPath, $newFilename);

                // Crear miniaturas
                $sizes = [
                    'large' => [800, 800],
                    'medium' => [350, 350],
                    'small' => [35, 35],
                    'thumb' => [150, 150]
                ];
                
                foreach ($sizes as $sizeName => $dimensions) {
                    $sizeFilename = $sizeName . '_' . $newFilename;
                    $sizePath = $topicPath . $sizeFilename;
                    
                    $image = Image::open($originalPath);
                    $image->thumb($dimensions[0], $dimensions[1], Image::THUMB_CENTER)
                          ->save($sizePath);
                }

                $data['pic'] = $newFilename;

            } catch (\Exception $e) {
                Session::flash('error', 'Error al procesar la imagen: ' . $e->getMessage());
                return redirect('/admin/product/edit?id=' . $id);
            }
        } else {
            // Si no se subió nueva imagen, mantener la existente
            $data['pic'] = $oldFilename;
        }

        // Actualizar producto
        Product::update($data, ['id' => $id]);

        // Guardar atributos especiales
        $special = $request->post('special', []);
        foreach ($special as $fieldId => $value) {
            $existing = ProductSpecialValue::where([
                'product_id' => $id,
                'special_field_id' => $fieldId
            ])->find();

            if ($existing) {
                $existing->value = $value;
                $existing->save();
            } else {
                ProductSpecialValue::create([
                    'product_id' => $id,
                    'special_field_id' => $fieldId,
                    'value' => $value
                ]);
            }
        }

        Session::flash('success', 'Producto actualizado correctamente');
        return redirect((string)url('product_index'));

    } catch (\Exception $e) {
        Session::flash('error', 'Error al actualizar el producto: ' . $e->getMessage());
        return redirect('/admin/product/edit?id=' . $id);
    }
}

    /**
     * Método auxiliar para eliminar imágenes antiguas
     */
    private function deleteProductImages($filename, $directory)
    {
        $filesToDelete = [
            $directory . $filename,           // original
            $directory . 'large_' . $filename,
            $directory . 'medium_' . $filename,
            $directory . 'small_' . $filename,
            $directory . 'thumb_' . $filename
        ];

        foreach ($filesToDelete as $filePath) {
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }
    }


    public function delete($id)
    {
        try {
            Product::destroy($id);
            return json(['status' => 'success', 'message' => 'Producto eliminado']);
        } catch (\Exception $e) {
            return json(['status' => 'error', 'message' => 'Error al eliminar el producto']);
        }
    }

    public function restore($id)
    {
        try {
            $product = Product::onlyTrashed()->find($id);
            if ($product) {
                $product->restore();
                return json(['status' => 'success', 'message' => 'Producto restaurado']);
            }
            return json(['status' => 'error', 'message' => 'Producto no encontrado']);
        } catch (\Exception $e) {
            return json(['status' => 'error', 'message' => 'Error al restaurar el producto']);
        }
    }
}
