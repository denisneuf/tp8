<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\View;
use think\facade\Session;
use think\facade\Validate;
use think\Request;
use app\model\Category;
use app\validate\CategoryFormValidator;
use think\response\Redirect;
use think\db\exception\ModelNotFoundException;
use app\service\ImageService;

class CategoryController extends BaseController
{
    /**
     * Validador de formularios para categorías
     * 
     * @var CategoryFormValidator
     */
    private CategoryFormValidator $categoryValidator;
    
    /**
     * Servicio de procesamiento de imágenes
     * 
     * @var ImageService
     */
    protected ImageService $imageService;

    /**
     * Inicializa el controlador
     * 
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        // Usar el contenedor de dependencias de ThinkPHP
        $this->categoryValidator = app(CategoryFormValidator::class);
        $this->imageService = app(ImageService::class); // Inyectar el servicio
    }

    /**
     * Muestra la lista de categorías con paginación
     * 
     * @return string Vista renderizada
     */
    public function index(): string
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        $categories = Category::withTrashed()->order('id desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);

        return View::fetch('admin/category/list', [
            'categories' => $categories,
            'success'    => $successMessage,
            'error'      => $errorMessage,
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva categoría
     * 
     * @return string Vista renderizada
     */
    public function create(): string
    {
        return View::fetch('/admin/category/create');
    }

    /**
     * Guarda una nueva categoría en la base de datos
     * 
     * @param Request $request Solicitud HTTP
     * @return Redirect Redirección con mensaje de éxito/error
     */
    public function save(Request $request): Redirect
    {
        $data = $request->post();

        if (!$this->categoryValidator->check($data)) {
            return redirect((string) url('category_create'))->with('error', $this->categoryValidator->getError());
        }

        try {
            Category::create($data);
            return redirect((string) url('category_index'))->with('success', 'Categoría creada correctamente.');
        } catch (\Exception $e) {
            return redirect((string) url('category_create'))->with('error', $e->getMessage());
        }

        return redirect('/admin/category/index');
    }

    /**
     * Muestra el formulario para editar una categoría existente
     * 
     * @param int $id ID de la categoría
     * @return string|Redirect Vista renderizada o redirección en caso de error
     */
    public function edit(int $id): string|Redirect
    {

        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');


        try {
            $category = Category::findOrFail($id);
            View::assign('success', $successMessage);
            View::assign('error', $errorMessage);
            View::assign('category', $category);
            return View::fetch('admin/category/edit');
            
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('category_index'))->with('error', $e->getMessage());
        }

    }

    /**
     * Obtiene las reglas de validación para la actualización de categorías
     * 
     * @param array $data Datos del formulario
     * @param Category $category Instancia de la categoría
     * @param int $id ID de la categoría
     * @return array Reglas de validación
     * @throws \InvalidArgumentException Si falta el campo requerido
     */
    private function getUpdateValidationRules(array $data, Category $category, int $id): array
    {
        // Validar que los datos necesarios estén presentes
        if (!isset($data['txt_short'])) {
            throw new \InvalidArgumentException('El campo txt_short es requerido para la validación');
        }

        $rules = [
            'bs_icon'     => ['max:55'],
            'txt_long'    => ['require', 'max:35'],
            'slug'        => ['require', 'max:55', 'alphaDash', 'unique:categories,slug,' . $id],
            'description' => ['max:500'],
            'pic'         => ['max:35'],
            'bg'          => ['max:35'],
            'visible'     => ['require', 'in:0,1'],
        ];
        
        /**
         * Añadir regla de unicidad condicional para brand_en
         * Solo se valida la unicidad si el valor ha cambiado respecto al original
         * Esto evita errores de validación cuando el usuario no modifica el nombre
         */
        if ($data['txt_short'] !== $category->txt_short) {
            $rules['txt_short'][] = 'unique:categories,txt_short';
        }
        
        return $rules;
    }

    /**
     * Actualiza una categoría existente en la base de datos
     * 
     * @param Request $request Solicitud HTTP
     * @param int $id ID de la categoría
     * @return Redirect Redirección con mensaje de éxito/error
     */
    public function update(Request $request, int $id): Redirect
    {
        $data = $request->post();
        
        try {
            $category = Category::findOrFail($id);

            // OBTENER ARCHIVOS AL INICIO (antes de cualquier procesamiento)
            $file = null;
            if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
                $file = $request->file('pic');
            }

            $bg = null;
            if (isset($_FILES['block_pic']) && $_FILES['block_pic']['error'] === UPLOAD_ERR_OK) {
                $bg = $request->file('block_pic');
            }

            // Definir paths una sola vez
            $basePath = app()->getRootPath() . 'public/static/img/';
            $topicPath = $basePath . 'category/';

            // Inicializar campos de eliminación si no existen
            $data['delete_pic'] = $data['delete_pic'] ?? '0';
            $data['delete_block_pic'] = $data['delete_block_pic'] ?? '0';

            // Obtener reglas de validación específicas para actualización
            $rules = $this->getUpdateValidationRules($data, $category, $id);
            $this->categoryValidator->rule($rules);
            
            if (!$this->categoryValidator->check($data)) {
                return redirect((string) url('category_edit', ['id' => $id]))->with('error', $this->categoryValidator->getError());
            }

            // LÓGICA PARA LA IMAGEN PRINCIPAL (pic)
            if ($data['delete_pic'] == '1') {
                // ELIMINAR imagen existente
                if ($category->pic && file_exists($topicPath . $category->pic)) {
                    $this->imageService->deleteBrandImage($category->pic, $topicPath);
                }
                $data['pic'] = null;
            } else {
                // Verificar si hay nuevo archivo (ya obtenido al inicio)
                if ($file) {
                    // CONFIGURAR el tamaño mínimo
                    $this->imageService->setImageMinDimension(800);

                    try {
                        $data['pic'] = $this->imageService->processSquareImage(
                            $file,
                            $topicPath, 
                            $data['txt_short'],
                            $category->pic
                        );
                    } catch (\RuntimeException $e) {
                        return redirect((string) url('category_edit', ['id' => $id]))->with('error', $e->getMessage());
                    }
                }
            }

            // LÓGICA PARA LA IMAGEN DE BLOQUE (block_pic)
            if ($data['delete_block_pic'] == '1') {
                // ELIMINAR imagen existente
                if ($category->bg && file_exists($topicPath . $category->bg)) {
                    $this->imageService->deleteBrandImage($category->bg, $topicPath);
                }
                $data['bg'] = null;
            } else {
                // Verificar si hay nuevo archivo (ya obtenido al inicio)
                if ($bg) {
                    try {
                        $data['bg'] = $this->imageService->processLandscapeImage(
                            $bg,
                            $topicPath, 
                            $data['txt_short'], 
                            $category->bg
                        );
                    } catch (\RuntimeException $e) {
                        return redirect((string) url('category_edit', ['id' => $id]))->with('error', $e->getMessage());
                    }
                }
            }

            // Actualizar categoria usando la instancia (MÁS EFICIENTE)
            $category->save($data);
            
            return redirect((string) url('category_index'))->with('success', 'Categoría actualizada correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('category_index'))->with('error', 'Categoría no encontrada');
        } catch (\InvalidArgumentException $e) {
            return redirect((string) url('category_edit', ['id' => $id]))->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect((string) url('category_edit', ['id' => $id]))->with('error', $e->getMessage());
        }
    }

    /**
     * Elimina una categoría (soft delete)
     * 
     * @param Request $request Solicitud HTTP
     * @param int $id ID de la categoría
     * @return Redirect Redirección con mensaje de éxito
     */
    public function delete(Request $request, int $id): Redirect
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return redirect((string) url('category_index'))->with('success', 'Categoría eliminada correctamente.');
    }

    /**
     * Restaura una categoría eliminada (soft delete)
     * 
     * @param Request $request Solicitud HTTP
     * @param int $id ID de la categoría
     * @return Redirect Redirección con mensaje de éxito
     */
    public function restore(Request $request, int $id): Redirect
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        return redirect((string) url('category_index'))->with('success', 'Categoría restaurada correctamente.');
    }
}