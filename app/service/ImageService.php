<?php
declare (strict_types = 1);

namespace app\service;

use think\File;
use think\facade\Session;
use think\facade\Log; // ← Esta línea añadida
//use app\common\ProcessResult; // Si decides implementarlo después

class ImageService
{
    // Constantes de configuración
    private const IMAGE_MAX_SIZE = 5242880; // 5MB
    private const IMAGE_MIN_DIMENSION = 800;
    private const IMAGE_MIN_RATIO = 1.5;
    private const IMAGE_MAX_RATIO = 2.5;

    //protected $savepath;


    private int $imageMinDimension;
    private float $imageMinRatio;
    private float $imageMaxRatio;



    public function setImageMaxRatio(float $ratio): self
    {
        $this->imageMaxRatio = $ratio;
        return $this;
    }

    /**
     * Método para obtener la dimensión mínima actual
     */
    public function getImageMaxRatio(): float
    {
        return $this->imageMaxRatio;
    }


    public function setImageMinRatio(float $ratio): self
    {
        $this->imageMinRatio = $ratio;
        return $this;
    }

    /**
     * Método para obtener la dimensión mínima actual
     */
    public function getImageMinRatio(): float
    {
        return $this->imageMinRatio;
    }


    public function setImageMinDimension(int $dimension): self
    {
        $this->imageMinDimension = $dimension;
        return $this;
    }

    /**
     * Método para obtener la dimensión mínima actual
     */
    public function getImageMinDimension(): int
    {
        return $this->imageMinDimension;
    }

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
    public function processSquareImage(File $file, string $path, string $brandName, ?string $oldFilename = null): string
    {


        Log::info('ImageService - imageMinDimension actual: ' . $this->imageMinDimension); //

        //dump($this->imageMinDimension);

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
        
        if ($width < self::IMAGE_MIN_DIMENSION || $height < $this->imageMinDimension) {
            throw new \RuntimeException("La imagen debe ser de al menos " . $this->imageMinDimension . "x" . $this->imageMinDimension . " píxeles");
        }

        return $this->saveImage($file, $path, $brandName, $oldFilename);
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
    public function processLandscapeImage(File $file, string $path, string $brandName, ?string $oldFilename = null): string
    {
        // Validación básica
        if (!$this->validateImageFile($file, 'block_pic')) {
            throw new \RuntimeException('Error de validación de imagen de bloque: Formato no válido o tamaño excedido');
        }

        // Validación de dimensiones (solo advertencia)
        $tempPath = $file->getRealPath();
        list($width, $height) = getimagesize($tempPath);
        
        $actualRatio = $width / $height;
        
        if ($actualRatio < self::IMAGE_MIN_RATIO || $actualRatio > self::IMAGE_MAX_RATIO) {
            // Solo lanzamos advertencia via Session
            Session::flash('warning', 'La imagen de bloque recomienda una relación de aspecto 2:1 (ancho doble que alto)');
        }

        return $this->saveImage($file, $path, $brandName, $oldFilename, '_bg_');
    }

    /**
     * Guarda la imagen y maneja la lógica común
     */
    private function saveImage(File $file, string $savepath, string $brandName, ?string $oldFilename, string $suffix = ''): string
    {
        //$basePath = app()->getRootPath() . 'public/static/img/';
        //$topicPath = $basePath . 'brand/';
        
        if (!is_dir($savepath)) {
            mkdir($savepath, 0755, true);
        }

        // Generar nombre del archivo
        $extension = strtolower($file->getOriginalExtension());
        $timestamp = time();
        $newFilename = $brandName . $suffix . $timestamp . '.' . $extension;

        // Eliminar imagen anterior si existe
        if ($oldFilename) {
            $this->deleteBrandImage($oldFilename, $savepath);
        }

        // Mover nueva imagen
        $file->move($savepath, $newFilename);

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
    /*
    public function getBrandImagePath(string $filename): string
    {
        return app()->getRootPath() . 'public/static/img/brand/' . $filename;
    }
    */
}
