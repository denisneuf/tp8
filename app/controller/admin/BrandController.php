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
        return View::fetch('admin/brand/edit', ['brand' => $brand]);
    }

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
