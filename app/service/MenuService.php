<?php
declare (strict_types = 1);

namespace app\service;

use app\model\Menu;
use app\model\MenuColumn;
use app\model\MenuLink;
use think\facade\Db;

class MenuService
{

    public function register()
    {
        //
    }

    /**
     * 执行服务
     *
     * @return mixed
     */
    public function boot()
    {
        //
    }


    public static function restoreMenuWithRelations($id): void
    {
        Db::startTrans();
        try {
            $menu = Menu::onlyTrashed()->find($id);
            if (!$menu) {
                throw new \Exception('Menú no encontrado o no está eliminado');
            }

            $menu->restore();

            // Restaurar columnas relacionadas
            $columns = MenuColumn::onlyTrashed()->where('menu_id', $id)->select();
            foreach ($columns as $column) {
                $column->restore();

                // Restaurar enlaces relacionados
                $links = MenuLink::onlyTrashed()->where('column_id', $column->id)->select();
                foreach ($links as $link) {
                    $link->restore();
                }
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }



    public static function deleteMenuWithRelations($id): bool
    {
        Db::startTrans();
        try {
            $menu = Menu::with(['columns.links'])->findOrFail($id);

            // Soft delete de enlaces
            foreach ($menu->columns as $column) {
                foreach ($column->links as $link) {
                    $link->delete(); // soft delete
                }
                $column->delete(); // soft delete
            }

            // Soft delete del menú
            $menu->delete();

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }


    /*
    public static function deleteMenuWithRelations($id): bool
    {
        Db::startTrans();
        try {
            $menu = Menu::findOrFail($id);

            // Eliminar enlaces
            foreach ($menu->columns as $column) {
                MenuLink::where('column_id', $column->id)->delete();
            }

            // Eliminar columnas
            MenuColumn::where('menu_id', $menu->id)->delete();

            // Eliminar menú
            $menu->delete();

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    */


public static function createMenuWithRelations(array $data): Menu
{
    Db::startTrans();
    try {
        $menu = Menu::create([
            'title' => $data['title'],
            'url' => $data['url'],
            'has_submenu' => !empty($data['columns']),
            'order' => $data['order'] ?? 0,
            'visible' => $data['visible'] ?? 1,
        ]);

        if (!empty($data['columns'])) {
            $columnsData = [];
            
            foreach ($data['columns'] as $columnData) {
                $columnItem = [
                    'menu_id' => $menu->id,
                    'title' => $columnData['title'],
                    'order' => $columnData['order'] ?? 0,
                ];

                // Si hay links, prepararlos también
                if (!empty($columnData['links'])) {
                    $linksData = [];
                    foreach ($columnData['links'] as $linkData) {
                        $linksData[] = [
                            'column_id' => null, // Se asignará después
                            'title' => $linkData['title'],
                            'url' => $linkData['url'],
                            'order' => $linkData['order'] ?? 0,
                        ];
                    }
                    $columnItem['links'] = $linksData;
                }

                $columnsData[] = $columnItem;
            }

            // Guardar todas las columnas
            $menu->columns()->saveAll($columnsData);

            // Para los links anidados, necesitamos guardarlos después
            foreach ($data['columns'] as $index => $columnData) {
                if (!empty($columnData['links'])) {
                    $column = $menu->columns[$index]; // La columna recién creada
                    $linksData = [];
                    
                    foreach ($columnData['links'] as $linkData) {
                        $linksData[] = [
                            'column_id' => $column->id,
                            'title' => $linkData['title'],
                            'url' => $linkData['url'],
                            'order' => $linkData['order'] ?? 0,
                        ];
                    }
                    
                    $column->links()->saveAll($linksData);
                }
            }
        }

        Db::commit();
        return $menu;
    } catch (\Exception $e) {
        Db::rollback();
        throw $e;
    }
}

    /*
    public static function createMenuWithRelations(array $data): Menu
    {
        Db::startTrans();
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

            Db::commit();
            return $menu;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    */

    public static function updateMenuWithRelations($id, array $data): bool
    {
        $menu = Menu::findOrFail($id);

        Db::startTrans();
        try {
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

                        MenuLink::where('column_id', $column->id)
                            ->whereNotIn('id', $linkIds)
                            ->delete();
                    }
                }

                MenuColumn::where('menu_id', $menu->id)
                    ->whereNotIn('id', $columnIds)
                    ->delete();
            } else {
                MenuColumn::where('menu_id', $menu->id)->delete();
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

}
