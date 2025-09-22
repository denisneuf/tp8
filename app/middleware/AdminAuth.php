<?php
declare(strict_types=1);

namespace app\middleware;

use think\Request;
use think\facade\Session;
use think\response\Redirect;
use think\response\Html;
use think\response\View;

class AdminAuth
{
    public function handle(Request $request, \Closure $next): Html|Redirect|View
    {
        if (!Session::has('admin_user')) {
        	$currentUrl = $request->url(true); // URL completa
			$encodedUrl = base64_encode($currentUrl);
        	return redirect('/admin/login?redirect=' . urlencode($encodedUrl))
            	->with('error', 'Debes iniciar sesión');
        }
        return $next($request);
    }
}