<?php
declare (strict_types = 1);

namespace app\middleware;

use app\model\Meta;
use think\facade\View;

class MetaIndex
{
    public function handle($request, \Closure $next)
    {
        // Ruta limpia sin barra inicial/final
        $path = trim($request->pathinfo(), '/');
        $page = $path === '' ? 'index' : $path;

        // Buscar en la base de datos
        $meta = Meta::where('page', $page)->find();

        // Valores por defecto
        $defaultMetaData = [
            'title'       => ucfirst($page),
            'metatitle'   => ucfirst($page) . ' - Mi Sitio',
            'description' => 'Descripción por defecto para ' . ucfirst($page),
            'keywords'    => $page,
            'lang'        => config('lang.default_lang', 'es'),
        ];

        // Si no existe meta en la BD, usar valores por defecto y marcar bandera
        if (!$meta) {
            $metaData = $defaultMetaData;
            $metaData['defaultmeta'] = true;
        } else {
            $metaData = [
                'title'       => $meta->title ?? '',
                'metatitle'   => $meta->metatitle ?? '',
                'description' => $meta->description ?? '',
                'keywords'    => $meta->keywords ?? '',
                'lang'        => config('lang.default_lang', 'es'),
                'defaultmeta' => false,
            ];
        }

        // Asignar a la vista globalmente
        View::assign($metaData);

        // Continuar con la petición
        return $next($request);
    }
}
