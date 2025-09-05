<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\View;
use think\Request;
use think\facade\Session;
use think\facade\Validate;
use app\model\User;
use think\exception\ValidateException;
use think\model\concern\SoftDelete;

class UserController extends AdminMenuController
{
    private function getValidationRules(): array
    {
        return [
            'rules' => [
                'username' => 'require|max:50|unique:users',
                'password' => 'require|min:6|max:255',
                'nickname' => 'max:50',
                'email'    => 'email|max:100',
                'mobile'   => 'max:20',
                'pid'      => 'integer',
                'loginnum' => 'integer',
                'lastip'   => 'max:45',
                'islock'   => 'in:0,1',
            ],
            'messages' => [
                'username.require' => 'El nombre de usuario es obligatorio',
                'username.max'     => 'Máximo 50 caracteres',
                'username.unique'  => 'Ya existe un usuario con ese nombre',
                'password.require' => 'La contraseña es obligatoria',
                'password.min'     => 'Mínimo 6 caracteres',
                'password.max'     => 'Máximo 255 caracteres',
                'email.email'      => 'Formato de email inválido',
                'email.max'        => 'Máximo 100 caracteres',
                'mobile.max'       => 'Máximo 20 caracteres',
                'nickname.max'     => 'Máximo 50 caracteres',
                'lastip.max'       => 'Máximo 45 caracteres',
                'islock.in'        => 'Valor inválido para bloqueo',
            ]
        ];
    }

    public function index()
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        /*
        $users = User::order('id', 'desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);
        */


        //Listado de todos eliminados y no eliminados con soft delete
        
        $users = User::withTrashed()->order('id', 'desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);


        //Listado de solo los que se han eliminado con soft delete
        /*
        $users = User::onlyTrashed()->order('id', 'desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);
        */



        
        //No se necesita whereNull('delete_time')
        /*
        $users = User::whereNull('delete_time')->order('id', 'desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);
        */


        return view('admin/user/list', [
            'users'   => $users,
            'error'   => $errorMessage,
            'success' => $successMessage,
        ]);
    }

    public function create()
    {
        return View::fetch('admin/user/create');
    }

    public function save(Request $request)
    {
        $data = $request->post();
        $validation = $this->getValidationRules();
        $validate = Validate::rule($validation['rules'])->message($validation['messages']);

        if (!$validate->check($data)) {
            return redirect('/admin/user/index')->with('error', $validate->getError());
        }

        // Hashear la contraseña antes de guardar
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        try {
            User::create($data);
            Session::flash('success', 'Usuario creado correctamente');
        } catch (\Exception $e) {
            Session::flash('error', 'Error al guardar el usuario: ' . $e->getMessage());
        }

        return redirect('/admin/user/index');
    }

    public function edit($id)
    {
        $this->initialize();
        $user = User::findOrFail($id);
        return View::fetch('admin/user/edit', ['user' => $user]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->post();

        $validation = $this->getValidationRules();
        unset($validation['rules']['username']); // Evitar validación de único en edición
        unset($validation['rules']['password']); // No obligar a cambiar contraseña

        $validate = Validate::rule($validation['rules'])->message($validation['messages']);

        if (!$validate->check($data)) {
            return redirect('/admin/user/index')->with('error', $validate->getError());
        }

        // Si se envía nueva contraseña, la hasheamos
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        $user->save($data);
        return redirect('/admin/user/index')->with('success', 'Usuario actualizado correctamente');
    }

    public function delete(Request $request, $id)
    {
        $check = $request->checkToken('__token__');
        if (false === $check) {
            throw new ValidateException('invalid token');
        }

        $user = User::findOrFail($id);
        $user->delete(); // Soft delete
        return redirect('/admin/user/index')->with('success', 'Usuario eliminado correctamente');
    }


    public function restore(Request $request, $id)
    {
        $check = $request->checkToken('__token__');
        if (false === $check) {
            throw new ValidateException('invalid token');
        }

        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect('/admin/user/index')->with('success', 'Usuario restaurado correctamente');
    }


}
