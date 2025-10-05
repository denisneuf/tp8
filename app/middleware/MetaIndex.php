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
            'og_type'     => 'website',
        ];

        // Si no existe meta en la BD, usar valores por defecto y marcar bandera
        if (!$meta) {
            $metaData = $defaultMetaData;
            $metaData['defaultmeta'] = true;
            
            // Flags para debug - todos son por defecto
            $metaData['meta_flags'] = [
                'default_title' => true,
                'default_metatitle' => true,
                'default_description' => true,
                'default_keywords' => true,
                'default_og_title' => true,
                'default_og_description' => true,
                'default_og_image' => true,
                'default_og_type' => true,
            ];
        } else {
            // CORREGIDO: Removí la referencia a $product que no existe
            $ogDescription = $meta->og_description;
            
            if (empty($ogDescription)) {
                // Si no hay og_description, usar la descripción normal (limitada)
                $ogDescription = $meta->description ? substr($meta->description, 0, 160) : $defaultMetaData['og_description'];
            }
            
            $metaData = [
                'title'       => $meta->title ?? '',
                'metatitle'   => $meta->metatitle ?? '',
                'description' => $meta->description ?? '',
                'keywords'    => $meta->keywords ?? '',
                'lang'        => config('lang.default_lang', 'es'),
                'og_title'    => $meta->og_title ?? $meta->title,
                'og_description' => $ogDescription,
                'og_image'    => $meta->og_image ?? '',
                'defaultmeta' => false,
                'og_type'     => $meta->og_type ?? 'website',
                
                // Flags para debug - verificamos cada campo individualmente
                'meta_flags' => [
                    'default_title' => empty($meta->title),
                    'default_metatitle' => empty($meta->metatitle),
                    'default_description' => empty($meta->description),
                    'default_keywords' => empty($meta->keywords),
                    'default_og_title' => empty($meta->og_title),
                    'default_og_description' => empty($meta->og_description),
                    'default_og_image' => empty($meta->og_image),
                    // CORREGIDO: No considerar 'website' como valor por defecto
                    'default_og_type' => empty($meta->og_type),
                ]
            ];
            
            // Si AL MENOS UN campo está vacío, marcamos defaultmeta como true
            $anyFieldDefault = $metaData['meta_flags']['default_title'] || 
                             $metaData['meta_flags']['default_metatitle'] || 
                             $metaData['meta_flags']['default_description'] || 
                             $metaData['meta_flags']['default_keywords'] || 
                             $metaData['meta_flags']['default_og_title'] || 
                             $metaData['meta_flags']['default_og_description'] || 
                             $metaData['meta_flags']['default_og_image'] || 
                             $metaData['meta_flags']['default_og_type'];
            
            $metaData['defaultmeta'] = $anyFieldDefault;
        }

        // Asignar información de debug adicional
        $metaData['debug_page'] = $page;
        $metaData['debug_has_meta_record'] = !is_null($meta);

        // Asignar a la vista globalmente
        View::assign($metaData);

        // Continuar con la petición
        return $next($request);
    }
}