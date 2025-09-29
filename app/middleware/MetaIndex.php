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
            'og_title'    => ucfirst($page),
            'og_description' => 'OG Descripción por defecto para ' . ucfirst($page),
            'og_image'    => 'https://example.com/img.jpg',
            'og_type'  => 'website',
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
                'og_title'    => $meta->og_title ?? $meta->title,
                'og_description' => $meta->og_description ?? substr($product.description, 0, 160),
                'og_image'    => $meta->og_image ?? '',
                'defaultmeta' => false,
                'og_type'     => $meta->og_type ?? 'website',
            ];
        }

        // Asignar a la vista globalmente
        View::assign($metaData);

        // Continuar con la petición
        return $next($request);
    }
}
