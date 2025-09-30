<?php
declare (strict_types = 1);

namespace app\service;

use think\File;
use think\Image;
use think\facade\Session;
use think\facade\Log;

class ImageService
{
    // Constantes de configuración
    private const IMAGE_MAX_SIZE = 5242880; // 5MB
    private const IMAGE_MIN_DIMENSION = 800;
    private const IMAGE_MIN_RATIO = 1.5;
    private const IMAGE_MAX_RATIO = 2.5;

    // Tamaños para las versiones de la imagen
    private const THUMBNAIL_SIZES = [
        'xs' => 50,    // Extra small (para listas muy compactas)
        'sm' => 100,   // Small (para listas)
        'md' => 150,   // Medium (para cards)
        'lg' => 300,   // Large (para detalle)
        'xl' => 500,    // Extra large (para lightbox)
        'xxl' => 1000    // Extra large (para lightbox)
    ];

    private int $imageMinDimension;
    private float $imageMinRatio;
    private float $imageMaxRatio;
    private bool $generateThumbnails = true; // Nuevo: flag para controlar thumbnails



    /**
     * Constructor para inicializar valores por defecto
     */
    public function __construct()
    {
        // Inicializar valores por defecto
        $this->imageMinDimension = self::IMAGE_MIN_DIMENSION;
        $this->imageMinRatio = self::IMAGE_MIN_RATIO;
        $this->imageMaxRatio = self::IMAGE_MAX_RATIO;
    }

    public function setGenerateThumbnails(bool $generate): self
    {
        $this->generateThumbnails = $generate;
        return $this;
    }

    public function getGenerateThumbnails(): bool
    {
        return $this->generateThumbnails;
    }


    // Métodos setters y getters existentes...
    public function setImageMinDimension(int $dimension): self
    {
        $this->imageMinDimension = $dimension;
        return $this;
    }

    public function getImageMinDimension(): int
    {
        return $this->imageMinDimension;
    }

    public function setImageMinRatio(float $ratio): self
    {
        $this->imageMinRatio = $ratio;
        return $this;
    }

    public function getImageMinRatio(): float
    {
        return $this->imageMinRatio;
    }

    public function setImageMaxRatio(float $ratio): self
    {
        $this->imageMaxRatio = $ratio;
        return $this;
    }

    public function getImageMaxRatio(): float
    {
        return $this->imageMaxRatio;
    }

    /**
     * Valida un archivo de imagen según los criterios establecidos
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
     * Procesa la imagen del logo de la marca con validaciones y genera múltiples tamaños
     */
    public function processSquareImage(File $file, string $path, string $brandName, ?string $oldFilename = null, ?bool $generateThumbnails = null): string
    {
        Log::info('ImageService - imageMinDimension actual: ' . $this->imageMinDimension);

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
        
        if ($width < $this->imageMinDimension || $height < $this->imageMinDimension) {
            throw new \RuntimeException("La imagen debe ser de al menos " . $this->imageMinDimension . "x" . $this->imageMinDimension . " píxeles");
        }

        //return $this->saveImageWithThumbnails($file, $path, $brandName, $oldFilename);
        // Usar el parámetro específico o la configuración general
        $shouldGenerateThumbnails = $generateThumbnails ?? $this->generateThumbnails;

        if ($shouldGenerateThumbnails) {
            return $this->saveImageWithThumbnails($file, $path, $brandName, $oldFilename);
        } else {
            return $this->saveImage($file, $path, $brandName, $oldFilename);
        }
    }

    /**
     * Procesa la imagen de bloque de la marca con validaciones
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
        
        if ($actualRatio < $this->imageMinRatio || $actualRatio > $this->imageMaxRatio) {
            Session::flash('warning', 'La imagen de bloque recomienda una relación de aspecto 2:1 (ancho doble que alto)');
        }

        return $this->saveImage($file, $path, $brandName, $oldFilename, '_bg_');
    }

    /**
     * Guarda la imagen original y genera thumbnails de diferentes tamaños
     */
    private function saveImageWithThumbnails(File $file, string $savepath, string $brandName, ?string $oldFilename, string $suffix = ''): string
    {
        if (!is_dir($savepath)) {
            mkdir($savepath, 0755, true);
        }

        // Crear directorio para thumbnails si no existe
        $thumbnailsPath = $savepath . 'thumbnails/';
        if (!is_dir($thumbnailsPath)) {
            mkdir($thumbnailsPath, 0755, true);
        }

        // Eliminar imágenes anteriores si existen
        if ($oldFilename) {
            $this->deleteBrandImage($oldFilename, $savepath);
            $this->deleteThumbnails($oldFilename, $thumbnailsPath);
        }

        // Generar nombre del archivo
        $extension = strtolower($file->getOriginalExtension());
        $timestamp = time();
        $newFilename = $this->generateSlug($brandName) . $suffix . $timestamp . '.' . $extension;

        // Mover imagen original
        $file->move($savepath, $newFilename);
        $originalPath = $savepath . $newFilename;

        // Generar thumbnails
        $this->generateThumbnails($originalPath, $thumbnailsPath, $newFilename);

        return $newFilename;
    }

    /**
     * Genera thumbnails de diferentes tamaños
     */
    private function generateThumbnails(string $originalPath, string $thumbnailsPath, string $filename): void
    {
        $image = Image::open($originalPath);
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        foreach (self::THUMBNAIL_SIZES as $sizeName => $size) {
            $thumbnailFilename = $nameWithoutExt . '_' . $sizeName . '.' . $extension;
            $thumbnailPath = $thumbnailsPath . $thumbnailFilename;

            // Crear thumbnail manteniendo relación de aspecto
            $image->thumb($size, $size, Image::THUMB_CENTER)
                  ->save($thumbnailPath);
        }
    }

    /**
     * Guarda la imagen sin generar thumbnails (para imágenes de bloque)
     */
    private function saveImage(File $file, string $savepath, string $brandName, ?string $oldFilename, string $suffix = ''): string
    {
        if (!is_dir($savepath)) {
            mkdir($savepath, 0755, true);
        }

        // Generar nombre del archivo
        $extension = strtolower($file->getOriginalExtension());
        $timestamp = time();
        $newFilename = $this->generateSlug($brandName) . $suffix . $timestamp . '.' . $extension;

        // Eliminar imagen anterior si existe
        if ($oldFilename) {
            $this->deleteBrandImage($oldFilename, $savepath);
        }

        // Mover nueva imagen
        $file->move($savepath, $newFilename);

        return $newFilename;
    }

    /**
     * Elimina una imagen y sus thumbnails
     */
    public function deleteBrandImage(string $filename, string $directory): void
    {
        $path = $directory . $filename;   
        if (file_exists($path) && is_file($path)) {
            unlink($path);
        }

        // Eliminar thumbnails
        $this->deleteThumbnails($filename, $directory . 'thumbnails/');
    }

    /**
     * Elimina todos los thumbnails de una imagen
     */
    private function deleteThumbnails(string $filename, string $thumbnailsPath): void
    {
        if (!is_dir($thumbnailsPath)) {
            return;
        }

        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        foreach (self::THUMBNAIL_SIZES as $sizeName => $size) {
            $thumbnailPath = $thumbnailsPath . $nameWithoutExt . '_' . $sizeName . '.' . $extension;
            if (file_exists($thumbnailPath) && is_file($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }

    /**
     * Genera un slug seguro para el nombre del archivo
     */
    private function generateSlug(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        return $text ?: 'image';
    }

    /**
     * Obtiene la ruta de un thumbnail específico
     */
    public function getThumbnailPath(string $filename, string $size = 'md'): string
    {
        if (!array_key_exists($size, self::THUMBNAIL_SIZES)) {
            $size = 'md'; // Tamaño por defecto
        }

        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        return 'thumbnails/' . $nameWithoutExt . '_' . $size . '.' . $extension;
    }

    /**
     * Obtiene todos los tamaños disponibles
     */
    public function getAvailableSizes(): array
    {
        return self::THUMBNAIL_SIZES;
    }
}