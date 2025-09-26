<?php
declare(strict_types=1);

namespace app\validate;

use think\Validate;

class MetaFormValidator extends Validate
{
    protected $rule = [
        'page'        => 'require|max:100|unique:meta,page',
        'title'       => 'require|max:70',
        'metatitle'   => 'max:70',
        'description' => 'max:160',
        'keywords'    => 'max:255',
        
        // Open Graph fields
        'og_title'    => 'max:70',
        'og_description' => 'max:160',
        'og_image'    => 'max:255|url',
        'og_type'     => 'max:50',
    ];

    protected $message = [
        'page.require'        => 'El campo página es obligatorio',
        'page.max'            => 'El campo página no puede superar 100 caracteres',
        'page.unique'         => 'Ya existe una meta para esta página',
        'title.require'       => 'El título es obligatorio',
        'title.max'           => 'El título no puede superar 70 caracteres',
        'metatitle.max'       => 'El metatitle no puede superar 70 caracteres',
        'description.max'     => 'La descripción no puede superar 160 caracteres',
        'keywords.max'        => 'Las keywords no pueden superar 255 caracteres',
        
        // Open Graph messages
        'og_title.max'        => 'El título Open Graph no puede superar 70 caracteres',
        'og_description.max'  => 'La descripción Open Graph no puede superar 160 caracteres',
        'og_image.max'        => 'La URL de la imagen Open Graph no puede superar 255 caracteres',
        'og_image.url'        => 'La imagen Open Graph debe ser una URL válida',
        'og_type.max'         => 'El tipo Open Graph no puede superar 50 caracteres',
    ];

    /**
     * Escena para actualización (ignorar unique en el registro actual)
     */
    public function sceneUpdate($id)
    {
        return $this->remove('page', 'unique')
                    ->append('page', 'unique:meta,page,' . $id);
    }
}