<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\View;
use think\facade\Session;
use think\Request;
use app\model\ProductType;
use app\model\ProductSpecialField;
use app\model\ProductSpecialValue;
use app\validate\ProductTypeFormValidator;
use app\validate\ProductTypeSpecialFieldValidator;

class ProductTypeController extends BaseController
{
    public function index()
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        $productTypes = ProductType::withTrashed()->order('id desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);

        return View::fetch('admin/product_type/list', [
            'productTypes' => $productTypes,
            'success'      => $successMessage,
            'error'        => $errorMessage,
        ]);
    }

    public function create()
    {
        return View::fetch('admin/product_type/create');
    }

    public function updateField(Request $request, int $id)
{
    $field = ProductSpecialField::findOrFail($id);
    $data = $request->post();
    
    // Validar primero que el campo existe
    if (!$field) {
        return redirect('/admin/product_type/index')
            ->with('error', 'El campo no existe');
    }

    $validate = new ProductTypeSpecialFieldValidator();
    
    // Usar la escena de update (quita validación unique)
    if (!$validate->scene('update')->check($data)) {
        return redirect('/admin/product_type/edit?id=' . $field->product_type_id)
            ->with('error', $validate->getError());
    }

    try {
        $field->name = $data['name'];
        $field->slug = $data['slug'];
        $field->data_type = $data['data_type'];
        $field->unit = $data['unit'] ?? null;
        $field->required = isset($data['required']);
        $field->save();

        Session::flash('success', 'Campo actualizado correctamente.');
    } catch (\Exception $e) {
        Session::flash('error', 'Error al actualizar el campo: ' . $e->getMessage());
    }

    return redirect('/admin/product_type/edit?id=' . $field->product_type_id);
}

    /*
    public function updateField(Request $request, int $id)
    {
        $field = ProductSpecialField::findOrFail($id);
        $productTypeId = $request->post('product_type_id');
        $data = $request->post();

        try {
            $field->name = $data['name'];
            $field->slug = $data['slug'];
            $field->data_type = $data['data_type'];
            $field->unit = $data['unit'] ?? null;
            $field->required = isset($data['required']);
            $field->save();

            Session::flash('success', 'Campo actualizado correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al actualizar el campo: ' . $e->getMessage());
        }

        return redirect('/admin/product_type/edit?id=' . $productTypeId);
    }
    */

    public function deleteField(Request $request, int $id)
    {
        $field = ProductSpecialField::findOrFail($id);
        $productTypeId = $request->post('product_type_id');
        
        try {
            // Eliminar también los valores asociados si existen
            ProductSpecialValue::where('special_field_id', $id)->delete();
            
            // Eliminar el campo
            $field->delete();
            
            Session::flash('success', 'Campo eliminado correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al eliminar el campo: ' . $e->getMessage());
        }

        return redirect('/admin/product_type/edit?id=' . $productTypeId);
    }


public function addField(Request $request, int $id)
{
    $data = $request->post();
    
    // Validar primero que el product_type_id existe
    $productType = ProductType::find($id);
    if (!$productType) {
        return redirect('/admin/product_type/edit?id=' . $id)
            ->with('error', 'El tipo de producto no existe');
    }

    $validate = new ProductTypeSpecialFieldValidator();
    if (!$validate->check($data)) {
        return redirect('/admin/product_type/edit?id=' . $id)
            ->with('error', $validate->getError());
    }

    try {
        $field = new ProductSpecialField();
        $field->name = $data['name'];
        $field->slug = $data['slug'];
        $field->data_type = $data['data_type'];
        $field->unit = $data['unit'] ?? null;
        $field->required = isset($data['required']);
        $field->product_type_id = $id; // Usamos el ID de la URL
        $field->save();

        Session::flash('success', 'Campo especializado añadido correctamente.');
    } catch (\Exception $e) {
        Session::flash('error', 'Error al añadir el campo: ' . $e->getMessage());
    }

    return redirect('/admin/product_type/edit?id=' . $id);
}

    /*
    public function addField(Request $request, int $id)
    {
        $productType = ProductType::findOrFail($id);
        $data = $request->post();

        // Validación básica
        if (empty($data['name']) || empty($data['slug']) || empty($data['data_type'])) {
            return redirect('/admin/product_type/edit?id=' . $id)
                ->with('error', 'Nombre, slug y tipo de dato son obligatorios');
        }

        try {
            // Crear el nuevo campo especializado
            $field = new ProductSpecialField();
            $field->name = $data['name'];
            $field->slug = $data['slug'];
            $field->data_type = $data['data_type'];
            $field->unit = $data['unit'] ?? null;
            $field->required = isset($data['required']) ? true : false;
            $field->product_type_id = $id;
            $field->save();

            Session::flash('success', 'Campo especializado añadido correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al añadir el campo: ' . $e->getMessage());
        }

        return redirect('/admin/product_type/edit?id=' . $id);
    }
    */

    public function save(Request $request)
    {
        $data = $request->post();

        $validate = new ProductTypeFormValidator();
        if (!$validate->check($data)) {
            return redirect('/admin/product_type/create')->with('error', $validate->getError());
        }

        try {
            ProductType::create($data);
            Session::flash('success', 'Tipo de producto creado correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al guardar el tipo de producto: ' . $e->getMessage());
        }

        return redirect('/admin/product_type/index');
    }

    public function edit(int $id)
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');
        $productType = ProductType::with('specialFields')->findOrFail($id);
        //$productType = ProductType::findOrFail($id);
        //return View::fetch('admin/product_type/edit', ['productType' => $productType]);
        return View::fetch('admin/product_type/edit', [
            'productType' => $productType,
            'success'      => $successMessage,
            'error'        => $errorMessage,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->post();
        $productType = ProductType::findOrFail($id);


        //dump($data);

        


        $validate = new ProductTypeFormValidator();

        //dump($validate);

        if ($data['slug'] === $productType->slug) {
            $validate->rule([
                'txt_short' => 'require|max:15',
                'slug'      => 'require|max:55',
            ]);
        }


        //dump($data);

        

        if (!$validate->check($data)) {
            //dump($validate->getError());
            //return;
            //Session::flash('error', 'Error al actualizar el tipo: ' . $validate->getError());
            return redirect('/admin/product_type/edit?id=' . $id)->with('error', $validate->getError());
        }

        try {
            $productType->save($data);
            Session::flash('success', 'Tipo de producto actualizado correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al actualizar el tipo de producto: ' . $e->getMessage());
        }

        return redirect('/admin/product_type/index');
    }

    public function delete(Request $request, int $id)
    {
        $productType = ProductType::findOrFail($id);
        $productType->delete();
        return redirect('/admin/product_type/index')->with('success', 'Tipo de producto eliminado correctamente.');
    }

    public function restore(Request $request, int $id)
    {
        $productType = ProductType::onlyTrashed()->findOrFail($id);
        $productType->restore();
        return redirect('/admin/product_type/index')->with('success', 'Tipo de producto restaurado correctamente.');
    }
}
