<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\View;
use think\Request;
use think\facade\Session;
use think\facade\Validate;
use think\exception\ValidateException;
use app\model\Menu;
use app\model\MenuColumn;
use app\model\MenuLink;
use think\facade\Db;
use app\service\MenuService;
use app\validate\MenuFormValidator;


class MenuController extends BaseController
{
    public function index()
    {
        $menus = Menu::withTrashed()->with(['columns.links'])->order('order', 'asc')->paginate([
        //$menus = Menu::with(['columns.links'])->order('order', 'asc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);

        return view('admin/menu/list', [
            'menus' => $menus,
            'success' => Session::get('success'),
            'error' => Session::get('error'),
            'pagination' => $menus->render(), // ✅ render() como método
        ]);
    }

    public function create()
    {
        return View::fetch('admin/menu/create');
    }


    public function save(Request $request)
    {
        $data = $request->post();

        try {
            MenuService::createMenuWithRelations($data);
            Session::flash('success', 'Menú creado correctamente');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al guardar el menú: ' . $e->getMessage());
        }

        return redirect('/admin/menu/index');
    }


    /*
    public function save(Request $request)
    {
        $data = $request->post();

        try {
            $menu = Menu::create([
                'title' => $data['title'],
                'url' => $data['url'],
                'has_submenu' => !empty($data['columns']),
                'order' => $data['order'] ?? 0,
                'visible' => $data['visible'] ?? 1,
            ]);

            if (!empty($data['columns'])) {
                foreach ($data['columns'] as $columnData) {
                    $column = $menu->columns()->create([
                        'title' => $columnData['title'],
                        'order' => $columnData['order'] ?? 0,
                    ]);

                    if (!empty($columnData['links'])) {
                        foreach ($columnData['links'] as $linkData) {
                            $column->links()->create([
                                'title' => $linkData['title'],
                                'url' => $linkData['url'],
                                'order' => $linkData['order'] ?? 0,
                            ]);
                        }
                    }
                }
            }

            Session::flash('success', 'Menú creado correctamente');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al guardar el menú: ' . $e->getMessage());
        }

        return redirect('/admin/menu/index');
    }

    */

    public function edit($id)
    {
        $menu = Menu::with(['columns.links'])->findOrFail($id);
        return View::fetch('admin/menu/edit', 
            [
                'menu' => $menu,
                'error' => Session::get('error'),
            ]);
    }



    public function update(Request $request, $id)
    {
        $data = $request->post();

        dump($id);
        dump($data);


        if (!is_numeric($id)) {
            return json(['error' => 'ID inválido']);
        }


  
        


        $validate = new MenuFormValidator();
        $validate->batch(true); // ← esto es clave

        if (!$validate->check($data)) {
            $errors = $validate->getError(); // ← Aquí definimos la variable correctamente

            dump($errors);
            
            Session::flash('error', is_array($errors) ? implode("\n", $errors) : $errors);

            //return redirect('/admin/menu/edit/' . $id);
            //return redirect('/admin/menu/index');

            return redirect(url('/admin/menu/edit', ['id' => $id])->build());


        }

        



        try {
            MenuService::updateMenuWithRelations($id, $data);
            Session::flash('success', 'Menú actualizado correctamente');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al actualizar el menú: ' . $e->getMessage());
        }

        return redirect('/admin/menu/index');
    }


    /*

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $data = $request->post();

        Db::startTrans();
        try {
            // Actualizar el menú
            $menu->save([
                'title' => $data['title'],
                'url' => $data['url'],
                'has_submenu' => !empty($data['columns']),
                'order' => $data['order'] ?? 0,
                'visible' => $data['visible'] ?? 1,
            ]);

            $columnIds = [];
            if (!empty($data['columns'])) {
                foreach ($data['columns'] as $colData) {
                    $column = !empty($colData['id']) 
                        ? MenuColumn::where('menu_id', $menu->id)->find($colData['id']) 
                        : new MenuColumn();

                    if (!$column) {
                        $column = new MenuColumn();
                    }

                    $column->menu_id = $menu->id;
                    $column->title = $colData['title'];
                    $column->order = $colData['order'] ?? 0;
                    $column->save();
                    $columnIds[] = $column->id;

                    $linkIds = [];
                    if (!empty($colData['links'])) {
                        foreach ($colData['links'] as $linkData) {
                            $link = !empty($linkData['id']) 
                                ? MenuLink::where('column_id', $column->id)->find($linkData['id']) 
                                : new MenuLink();

                            if (!$link) {
                                $link = new MenuLink();
                            }

                            $link->column_id = $column->id;
                            $link->title = $linkData['title'];
                            $link->url = $linkData['url'];
                            $link->order = $linkData['order'] ?? 0;
                            $link->save();
                            $linkIds[] = $link->id;
                        }

                        // Eliminar enlaces que ya no están
                        MenuLink::where('column_id', $column->id)
                            ->whereNotIn('id', $linkIds)
                            ->delete();
                    }
                }

                // Eliminar columnas que ya no están
                MenuColumn::where('menu_id', $menu->id)
                    ->whereNotIn('id', $columnIds)
                    ->delete();
            } else {
                // Si no hay columnas, eliminar todas las existentes
                MenuColumn::where('menu_id', $menu->id)->delete();
            }

            Db::commit();
            Session::flash('success', 'Menú actualizado correctamente');
        } catch (\Exception $e) {
            Db::rollback();
            Session::flash('error', 'Error al actualizar el menú: ' . $e->getMessage());
        }

        return redirect('/admin/menu/index');
    }

    */


    public function delete($id)
    {

        try {
            MenuService::deleteMenuWithRelations($id);
            Session::flash('success', 'Menú eliminado correctamente');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al eliminar el menú: ' . $e->getMessage());
        }

        return redirect('/admin/menu/index');
    }

    /*
    public function restore(Request $request, $id)
    {
        $request->checkToken('__token__');
        $menu = Menu::onlyTrashed()->findOrFail($id);
        $menu->restore();
        return redirect('/admin/menu/index')->with('success', 'Menú restaurado correctamente');
    }
    */


    public function restore($id)
    {
        try {
            MenuService::restoreMenuWithRelations($id);
            Session::flash('success', 'Menú restaurado correctamente');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al restaurar el menú: ' . $e->getMessage());
        }

        return redirect('/admin/menu/index');
    }


}
