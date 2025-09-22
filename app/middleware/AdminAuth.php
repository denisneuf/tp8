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
            //return Redirect::url('admin/login');
            //return redirect(url('admin/login'))->with('error', 'Debes iniciar sesión');

        	$currentUrl = $request->url(true); // URL completa
            //dump($currentUrl);
            //Log::debug('URL: ' . currentUrl);
			$encodedUrl = base64_encode($currentUrl);


        	//dump($currentUrl);


        	return redirect('/admin/login?redirect=' . urlencode($encodedUrl))
            	->with('error', 'Debes iniciar sesión');


            //return redirect('/admin/login')->with('error', 'Debes iniciar sesión');
        }

        return $next($request);
    }
}

