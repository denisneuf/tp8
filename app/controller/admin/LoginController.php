<?php
declare(strict_types=1);

namespace app\controller\admin;

use think\facade\Session;
use think\Request;
use think\facade\Validate;
use app\model\User;

class LoginController
{
    public function index(Request $request)
    {
        // Vista de login
        //dump($request->url());
        return view('admin/login');
    }

    public function login(Request $request)
    {

        //$data = Request::post();
        $data = $request->post();

        $check = $request->checkToken('__token__');
        
        if(false === $check) {
            return view('admin/login', ['error' => 'invalid token']);
            //throw new ValidateException('invalid token');
        }

        //dump(Request::url());

        //$password_plano = "123456";
        //$hash = password_hash($password_plano, PASSWORD_DEFAULT);

        //dump($hash);

        //$redirect = $data['redirect'];
        $redirect = $data['redirect'] ?? null;

        // Validar datos mínimos
        $validate = Validate::rule([
            'username' => 'require|max:50',
            'password' => 'require|min:6',
        ]);

        if (!$validate->check($data)) {
            dump($validate->getError());
            //return redirect('/admin/login')->with('error', $validate->getError());
            return view('admin/login', ['error' => $validate->getError()]);
        }

        $userModel = new User();
        $user = $userModel->where('username', $data['username'])
                          ->where('islock', 0)
                          ->find();

        //dump($user);

        if ($user && password_verify($data['password'], $user->password)) {
            //echo "✅ Contraseña correcta, acceso permitido.";
            Session::set('admin_user', $user->toArray());




            // Obtener parámetro redirect

            if ($redirect) {
                return redirect(base64_decode($redirect));
            }


            return redirect('/admin/dashboard');


        //return redirect('/admin/dashboard');
        } else {
            //echo "❌ Contraseña incorrecta.";
            return view('admin/login', ['error' => 'Usuario o contraseña incorrectos']);
        }

        /*

        if (!$user || !password_verify($data['password'], $user->password)) {

            dump("Usuario o contraseña incorrectos");
            dump(password_verify($data['password'], $user->password));

            //return view('admin/login', ['error' => 'Usuario o contraseña incorrectos']);

            //return redirect('/admin/login')->with('error', 'Usuario o contraseña incorrectos');
        }

        // Guardar sesión
        Session::set('admin_user', $user->toArray());


        //return redirect('/admin/dashboard');
        */
    }

    public function logout()
    {
        Session::delete('admin_user');
        return redirect('/admin/login');
    }
}
