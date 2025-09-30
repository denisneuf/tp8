<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\View;
use think\facade\Session;
use think\Request;
use app\model\Product;
use app\model\Brand;
use app\model\Category;
use app\model\ProductType;
use app\model\ProductSpecialField;
use app\model\ProductSpecialValue;
use app\validate\ProductFormValidator;
use app\service\ImageService;
use think\response\Redirect;
use think\db\exception\ModelNotFoundException;
use think\db\exception\DataNotFoundException;
use RuntimeException;
use InvalidArgumentException;
use Exception;

class ProductController extends BaseController
{
    private ProductFormValidator $productValidator;
    protected ImageService $imageService;

    public function initialize(): void
    {
        parent::initialize();
        $this->productValidator = app(ProductFormValidator::class);
        $this->imageService = app(ImageService::class);
    }

    public function index(): string
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');


        $products = Product::withTrashed(['brand', 'category', 'productType'])->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);

        View::assign([
            'products' => $products,
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);

        return View::fetch('admin/product/list');
    }

    public function create(): string
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');
        $oldData = Session::get('old_data');
        $errorField = Session::get('error_field');

        $brands = Brand::order('brand_en')->select();
        $categories = Category::order('txt_short')->select();
        $productTypes = ProductType::order('name')->select();

        View::assign([
            'brands' => $brands,
            'categories' => $categories,
            'productTypes' => $productTypes,
            'specialFields' => collect(),
            'specialValues' => [],
            'old_data' => $oldData,
            'error_field' => $errorField,
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);

        return View::fetch('admin/product/create');
    }

    public function getSpecialFields(Request $request)
    {
        $productTypeId = $request->get('product_type_id');
        
        if (!$productTypeId) {
            return '<p class="text-muted">Selecciona un tipo de producto</p>';
        }
        
        $fields = ProductSpecialField::where('product_type_id', $productTypeId)->select();
        
        if ($fields->isEmpty()) {
            return '<p class="text-muted">Este tipo de producto no tiene campos especializados</p>';
        }
        
        return View::fetch('admin/product/special_fields_form', [
            'fields' => $fields,
            'values' => []
        ]);
    }

    public function save(Request $request): Redirect
    {
        $data = $request->post();
        $cleanData = $data;

        // 1. Validación del formulario
        if (!$this->productValidator->check($data)) {
            $error = $this->productValidator->getError();
            $errorField = $error['code'];
            $errorMessage = $error['msg'];

            // Limpiar solo el campo con error
            if (isset($cleanData[$errorField])) {
                $cleanData[$errorField] = '';
            }

            return redirect((string) url('product_create'))
                ->with('old_data', $cleanData)
                ->with('error', $errorMessage)
                ->with('error_field', $errorField);
        }

        // 2. Procesar imagen y guardar producto
        try {
            // Obtener archivo de imagen
            $file = null;
            if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
                $file = $request->file('pic');
            }
            
            // Configurar ImageService para productos (1500px mínimo, cuadrada)
            $this->imageService->setImageMinDimension(1500);
            
            // Definir path para imágenes de productos
            $basePath = app()->getRootPath() . 'public/static/img/';
            $productPath = $basePath . 'product/';

            // Procesar imagen si se subió
            if ($file) {
                try {
                    $data['pic'] = $this->imageService->processSquareImage(
                        $file,
                        $productPath,
                        $data['asin'], // Usar asin del producto para el slug
                        null
                    );
                } catch (RuntimeException $e) {
                    return redirect((string) url('product_create'))
                        ->with('old_data', $cleanData)
                        ->with('error', $e->getMessage());
                }
            } else {
                $data['pic'] = null;
            }

            // Crear el producto
            $product = Product::create($data);
            $productId = $product->id;

            // Guardar atributos especiales si existen
            $special = $request->post('special', []);
            foreach ($special as $fieldId => $value) {
                if (!empty($value)) {
                    ProductSpecialValue::create([
                        'product_id' => $productId,
                        'special_field_id' => $fieldId,
                        'value' => $value
                    ]);
                }
            }

            return redirect((string) url('product_index'))->with('success', 'Producto creado correctamente.');

        } catch (RuntimeException | InvalidArgumentException | Exception $e) {
            return redirect((string) url('product_create'))
                ->with('old_data', $cleanData)
                ->with('error', 'Error inesperado: ' . $e->getMessage());
        }
    }

    public function edit(int $id): string|Redirect
    {
        $errorMessage = Session::get('error');
        $oldData = Session::get('old_data');
        $errorField = Session::get('error_field');

        try {
            $product = Product::with(['brand', 'category', 'productType'])->findOrFail($id);
            
            $brands = Brand::order('brand_en')->select();
            $categories = Category::order('txt_short')->select();
            $productTypes = ProductType::order('name')->select();

            // Obtener campos especiales según el tipo de producto
            $specialFields = ProductSpecialField::where('product_type_id', $product->product_type_id)->select();
            $specialValues = ProductSpecialValue::where('product_id', $id)->column('value', 'special_field_id');

            View::assign([
                'product' => $product,
                'brands' => $brands,
                'categories' => $categories,
                'productTypes' => $productTypes,
                'specialFields' => $specialFields,
                'specialValues' => $specialValues,
                'old_data' => $oldData,
                'error_field' => $errorField,
                'error' => $errorMessage,
            ]);

            return View::fetch('admin/product/edit');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_index'))->with('error', 'Producto no encontrado.');
        }
    }

    public function update(Request $request, int $id): Redirect
    {
        try {
            $product = Product::findOrFail($id);
            $data = $request->post();
            $cleanData = $data;

            // 1. Validación del formulario
            //if (!$this->productValidator->check($data)) {
            if (!$this->productValidator->sceneUpdate($id)->check($data)) {
                $error = $this->productValidator->getError();
                $errorField = $error['code'];
                $errorMessage = $error['msg'];

                if (isset($cleanData[$errorField])) {
                    $cleanData[$errorField] = '';
                }

                return redirect((string) url('product_edit', ['id' => $id]))
                    ->with('old_data', $cleanData)
                    ->with('error', $errorMessage)
                    ->with('error_field', $errorField);
            }

            // Obtener archivo de imagen de forma segura
            $file = null;
            if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
                $file = $request->file('pic');
            }
            
            // Solo procesar imagen si se subió un archivo válido
            if ($file) {
                try {
                    // Configurar ImageService para productos
                    $this->imageService->setImageMinDimension(1500);
                    $basePath = app()->getRootPath() . 'public/static/img/';
                    $productPath = $basePath . 'product/';

                    $data['pic'] = $this->imageService->processSquareImage(
                        $file,
                        $productPath,
                        $data['asin'],
                        $product->pic // Imagen anterior para eliminar
                    );
                } catch (RuntimeException $e) {
                    return redirect((string) url('product_edit', ['id' => $id]))
                        ->with('old_data', $cleanData)
                        ->with('error', $e->getMessage());
                }
            } else {
                // Si no se subió nueva imagen, mantener la existente
                $data['pic'] = $product->pic;
            }

            // Actualizar producto
            $product->save($data);

            // Actualizar atributos especiales
            $special = $request->post('special', []);
            foreach ($special as $fieldId => $value) {
                $existing = ProductSpecialValue::where([
                    'product_id' => $id,
                    'special_field_id' => $fieldId
                ])->find();

                if ($existing) {
                    if (!empty($value)) {
                        $existing->value = $value;
                        $existing->save();
                    } else {
                        $existing->delete();
                    }
                } elseif (!empty($value)) {
                    ProductSpecialValue::create([
                        'product_id' => $id,
                        'special_field_id' => $fieldId,
                        'value' => $value
                    ]);
                }
            }

            return redirect((string) url('product_index'))->with('success', 'Producto actualizado correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_index'))->with('error', 'Producto no encontrado.');
        } catch (RuntimeException | InvalidArgumentException | Exception $e) {
            return redirect((string) url('product_edit', ['id' => $id]))
                ->with('old_data', $cleanData)
                ->with('error', 'Error inesperado: ' . $e->getMessage());
        }
    }

    public function delete(int $id): Redirect
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return redirect((string) url('product_index'))->with('success', 'Producto eliminado correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_index'))->with('error', 'Producto no encontrado.');
        } catch (Exception $e) {
            return redirect((string) url('product_index'))->with('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    public function restore(int $id): Redirect
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);
            $product->restore();
            return redirect((string) url('product_index'))->with('success', 'Producto restaurado correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_index'))->with('error', 'Producto no encontrado.');
        } catch (Exception $e) {
            return redirect((string) url('product_index'))->with('error', 'Error al restaurar el producto: ' . $e->getMessage());
        }
    }

    public function forceDelete(int $id): Redirect
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($id);
            
            // Eliminar imagen si existe
            if ($product->pic) {
                $basePath = app()->getRootPath() . 'public/static/img/';
                $productPath = $basePath . 'product/';
                $this->imageService->deleteBrandImage($product->pic, $productPath);
            }
            
            $product->force()->delete();
            return redirect((string) url('product_index'))->with('success', 'Producto eliminado permanentemente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_index'))->with('error', 'Producto no encontrado.');
        } catch (Exception $e) {
            return redirect((string) url('product_index'))->with('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }
}