<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\View;
use think\facade\Session;
use think\facade\Validate;
use think\Request;
use app\model\Category;
use app\validate\CategoryFormValidator;

class CategoryController extends AdminMenuController
{
    public function index()
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        $categories = Category::withTrashed()->order('id desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);

        return View::fetch('admin/category/list', [
            'categories' => $categories,
            'success'    => $successMessage,
            'error'      => $errorMessage,
        ]);
    }

    public function create()
    {
        return View::fetch('/admin/category/create');
    }

    public function save(Request $request)
    {
        $data = $request->post();

        $validate = new CategoryFormValidator();
        if (!$validate->check($data)) {
            return redirect('/admin/category/create')->with('error', $validate->getError());
        }

        try {
            Category::create($data);
            Session::flash('success', 'Categoría creada correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al guardar la categoría: ' . $e->getMessage());
        }

        return redirect('/admin/category/index');
    }

    public function edit(int $id)
    {
        $category = Category::findOrFail($id);
        return View::fetch('/admin/category/edit', ['category' => $category]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->post();
        $category = Category::findOrFail($id);

        $validate = new CategoryFormValidator();

        if ($data['slug'] === $category->slug) {
            $validate->rule([
                'txt_short' => 'require|max:15',
                'slug'      => 'require|max:55',
            ]);
        }

        if (!$validate->check($data)) {
            return redirect('/admin/category/edit?id=' . $id)->with('error', $validate->getError());
        }

        try {
            $category->save($data);
            Session::flash('success', 'Categoría actualizada correctamente.');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al actualizar la categoría: ' . $e->getMessage());
        }

        return redirect('/admin/category/index');
    }

    public function delete(Request $request, int $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return redirect('/admin/category/index')->with('success', 'Categoría eliminada correctamente.');
    }

    public function restore(Request $request, int $id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        return redirect('/admin/category/index')->with('success', 'Categoría restaurada correctamente.');
    }
}
