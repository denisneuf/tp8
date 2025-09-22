<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\Session;
use think\Request;
use app\model\User;
use app\validate\LoginFormValidator;
use think\facade\View;
use think\exception\ValidateException;
use think\response\Redirect;

class LoginController extends BaseController
{


    /**
     * Validador de formularios para categorías
     * 
     * @var LoginFormValidator
     */
    private LoginFormValidator $loginValidator;


    public function initialize(): void
    {
        // Usar el contenedor de dependencias de ThinkPHP
        $this->loginValidator = app(LoginFormValidator::class);
    }


    public function index(): string
    {
        //return view('/admin/login');
        return View::fetch('/admin/login');
    }

    public function login(Request $request): string|Redirect
    {
        $data = $request->post();
        $redirect = $data['redirect'] ?? null;

        // Verificación de token CSRF
        if (false === $request->checkToken('__token__')) {
            return View::fetch('admin/login', [
                'error' => 'Token inválido, inténtalo de nuevo.',
            ]);
        }

        // Validación mediante clase dedicada
        try {
            $this->loginValidator->check($data);
        } catch (ValidateException $e) {
            return View::fetch('admin/login', [
                'error' => $e->getError(),
            ]);
        }

        $user = User::findActiveByUsername($data['username']);

        if ($user && $user->checkPassword($data['password'])) {
            Session::set('admin_user', $user->toArray());
            Session::regenerate();
            // Redirección segura mejorada
            if (is_string($redirect) && $redirect !== '') {
                $decoded = base64_decode($redirect, true);
                
                // Verificar si la decodificación fue exitosa
                if ($decoded !== false) {
                    // Comprobar si es una ruta relativa (comienza con /)
                    if (str_starts_with($decoded, '/')) {
                        return redirect($decoded);
                    }
                    // Comprobar si es una URL del mismo dominio (opcional, según necesidades)
                    elseif (filter_var($decoded, FILTER_VALIDATE_URL) !== false) {
                        $currentDomain = request()->domain();
                        if (str_starts_with($decoded, $currentDomain)) {
                            return redirect($decoded);
                        }
                    }
                }
                // Si no es válida, redirigir a una página por defecto
                return redirect('/admin/dashboard');
            }
            return redirect('/admin/dashboard');

        }

        return View::fetch('admin/login', [
            'error' => 'Usuario o contraseña incorrectos',
        ]);
    }

    public function logout(): Redirect
    {
        Session::delete('admin_user');
        Session::clear(); // Opcional: limpiar toda la sesión
        return redirect('/admin/login');
    }
}
