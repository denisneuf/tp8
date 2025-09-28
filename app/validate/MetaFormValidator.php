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
        'page.require'        => ['code' => 'page', 'msg' => 'El campo página es obligatorio'],
        'page.max'            => ['code' => 'page', 'msg' => 'El campo página no puede superar 100 caracteres'],
        'page.unique'         => ['code' => 'page', 'msg' => 'Ya existe una meta para esta página'],
        'title.require'       => ['code' => 'title', 'msg' => 'El título es obligatorio'],
        'title.max'           => ['code' => 'title', 'msg' => 'El título no puede superar 70 caracteres'],
        'metatitle.max'       => ['code' => 'metatitle', 'msg' => 'El metatitle no puede superar 70 caracteres'],
        'description.max'     => ['code' => 'description', 'msg' => 'La descripción no puede superar 160 caracteres'],
        'keywords.max'        => ['code' => 'keywords', 'msg' => 'Las keywords no pueden superar 255 caracteres'],
        
        // Open Graph messages
        'og_title.max'        => ['code' => 'og_title', 'msg' => 'El título Open Graph no puede superar 70 caracteres'],
        'og_description.max'  => ['code' => 'og_description', 'msg' => 'La descripción Open Graph no puede superar 160 caracteres'],
        'og_image.max'        => ['code' => 'og_image', 'msg' => 'La URL de la imagen Open Graph no puede superar 255 caracteres'],
        'og_image.url'        => ['code' => 'og_image', 'msg' => 'La imagen Open Graph debe ser una URL válida'],
        'og_type.max'         => ['code' => 'og_type', 'msg' => 'El tipo Open Graph no puede superar 50 caracteres'],
    ];

    /**
     * Escena para actualización (ignorar unique en el registro actual)
     */

    
    //Working
    public function sceneUpdate(int $id)
    {
        // Filtramos los campos que queremos validar
        $this->only([
            'page', 'title', 'metatitle', 'description', 'keywords', 'og_title', 'og_description', 'og_image', 'og_type'
        ]);

        // Quitamos las reglas base de page (que tenían unique sin id)
        $this->removeRule(['page']);

        // Añadimos las reglas correctas para update
        $this->rule([
            'page' => "require|max:100|unique:meta,page,{$id},id",
        ]);

        return $this;
    }
    


}