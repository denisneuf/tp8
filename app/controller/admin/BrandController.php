<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\View;
use think\facade\Session;
use think\facade\Validate;
use think\Request;
use app\model\Brand;
use app\validate\BrandFormValidator;

class BrandController extends AdminMenuController
{
    public function index()
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        $brands = Brand::withTrashed()->order('id desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);

        return View::fetch('admin/brand/list', [
            'brands'  => $brands,
            'success' => $successMessage,
            'error'   => $errorMessage,
        ]);
    }

    public function create()
    {
        return View::fetch('admin/brand/create');
    }

    public function save(Request $request)
    {
        $data = $request->post();

        $validate = new BrandFormValidator();
        if (!$validate->check($data)) {
            return redirect('/admin/brand/create')->with('error', $validate->getError());
        }

        try {
            Brand::create($data);
            Session::flash('success', 'Marca creada correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al guardar la marca: ' . $e->getMessage());
        }

        return redirect('/admin/brand/index');
    }

    public function edit(int $id)
    {
        $brand = Brand::findOrFail($id);
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');



        View::assign('success', $successMessage);
        View::assign('error', $errorMessage);
        View::assign('brand', $brand);
        return View::fetch('admin/brand/edit');

        //return View::fetch('admin/brand/edit', ['brand' => $brand]);
    }


    public function update(Request $request, int $id)
    {

        $brand = Brand::findOrFail($id);
        $data = $request->post();
        
        // Configurar reglas de validación
        $validate = new BrandFormValidator();
        
        // Reglas básicas
        $rules = [
            'brand_en' => 'require|max:100',
            'slug'     => 'require|max:100|unique:brands,slug,' . $id,
            'email'    => 'email',
            'web'      => 'url',
        ];
        
        // Solo validar unicidad si el nombre en inglés cambió
        if ($data['brand_en'] !== $brand->brand_en) {
            $rules['brand_en'] .= '|unique:brands,brand_en';
        }
        
        $validate->rule($rules);

        //dump($validate);

        //return;
        
        if (!$validate->check($data)) {

            //dump($validate->getError());

            //return;

            Session::flash('error', 'Error de validación: ' . $validate->getError());
            return redirect('/admin/brand/edit?id=' . $id);

            //return redirect('/admin/brand/edit?id=' . $id)->with('error', $validate->getError());
        }


            // Verificar archivo de forma segura sin usar file() directamente
        $file = null;
        if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
            $file = $request->file('pic');
        }


        $oldFilename = $brand->pic;

        try {
            // Procesar imagen solo si se subió un archivo válido
            if ($file) {

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
                        return redirect('/admin/brand/edit?id=' . $id);
                    }

                    // Validación avanzada de dimensiones
                    $tempPath = $file->getRealPath();
                    list($width, $height) = getimagesize($tempPath);
                    
                    if ($width !== $height) {
                        Session::flash('error', 'La imagen debe ser cuadrada');
                        return redirect('/admin/brand/edit?id=' . $id);
                    }
                    
                    $minSize = 1500;
                    if ($width < $minSize || $height < $minSize) {
                        Session::flash('error', "La imagen debe ser de al menos {$minSize}x{$minSize} píxeles");
                        return redirect('/admin/brand/edit?id=' . $id);
                    }

                    // Directorios
                    $basePath = app()->getRootPath() . 'public/static/img/';
                    $topicPath = $basePath . 'brand/';
                    
                    if (!is_dir($topicPath)) {
                        mkdir($topicPath, 0755, true);
                    }

                    // Generar nombre basado en ASIN
                    $extension = strtolower($file->getOriginalExtension());


                    $timestamp = time();

                    $newFilename = $data['brand_en'] . $timestamp . '.' . $extension;
                    $originalPath = $topicPath . $newFilename;

                    // Eliminar imágenes antiguas si existen
                    if ($oldFilename) {
                        $this->deleteBrandImage($oldFilename, $topicPath);
                    }

                    // Mover nueva imagen
                    $file->move($topicPath, $newFilename);

                    // Crear miniaturas
                    /*
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
                    */

                    $data['pic'] = $newFilename;

            }

            // Actualizar producto
            Brand::update($data, ['id' => $id]);
            Session::flash('success', 'Marca actualizado correctamente');
            return redirect('/admin/brand/index');
            //return redirect((string)url('product_index'));

            
        } catch (\Exception $e) {
            Session::flash('error', 'Error al actualizar la marca: ' . $e->getMessage());
            return redirect('/admin/brand/edit?id=' . $id);
        }

        
    }


    private function deleteBrandImage($filename, $directory)
    {

        //dump($directory . $filename);

        $path = $directory . $filename;

        
        if (file_exists($path) && is_file($path)) {
            unlink($path);
        }
        
    }

    /*

    public function update(Request $request, int $id)
    {
        $data = $request->post();
        $brand = Brand::findOrFail($id);

        $validate = new BrandFormValidator();

        if ($data['brand_en'] === $brand->brand_en) {
            $validate->rule([
                'brand_en' => 'require|max:100',
                'slug'     => 'require|max:100',
            ]);
        }

        if (!$validate->check($data)) {
            return redirect('/admin/brand/edit?id=' . $id)->with('error', $validate->getError());
        }

        try {
            $brand->save($data);
            Session::flash('success', 'Marca actualizada correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al actualizar la marca: ' . $e->getMessage());
        }

        return redirect('/admin/brand/index');
    }

    */

    public function delete(Request $request, int $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();
        return redirect('/admin/brand/index')->with('success', 'Marca eliminada correctamente.');
    }

    public function restore(Request $request, int $id)
    {
        $brand = Brand::onlyTrashed()->findOrFail($id);
        $brand->restore();
        return redirect('/admin/brand/index')->with('success', 'Marca restaurada correctamente.');
    }
}
