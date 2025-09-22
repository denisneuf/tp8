<?php
declare(strict_types=1);

namespace app\middleware;

use think\facade\Session;
use think\response\Redirect;
use think\facade\Log;

class AdminAuth
{
    public function handle($request, \Closure $next)
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