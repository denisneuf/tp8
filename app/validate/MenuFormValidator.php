<?php
declare (strict_types = 1);

namespace app\validate;

use think\Validate;

class MenuFormValidator extends Validate
{
    protected $rule = [
        'title' => 'require|max:100',
        'url' => 'require|max:255',
        'order' => 'number',
        'visible' => 'in:0,1',

        'columns' => 'array',
        'columns.*.title' => 'require|max:100',
        'columns.*.order' => 'number',

        'columns.*.links' => 'array',
        'columns.*.links.*.title' => 'require|max:100',
        'columns.*.links.*.url' => 'require|max:255',
        'columns.*.links.*.order' => 'number',
    ];

    protected $message = [
        'title.require' => 'El título del menú es obligatorio.',
        'title.max' => 'El título del menú no puede superar los 100 caracteres.',
        'url.require' => 'La URL del menú es obligatoria.',
        'url.max' => 'La URL del menú no puede superar los 255 caracteres.',
        'order.number' => 'El orden del menú debe ser un número.',
        'visible.in' => 'El valor de visibilidad del menú es inválido.',

        'columns.array' => 'Las columnas deben ser un arreglo.',
        'columns.*.title.require' => 'Cada columna debe tener un título.',
        'columns.*.title.max' => 'El título de la columna no puede superar los 100 caracteres.',
        'columns.*.order.number' => 'El orden de la columna debe ser un número.',

        'columns.*.links.array' => 'Los enlaces deben ser un arreglo.',
        'columns.*.links.*.title.require' => 'Cada enlace debe tener un título.',
        'columns.*.links.*.title.max' => 'El título del enlace no puede superar los 100 caracteres.',
        'columns.*.links.*.url.require' => 'Cada enlace debe tener una URL.',
        'columns.*.links.*.url.max' => 'La URL del enlace no puede superar los 255 caracteres.',
        'columns.*.links.*.order.number' => 'El orden del enlace debe ser un número.',
    ];
}
