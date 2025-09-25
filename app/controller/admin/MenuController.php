<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\View;
use think\Request;
use think\facade\Session;
use app\model\Menu;
use app\service\MenuService;
use app\validate\MenuFormValidator;
use think\response\Redirect;
use think\db\exception\ModelNotFoundException;
use think\helper\Arr;
use RuntimeException;
use InvalidArgumentException;
use Exception;

class MenuController extends BaseController
{
    private MenuFormValidator $menuValidator;

    public function initialize(): void
    {
        parent::initialize();
        $this->menuValidator = app(MenuFormValidator::class);
    }

    public function index(): string
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        $menus = Menu::withTrashed()->with(['columns.links'])->order('order', 'asc')->paginate([
            'list_rows' => 5,
            'query'     => request()->param(),
        ]);

        View::assign([
            'menus' => $menus,
            'success' => $successMessage,
            'error' => $errorMessage,
            'pagination' => $menus->render(),
        ]);

        return View::fetch('/admin/menu/list');
    }

    public function create(): string
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');
        $oldData = Session::get('old_data');
        $errorField = Session::get('error_field');

        View::assign([
            'old_data' => $oldData,
            'error_field' => $errorField,
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);
        return View::fetch('/admin/menu/create');
    }

    public function save(Request $request): Redirect
    {
        $data = $request->post();
        $cleanData = $data; // ← CORRECCIÓN: Definir al inicio

        // 1. PRIMERO validar (FUERA del try-catch)
        if (!$this->menuValidator->check($data)) {
            $error = $this->menuValidator->getError();
            $errorField = $error['code'];
            $errorMessage = $error['msg'];
            
            // Convertir formato brackets a dot notation
            $dotPath = preg_replace('/\[(\w+)\]/', '.$1', $errorField);
            
            if (Arr::has($cleanData, $dotPath)) {
                Arr::set($cleanData, $dotPath, '');
            }

            return redirect((string) url('menu_create'))
                ->with('old_data', $cleanData)
                ->with('error', $errorMessage)
                ->with('error_field', $errorField);
        }

        // 2. LUEGO procesar guardado (DENTRO del try-catch)
        try {
            MenuService::createMenuWithRelations($data);
            return redirect((string) url('menu_index'))->with('success', 'Menú creado correctamente.'); // ← Corregido mensaje
        } catch (RuntimeException | InvalidArgumentException | Exception $e) {
            return redirect((string) url('menu_create'))
                ->with('old_data', $cleanData)
                ->with('error', $e->getMessage());
        }
    }

    public function edit(int $id): string|Redirect
    {
        $errorMessage = Session::get('error');
        $oldData = Session::get('old_data');
        $errorField = Session::get('error_field');
        try {
            $menu = Menu::with(['columns.links'])->findOrFail($id);
            View::assign([
                'error' => $errorMessage,
                'menu' => $menu,
                'old_data' => $oldData,
                'error_field' => $errorField
            ]);
            return View::fetch('admin/menu/edit');
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('menu_index'))->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, int $id): Redirect // ← Corregido tipo de retorno
    {
        $data = $request->post();
        $cleanData = $data; // ← CORRECCIÓN: Definir al inicio

        // 1. PRIMERO validar (FUERA del try-catch)
        if (!$this->menuValidator->check($data)) {
            $error = $this->menuValidator->getError();
            $errorField = $error['code'];
            $errorMessage = $error['msg'];
            
            $dotPath = preg_replace('/\[(\w+)\]/', '.$1', $errorField);
            
            if (Arr::has($cleanData, $dotPath)) {
                Arr::set($cleanData, $dotPath, '');
            }

            return redirect((string) url('menu_edit', ['id' => $id]))
                ->with('old_data', $cleanData)
                ->with('error', $errorMessage)
                ->with('error_field', $errorField);
        }

        try {
            MenuService::updateMenuWithRelations($id, $data);
            return redirect((string) url('menu_index'))->with('success', 'Menú actualizado correctamente.');
        } catch (RuntimeException | InvalidArgumentException | Exception $e) {
            return redirect((string) url('menu_edit', ['id' => $id]))
                ->with('old_data', $cleanData)
                ->with('error', $e->getMessage());
        }
    }

    public function forceDelete(int $id): Redirect
    {
        try {
            $menu = Menu::onlyTrashed()->findOrFail($id);
            $menu->force()->delete();
            return redirect((string) url('menu_index'))->with('success', 'Menú eliminado permanentemente.');
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('menu_index'))->with('error', 'Menú no encontrado.');
        } catch (Exception $e) {
            return redirect((string) url('menu_index'))->with('error', 'Error al eliminar el menú: ' . $e->getMessage());
        }
    }

    public function delete(int $id): Redirect
    {
        try {
            MenuService::deleteMenuWithRelations($id);
            return redirect((string) url('menu_index'))->with('success', 'Menú eliminado correctamente.');
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('menu_index'))->with('error', 'Menú no encontrado.');
        } catch (Exception $e) {
            return redirect((string) url('menu_index'))->with('error', 'Error al eliminar el menú: ' . $e->getMessage());
        }
    }

    public function restore(int $id): Redirect
    {
        try {
            MenuService::restoreMenuWithRelations($id);
            return redirect((string) url('menu_index'))->with('success', 'Menú restaurado correctamente.');
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('menu_index'))->with('error', 'Menú no encontrado.');
        } catch (Exception $e) {
            return redirect((string) url('menu_index'))->with('error', 'Error al restaurar el menú: ' . $e->getMessage());
        }
    }
}