<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\View;
use think\Request;
use think\facade\Db;
use app\model\Meta;
use think\facade\Validate;
use think\facade\Session;
use think\exception\PDOException;
use think\db\exception\DuplicateException;



class MetaController extends AdminMenuController
{
    // Mostrar listado

    /*
    public function initialize()
    {
        parent::initialize();
    }
    */


    public function index()
    {
        //$this->initialize(); // <<-- esto es obligatorio
        $successMessage = Session::get('success');
        //dump($successMessage);
        $errorMessage = Session::get('error');
        //dump($errorMessage);
        $metas = Meta::order('id', 'desc')->select();
        //return View::fetch('list', ['metas' => $metas]);
        return view('admin/meta/list', 
            [
                'metas' => $metas,
                'error' => $errorMessage,
                'success' => $successMessage,

            ]);
    }

    // Mostrar formulario de creación
    public function create()
    {
        return View::fetch('admin/meta/create'); // misma vista pero campos vacíos
    }

    // Guardar creación
    public function save(Request $request)
    {
        // Reglas de validación
        $rules = [
            'page'        => 'require|max:100',
            'title'       => 'require|max:70',
            'metatitle'   => 'max:70',
            'description' => 'max:160',
            'keywords'    => 'max:255',
        ];

        // Mensajes personalizados (opcional)
        $messages = [
            'page.require'        => 'El campo página es obligatorio',
            'page.max'            => 'El campo página no puede superar 100 caracteres',
            'title.require'       => 'El título es obligatorio',
            'title.max'           => 'El título no puede superar 70 caracteres',
            'metatitle.max'       => 'El metatitle no puede superar 70 caracteres',
            'description.max'     => 'La descripción no puede superar 160 caracteres',
            'keywords.max'        => 'Las keywords no pueden superar 255 caracteres',
        ];

        // Obtener datos POST
        $data = $request->post();

        // Validar
        $validate = Validate::rule($rules)->message($messages);
        if (!$validate->check($data)) {
            //return json(['error' => $validate->getError()], 400);
            //Session::flash('error', $validate->getError());
            //return redirect('/admin/meta/index');
            return redirect('/admin/meta/index')->with('error', $validate->getError());

        }
        else
        {

            try {
                Meta::create($data);
                Session::flash('success', 'Meta creada correctamente');
                return redirect('/admin/meta/index');
            } catch (DuplicateException $e) {
                // Captura errores SQL como duplicados

                //dump($e);
                //Session::flash('error', 'Error al guardar la meta: ' . $e->getMessage());

                Session::flash('error', $e->getMessage());

                /*

                if (str_contains($e->getMessage(), 'Duplicate entry')) {
                    Session::flash('error', 'Ya existe una meta con esa página.');
                } else {
                    Session::flash('error', 'Error al guardar la meta: ' . $e->getMessage());
                }
                */
                return redirect('/admin/meta/index');
            }


            /*
            Meta::create($data);
            Session::flash('success', 'Meta creada correctamente');
            //return redirect('/admin/meta/index')->with('success', 'Meta creada correctamente');
            return redirect('/admin/meta/index');
            */
        }
    }

    // Mostrar formulario de edición
    public function edit($id)
    {
        $this->initialize(); // <<-- esto es obligatorio
        $meta = Meta::findOrFail($id);
        return View::fetch('/admin/meta/edit', ['meta' => $meta]);
    }

    // Guardar edición
    public function update(Request $request, $id)
    {
        $meta = Meta::findOrFail($id);
        //$data = Request::post();


        // Reglas de validación
        $rules = [
            'page'        => 'require|max:100',
            'title'       => 'require|max:70',
            'metatitle'   => 'max:70',
            'description' => 'max:160',
            'keywords'    => 'max:255',
        ];

        // Mensajes personalizados (opcional)
        $messages = [
            'page.require'        => 'El campo página es obligatorio',
            'page.max'            => 'El campo página no puede superar 100 caracteres',
            'title.require'       => 'El título es obligatorio',
            'title.max'           => 'El título no puede superar 70 caracteres',
            'metatitle.max'       => 'El metatitle no puede superar 70 caracteres',
            'description.max'     => 'La descripción no puede superar 160 caracteres',
            'keywords.max'        => 'Las keywords no pueden superar 255 caracteres',
        ];

        // Obtener datos POST
        $data = $request->post();

        // Validar
        $validate = Validate::rule($rules)->message($messages);

        dump($validate);

        if (!$validate->check($data)) {

            //return json(['error' => $validate->getError()], 400);
            //Session::flash('error', $validate->getError());
            return redirect('/admin/meta/index')->with('error', $validate->getError());

        }
        else
        {

            $meta->save($data);
            return redirect('/admin/meta/index')->with('success', 'Meta actualizada correctamente');
        }
    }

    // Eliminar
    public function delete($id)
    {
        $meta = Meta::findOrFail($id);
        $meta->delete();
        return redirect('/admin/meta/index')->with('success', 'Meta eliminada correctamente');
    }
}
