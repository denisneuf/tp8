<?php
declare (strict_types = 1);

namespace app\service;

use think\File;
use think\facade\Session;
use app\common\ProcessResult; // Si decides implementarlo después

class ImageService
{
    // Constantes de configuración
    private const IMAGE_MAX_SIZE = 5242880; // 5MB
    private const IMAGE_MIN_DIMENSION = 1500;
    private const BLOCK_IMAGE_MIN_RATIO = 1.5;
    private const BLOCK_IMAGE_MAX_RATIO = 2.5;

    /**
     * Valida un archivo de imagen según los criterios establecidos
     * 
     * @param \think\File $file Archivo a validar
     * @param string $fieldName Nombre del campo para mensajes de error
     * @param string[] $allowedExtensions Extensiones permitidas
     * @return bool True si la validación es exitosa, false en caso contrario
     */
    public function validateImageFile(File $file, string $fieldName, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp']): bool
    {
        $validate = validate([
            $fieldName => [
                'file', 
                'fileExt' => implode(',', $allowedExtensions),
                'fileSize' => self::IMAGE_MAX_SIZE,
            ]
        ]);
        
        return $validate->check([$fieldName => $file]);
    }

    /**
     * Procesa la imagen del logo de la marca con validaciones
     * 
     * @param \think\File $file Archivo de imagen a procesar
     * @param string $brandName Nombre de la marca para generar el nombre del archivo
     * @param string|null $oldFilename Nombre del archivo antiguo a eliminar
     * @param int $id ID de la marca para redireccionamientos
     * @return string|false Retorna el nuevo nombre del archivo o false en caso de error
     */
    public function processBrandLogo(File $file, string $brandName, ?string $oldFilename = null): string
    {
        // Validación básica
        if (!$this->validateImageFile($file, 'pic')) {
            throw new \RuntimeException('Error de validación de imagen: Formato no válido o tamaño excedido');
        }

        // Validación de dimensiones
        $tempPath = $file->getRealPath();
        list($width, $height) = getimagesize($tempPath);
        
        if ($width !== $height) {
            throw new \RuntimeException('La imagen debe ser cuadrada');
        }
        
        if ($width < self::IMAGE_MIN_DIMENSION || $height < self::IMAGE_MIN_DIMENSION) {
            throw new \RuntimeException("La imagen debe ser de al menos " . self::IMAGE_MIN_DIMENSION . "x" . self::IMAGE_MIN_DIMENSION . " píxeles");
        }

        return $this->saveBrandImage($file, $brandName, $oldFilename);
    }

    /**
     * Procesa la imagen de bloque de la marca con validaciones
     * 
     * @param \think\File $file Archivo de imagen de bloque a procesar
     * @param string $brandName Nombre de la marca para generar el nombre del archivo
     * @param string|null $oldFilename Nombre del archivo antiguo a eliminar
     * @param int $id ID de la marca para redireccionamientos
     * @return string|false Retorna el nuevo nombre del archivo o false en caso de error
     */
    public function processBrandBlockImage(File $file, string $brandName, ?string $oldFilename = null): string
    {
        // Validación básica
        if (!$this->validateImageFile($file, 'block_pic')) {
            throw new \RuntimeException('Error de validación de imagen de bloque: Formato no válido o tamaño excedido');
        }

        // Validación de dimensiones (solo advertencia)
        $tempPath = $file->getRealPath();
        list($width, $height) = getimagesize($tempPath);
        
        $actualRatio = $width / $height;
        
        if ($actualRatio < self::BLOCK_IMAGE_MIN_RATIO || $actualRatio > self::BLOCK_IMAGE_MAX_RATIO) {
            // Solo lanzamos advertencia via Session
            Session::flash('warning', 'La imagen de bloque recomienda una relación de aspecto 2:1 (ancho doble que alto)');
        }

        return $this->saveBrandImage($file, $brandName, $oldFilename, '_bg_');
    }

    /**
     * Guarda la imagen y maneja la lógica común
     */
    private function saveBrandImage(File $file, string $brandName, ?string $oldFilename, string $suffix = ''): string
    {
        $basePath = app()->getRootPath() . 'public/static/img/';
        $topicPath = $basePath . 'brand/';
        
        if (!is_dir($topicPath)) {
            mkdir($topicPath, 0755, true);
        }

        // Generar nombre del archivo
        $extension = strtolower($file->getOriginalExtension());
        $timestamp = time();
        $newFilename = $brandName . $suffix . $timestamp . '.' . $extension;

        // Eliminar imagen anterior si existe
        if ($oldFilename) {
            $this->deleteBrandImage($oldFilename, $topicPath);
        }

        // Mover nueva imagen
        $file->move($topicPath, $newFilename);

        return $newFilename;
    }

    /**
     * Elimina una imagen de marca del sistema de archivos
     * 
     * @param string $filename Nombre del archivo a eliminar
     * @param string $directory Directorio donde se encuentra el archivo
     * @return void
     */
    public function deleteBrandImage(string $filename, string $directory): void
    {
        $path = $directory . $filename;   
        if (file_exists($path) && is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Obtiene la ruta completa para imágenes de marca
     */
    public function getBrandImagePath(string $filename): string
    {
        return app()->getRootPath() . 'public/static/img/brand/' . $filename;
    }
}
