<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\View;
use think\facade\Session;
use think\facade\Validate;
use think\Request;
use app\model\Category;
use app\validate\CategoryFormValidator;
use think\response\Redirect;
use think\db\exception\ModelNotFoundException;

class CategoryController extends BaseController
{
    /**
     * @var CategoryFormValidator
     */
    private CategoryFormValidator $categoryValidator;

    public function initialize(): void
    {
        parent::initialize();
        // Usar el contenedor de dependencias de ThinkPHP
        $this->categoryValidator = app(CategoryFormValidator::class);
    }

    public function index(): string
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

    public function create(): string
    {
        return View::fetch('/admin/category/create');
    }

    public function save(Request $request): Redirect
    {
        $data = $request->post();

        if (!$this->categoryValidator->check($data)) {
            return redirect((string) url('category_create'))->with('error', $this->categoryValidator->getError());
        }

        try {
            Category::create($data);
            return redirect((string) url('category_index'))->with('success', 'Categoría creada correctamente.');
        } catch (\Exception $e) {
            return redirect((string) url('category_create'))->with('error', $e->getMessage());
        }

        return redirect('/admin/category/index');
    }

    public function edit(int $id): string|Redirect
    {

        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');


        try {
            $category = Category::findOrFail($id);
            View::assign('success', $successMessage);
            View::assign('error', $errorMessage);
            View::assign('category', $category);
            return View::fetch('admin/category/edit');
            
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('category_index'))->with('error', $e->getMessage());
        }

        //return View::fetch('/admin/category/edit', ['category' => $category]);
    }

    public function update(Request $request, int $id): string|Redirect
    {
        $data = $request->post();
        $category = Category::findOrFail($id);


        if ($data['slug'] === $category->slug) {
            $this->categoryValidator->rule([
                'txt_short' => 'require|max:15',
                'slug'      => 'require|max:55',
            ]);
        }

        if (!$this->categoryValidator->check($data)) {
            return redirect((string) url('category_edit', ['id' => $id]))->with('error', $this->categoryValidator->getError());
        }

        try {
            $category->save($data);
            return redirect((string) url('category_index'))->with('success', 'actualizada actualizada correctamente.');
        } catch (\Exception $e) {
            return redirect((string) url('category_edit', ['id' => $id]))->with('error', $e->getMessage());
        }

        return redirect('/admin/category/index');
    }

    public function delete(Request $request, int $id): Redirect
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return redirect((string) url('category_index'))->with('success', 'Categoría eliminada correctamente.');
    }

    public function restore(Request $request, int $id): Redirect
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        return redirect((string) url('category_index'))->with('success', 'Categoría restaurada correctamente.');
    }
}
