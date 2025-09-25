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
    ];

    protected $message = [
        'title.require'  => ['code' => 'title', 'msg' => 'El título del menú es obligatorio'],
        'title.max'      => ['code' => 'title', 'msg' => 'El título del menú no puede superar los 100 caracteres'],
        'url.require'    => ['code' => 'url', 'msg' => 'La URL del menú es obligatoria'],
        'url.max'        => ['code' => 'url', 'msg' => 'La URL del menú no puede superar los 255 caracteres'],
        'order.number'   => ['code' => 'order', 'msg' => 'El orden del menú debe ser un número'],
        'visible.in'     => ['code' => 'visible', 'msg' => 'El valor de visibilidad del menú es inválido'],
    ];

    public function check(array $data, $rules = []): bool
    {
        // Primero validar campos estáticos con la validación padre
        $staticResult = parent::check($data, $rules);
        
        if (!$staticResult) {
            return false;
        }

        // Luego validar campos dinámicos - ESTA PARTE SÍ SE EJECUTA
        $dynamicError = $this->validateDynamicFields($data);
        
        if ($dynamicError !== null) {
            // Configurar el error dinámico
            $this->error = $dynamicError;
            return false;
        }

        return true;
    }

    private function validateDynamicFields($data)
    {
        // DEBUG: Verificar que la función se ejecuta
        // dump('validateDynamicFields ejecutándose');
        // dump($data);

        // Validar columnas
        if (isset($data['columns']) && is_array($data['columns'])) {
            foreach ($data['columns'] as $colIndex => $column) {
                // DEBUG: Verificar cada columna
                // dump("Validando columna {$colIndex}:", $column);

                // Validar título de columna - CORREGIDO
                if (isset($column['title'])) {
                    // DEBUG
                    // dump("Título de columna {$colIndex}: '{$column['title']}'");
                    // dump("Longitud: " . strlen($column['title']));
                    
                    if (empty(trim($column['title']))) {
                        $fieldName = "columns[{$colIndex}][title]";
                        // DEBUG
                        // dump("ERROR: Título vacío en columna {$colIndex}");
                        return [
                            'code' => $fieldName, 
                            'msg' => "La columna #" . ($colIndex + 1) . " requiere un título"
                        ];
                    } elseif (strlen($column['title']) > 100) {
                        $fieldName = "columns[{$colIndex}][title]";
                        // DEBUG
                        // dump("ERROR: Título demasiado largo en columna {$colIndex}");
                        return [
                            'code' => $fieldName, 
                            'msg' => "El título de la columna #" . ($colIndex + 1) . " no puede tener más de 100 caracteres"
                        ];
                    }
                } else {
                    // Si no existe el título
                    $fieldName = "columns[{$colIndex}][title]";
                    return [
                        'code' => $fieldName, 
                        'msg' => "La columna #" . ($colIndex + 1) . " requiere un título"
                    ];
                }

                // Validar order de columna
                if (isset($column['order'])) {
                    if (!is_numeric($column['order'])) {
                        $fieldName = "columns[{$colIndex}][order]";
                        return [
                            'code' => $fieldName, 
                            'msg' => "El orden de la columna #" . ($colIndex + 1) . " debe ser un número"
                        ];
                    }
                }

                // Validar links dentro de cada columna
                if (isset($column['links']) && is_array($column['links'])) {
                    foreach ($column['links'] as $linkIndex => $link) {
                        // Validar título del link
                        if (isset($link['title'])) {
                            if (empty(trim($link['title']))) {
                                $fieldName = "columns[{$colIndex}][links][{$linkIndex}][title]";
                                return [
                                    'code' => $fieldName, 
                                    'msg' => "El link #" . ($linkIndex + 1) . " de la columna #" . ($colIndex + 1) . " requiere un título"
                                ];
                            } elseif (strlen($link['title']) > 100) {
                                $fieldName = "columns[{$colIndex}][links][{$linkIndex}][title]";
                                return [
                                    'code' => $fieldName, 
                                    'msg' => "El título del link #" . ($linkIndex + 1) . " de la columna #" . ($colIndex + 1) . " no puede tener más de 100 caracteres"
                                ];
                            }
                        } else {
                            $fieldName = "columns[{$colIndex}][links][{$linkIndex}][title]";
                            return [
                                'code' => $fieldName, 
                                'msg' => "El link #" . ($linkIndex + 1) . " de la columna #" . ($colIndex + 1) . " requiere un título"
                            ];
                        }

                        // Validar URL del link
                        if (isset($link['url'])) {
                            if (empty(trim($link['url']))) {
                                $fieldName = "columns[{$colIndex}][links][{$linkIndex}][url]";
                                return [
                                    'code' => $fieldName, 
                                    'msg' => "El link #" . ($linkIndex + 1) . " de la columna #" . ($colIndex + 1) . " requiere una URL"
                                ];
                            } elseif (strlen($link['url']) > 255) {
                                $fieldName = "columns[{$colIndex}][links][{$linkIndex}][url]";
                                return [
                                    'code' => $fieldName, 
                                    'msg' => "La URL del link #" . ($linkIndex + 1) . " de la columna #" . ($colIndex + 1) . " no puede tener más de 255 caracteres"
                                ];
                            }
                        } else {
                            $fieldName = "columns[{$colIndex}][links][{$linkIndex}][url]";
                            return [
                                'code' => $fieldName, 
                                'msg' => "El link #" . ($linkIndex + 1) . " de la columna #" . ($colIndex + 1) . " requiere una URL"
                            ];
                        }

                        // Validar order del link
                        if (isset($link['order']) && !is_numeric($link['order'])) {
                            $fieldName = "columns[{$colIndex}][links][{$linkIndex}][order]";
                            return [
                                'code' => $fieldName, 
                                'msg' => "El orden del link #" . ($linkIndex + 1) . " de la columna #" . ($colIndex + 1) . " debe ser un número"
                            ];
                        }
                    }
                }
            }
        }

        return null;
    }
}
