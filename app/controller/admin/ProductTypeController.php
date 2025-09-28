<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\View;
use think\facade\Session;
use think\Request;
use think\response\Redirect;
use think\db\exception\ModelNotFoundException;
use think\db\exception\DataNotFoundException;
use RuntimeException;
use InvalidArgumentException;
use Exception;
use app\model\ProductType;
use app\model\ProductSpecialField;
use app\model\ProductSpecialValue;
use app\validate\ProductTypeFormValidator;
use app\validate\ProductTypeSpecialFieldValidator;

class ProductTypeController extends BaseController
{
    /**
     * @var ProductTypeFormValidator
     */
    private ProductTypeFormValidator $productTypeValidator;

    /**
     * @var ProductTypeSpecialFieldValidator
     */
    private ProductTypeSpecialFieldValidator $specialFieldValidator;

    /**
     * Inicializa el controlador
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->productTypeValidator = app(ProductTypeFormValidator::class);
        $this->specialFieldValidator = app(ProductTypeSpecialFieldValidator::class);
    }

    /**
     * Muestra el listado de tipos de producto con paginación
     */
    public function index(): string
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        $productTypes = ProductType::withTrashed()->order('id', 'desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);

        View::assign([
            'productTypes' => $productTypes,
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);

        return View::fetch('admin/product_type/list');
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de producto
     */
    public function create(): string
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');
        $oldData = Session::get('old_data');
        $errorField = Session::get('error_field');

        View::assign([
            'old_data' => $oldData,
            'error_field' => $errorField,
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);

        return View::fetch('admin/product_type/create');
    }

    /**
     * Guarda un nuevo tipo de producto en la base de datos
     */
    public function save(Request $request): Redirect
    {
        $data = $request->post();
        $cleanData = $data;

        // 1. PRIMERO validar (FUERA del try-catch)
        if (!$this->productTypeValidator->check($data)) {
            $error = $this->productTypeValidator->getError();
            $errorField = $error['code'] ?? 'unknown';
            $errorMessage = $error['msg'] ?? $error;

            // Limpiar solo el campo con error
            if (isset($cleanData[$errorField])) {
                $cleanData[$errorField] = '';
            }

            return redirect((string) url('product_type_create'))
                ->with('old_data', $cleanData)
                ->with('error', $errorMessage)
                ->with('error_field', $errorField);
        }

        // 2. LUEGO procesar guardado (DENTRO del try-catch)
        try {
            ProductType::create($data);
            return redirect((string) url('product_type_index'))->with('success', 'Tipo de producto creado correctamente.');

        } catch (RuntimeException | InvalidArgumentException | Exception $e) {
            return redirect((string) url('product_type_create'))
                ->with('old_data', $cleanData)
                ->with('error', 'Error al guardar el tipo de producto: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para editar un tipo de producto existente
     */
    public function edit(int $id): string|Redirect
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');
        $oldData = Session::get('old_data');
        $errorField = Session::get('error_field');

        try {
            $productType = ProductType::with('specialFields')->findOrFail($id);
            
            View::assign([
                'productType' => $productType,
                'old_data' => $oldData,
                'error_field' => $errorField,
                'success' => $successMessage,
                'error' => $errorMessage,
            ]);

            return View::fetch('admin/product_type/edit');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_type_index'))->with('error', 'Tipo de producto no encontrado.');
        }
    }

    /**
     * Actualiza un tipo de producto existente en la base de datos
     */
    public function update(Request $request, int $id): Redirect
    {
        try {
            $productType = ProductType::findOrFail($id);
            $data = $request->post();
            $cleanData = $data;

            // 1. PRIMERO validar (FUERA del try-catch)
            if (!$this->productTypeValidator->sceneUpdate($id)->check($data)) {
                $error = $this->productTypeValidator->getError();
                $errorField = $error['code'] ?? 'unknown';
                $errorMessage = $error['msg'] ?? $error;

                // Limpiar solo el campo con error
                if (isset($cleanData[$errorField])) {
                    $cleanData[$errorField] = '';
                }

                return redirect((string) url('product_type_edit', ['id' => $id]))
                    ->with('old_data', $cleanData)
                    ->with('error', $errorMessage)
                    ->with('error_field', $errorField);
            }

            // 2. LUEGO procesar actualización
            $productType->save($data);
            return redirect((string) url('product_type_index'))->with('success', 'Tipo de producto actualizado correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_type_index'))->with('error', 'Tipo de producto no encontrado.');
        } catch (RuntimeException | InvalidArgumentException | Exception $e) {
            return redirect((string) url('product_type_edit', ['id' => $id]))
                ->with('old_data', $cleanData ?? [])
                ->with('error', 'Error al actualizar el tipo de producto: ' . $e->getMessage());
        }
    }

    /**
     * Añade un nuevo campo especializado a un tipo de producto
     */
    public function addField(Request $request, int $id): Redirect
    {
        $data = $request->post();
        $cleanData = $data;

        try {
            // Validar que el tipo de producto existe
            $productType = ProductType::findOrFail($id);

            // 1. PRIMERO validar el campo especializado
            if (!$this->specialFieldValidator->check($data)) {
                $error = $this->specialFieldValidator->getError();
                $errorField = $error['code'] ?? 'unknown';
                $errorMessage = $error['msg'] ?? $error;

                return redirect((string) url('product_type_edit', ['id' => $id]))
                    ->with('error', $errorMessage);
            }

            // 2. LUEGO crear el campo
            $field = new ProductSpecialField();
            $field->name = $data['name'];
            $field->slug = $data['slug'];
            $field->data_type = $data['data_type'];
            $field->unit = $data['unit'] ?? null;
            $field->required = isset($data['required']);
            $field->product_type_id = $id;
            $field->save();

            return redirect((string) url('product_type_edit', ['id' => $id]))
                ->with('success', 'Campo especializado añadido correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_type_index'))->with('error', 'Tipo de producto no encontrado.');
        } catch (RuntimeException | InvalidArgumentException | Exception $e) {
            return redirect((string) url('product_type_edit', ['id' => $id]))
                ->with('error', 'Error al añadir el campo: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza un campo especializado existente
     */
    public function updateField(Request $request, int $id): Redirect
    {
        $data = $request->post();
        $cleanData = $data;

        try {
            $field = ProductSpecialField::findOrFail($id);

            // 1. PRIMERO validar (usando escena update para quitar unique)
            //if (!$this->specialFieldValidator->scene('update')->check($data)) {
            if (!$this->specialFieldValidator->sceneUpdate($id)->check($data)) {
                $error = $this->specialFieldValidator->getError();
                $errorField = $error['code'] ?? 'unknown';
                $errorMessage = $error['msg'] ?? $error;

                return redirect((string) url('product_type_edit', ['id' => $field->product_type_id]))
                    ->with('error', $errorMessage);
            }

            // 2. LUEGO actualizar el campo
            $field->name = $data['name'];
            $field->slug = $data['slug'];
            $field->data_type = $data['data_type'];
            $field->unit = $data['unit'] ?? null;
            $field->required = isset($data['required']);
            $field->save();

            return redirect((string) url('product_type_edit', ['id' => $field->product_type_id]))
                ->with('success', 'Campo actualizado correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_type_index'))->with('error', 'Campo no encontrado.');
        } catch (RuntimeException | InvalidArgumentException | Exception $e) {
            return redirect((string) url('product_type_edit', ['id' => $field->product_type_id ?? $id]))
                ->with('error', 'Error al actualizar el campo: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un campo especializado
     */
    public function deleteField(Request $request, int $id): Redirect
    {
        try {
            $field = ProductSpecialField::findOrFail($id);
            $productTypeId = $field->product_type_id;

            // Eliminar también los valores asociados
            ProductSpecialValue::where('special_field_id', $id)->delete();
            
            // Eliminar el campo
            $field->delete();

            return redirect((string) url('product_type_edit', ['id' => $productTypeId]))
                ->with('success', 'Campo eliminado correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_type_index'))->with('error', 'Campo no encontrado.');
        } catch (Exception $e) {
            return redirect((string) url('product_type_edit', ['id' => $productTypeId ?? $id]))
                ->with('error', 'Error al eliminar el campo: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un tipo de producto (soft delete)
     */
    public function delete(int $id): Redirect
    {
        try {
            $productType = ProductType::findOrFail($id);
            $productType->delete();
            return redirect((string) url('product_type_index'))->with('success', 'Tipo de producto eliminado correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_type_index'))->with('error', 'Tipo de producto no encontrado.');
        } catch (Exception $e) {
            return redirect((string) url('product_type_index'))->with('error', 'Error al eliminar el tipo de producto: ' . $e->getMessage());
        }
    }

    /**
     * Restaura un tipo de producto previamente eliminado
     */
    public function restore(int $id): Redirect
    {
        try {
            $productType = ProductType::onlyTrashed()->findOrFail($id);
            $productType->restore();
            return redirect((string) url('product_type_index'))->with('success', 'Tipo de producto restaurado correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('product_type_index'))->with('error', 'Tipo de producto no encontrado.');
        } catch (Exception $e) {
            return redirect((string) url('product_type_index'))->with('error', 'Error al restaurar el tipo de producto: ' . $e->getMessage());
        }
    }
}