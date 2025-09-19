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
        return View::fetch('/admin/category/create');
    }

    /**
     * Guarda una nueva categoría en la base de datos
     * 
     * @param Request $request Solicitud HTTP con los datos del formulario
     * @return \think\response\Redirect
     */
    public function save(Request $request): Redirect
    {
        $data = $request->post();

        // 1. PRIMERO validar (FUERA del try-catch)
        if (!$this->categoryValidator->check($data)) {
            $error = $this->categoryValidator->getError(); // Esto devuelve un array en ThinkPHP 8
            // Devuelve ['code' => campo, 'msg' => mensaje]

            $errorField = $error['code'];
            $errorMessage = $error['msg'];

            // Limpiar solo el campo con error
            $cleanData = $data;
            if (isset($cleanData[$errorField])) {
                $cleanData[$errorField] = '';
            }

            return redirect((string) url('category_create'))
                ->with('old_data', $cleanData)
                ->with('error', $errorMessage)
                ->with('error_field', $errorField);
        }

        // 2. LUEGO procesar archivos (DENTRO del try-catch)
        try {
            // OBTENER ARCHIVOS
            $picFile = null;
            if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
                $picFile = $request->file('pic');
            }

            $bgFile = null;
            if (isset($_FILES['bg']) && $_FILES['bg']['error'] === UPLOAD_ERR_OK) {
                $bgFile = $request->file('bg');
            }

            // Definir paths
            $basePath = app()->getRootPath() . 'public/static/img/';
            $categoryPath = $basePath . 'category/';

            // LÓGICA PARA LA IMAGEN PRINCIPAL (pic)
            if ($picFile) {
                $this->imageService->setImageMinDimension(800);
                $this->imageService->setGenerateThumbnails(false);
                try {
                    $data['pic'] = $this->imageService->processSquareImage(
                        $picFile, 
                        $categoryPath, 
                        $data['txt_short'], 
                        null
                    );
                } catch (\RuntimeException $e) {
                    return redirect((string) url('category_create'))
                        ->with('old_data', $data)
                        ->with('error', $e->getMessage());
                }
            } else {
                $data['pic'] = null;
            }

            // LÓGICA PARA LA IMAGEN DE FONDO (bg)
            if ($bgFile) {
                try {
                    $data['bg'] = $this->imageService->processLandscapeImage(
                        $bgFile, 
                        $categoryPath, 
                        $data['txt_short'], 
                        null
                    );
                } catch (\RuntimeException $e) {
                    return redirect((string) url('category_create'))
                        ->with('old_data', $data)
                        ->with('error', $e->getMessage());
                }
            } else {
                $data['bg'] = null;
            }

            // Crear la categoría en la base de datos
            Category::create($data);
            return redirect((string) url('category_index'))->with('success', 'Categoría creada correctamente.');

        } catch (\Exception $e) {
            // Esto solo captura errores inesperados
            return redirect((string) url('category_create'))
                ->with('old_data', $data)
                ->with('error', 'Error inesperado: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para editar una categoría existente
     * 
     * @param int $id ID de la categoría
     * @return string|Redirect Vista renderizada o redirección en caso de error
     */
    public function edit(int $id): string|Redirect
    {

        $errorMessage = Session::get('error');

        $oldData = Session::get('old_data');
        $errorField = Session::get('error_field');


        try {
            $category = Category::findOrFail($id);
            View::assign('old_data', $oldData);
            View::assign('error_field', $errorField);
            View::assign('error', $errorMessage);
            View::assign('category', $category);
            return View::fetch('admin/category/edit');
            
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('category_index'))->with('error', $e->getMessage());
        }


    }


    /**
     * Actualiza una categoría existente en la base de datos
     * 
     * @param Request $request Solicitud HTTP
     * @param int $id ID de la categoría
     * @return Redirect Redirección con mensaje de éxito/error
     */
    /**
     * Actualiza una categoría existente en la base de datos
     * 
     * @param Request $request Solicitud HTTP
     * @param int $id ID de la categoría
     * @return Redirect Redirección con mensaje de éxito/error
     */
    public function update(Request $request, int $id)
    {
        /** @var Category $category Instancia de la categoría a actualizar */
        $category = Category::findOrFail($id);
        $data = $request->post();

        // OBTENER ARCHIVOS AL INICIO (antes de cualquier procesamiento)
        $picFile = null;
        if (isset($_FILES['pic']) && $_FILES['pic']['error'] === UPLOAD_ERR_OK) {
            $picFile = $request->file('pic');
        }

        $bgFile = null;
        if (isset($_FILES['bg']) && $_FILES['bg']['error'] === UPLOAD_ERR_OK) {
            $bgFile = $request->file('bg');
        }

        // Definir paths una sola vez
        $basePath = app()->getRootPath() . 'public/static/img/';
        $categoryPath = $basePath . 'category/';



        /*
        $data = [
            'name'  => 'thinkphp',
            'age'   => 10,
            'email' => 'thinkphp@qq.com',
        ];
        */

        dump($id); // el id que llega
        dump($data); // datos enviados


        try {
            if (!$this->categoryValidator->sceneUpdate($id)->check($data)) {
                $error = $this->categoryValidator->getError();
                $errorField = $error['code'];
                $errorMessage = $error['msg'];

                // Limpiar solo el campo con error
                $cleanData = $data;
                if (isset($cleanData[$errorField])) {
                    $cleanData[$errorField] = '';
                }

                return redirect((string) url('category_edit', ['id' => $id]))
                    ->with('old_data', $cleanData)
                    ->with('error', $errorMessage)
                    ->with('error_field', $errorField);
                }

        } catch (ValidateException $e) {
            dump($e->getError());
        }



        try {
            // Inicializar campos de eliminación si no existen
            $data['delete_pic'] = $data['delete_pic'] ?? '0';
            $data['delete_bg'] = $data['delete_bg'] ?? '0';

            // LÓGICA PARA LA IMAGEN PRINCIPAL (pic)
            if ($data['delete_pic'] == '1') {
                // ELIMINAR imagen existente
                if ($category->pic && file_exists($categoryPath . $category->pic)) {
                    $this->imageService->deleteBrandImage($category->pic, $categoryPath);
                }
                $data['pic'] = null;
            } else {
                // Verificar si hay nuevo archivo (ya obtenido al inicio)
                if ($picFile) {
                    // CONFIGURAR el tamaño mínimo
                    $this->imageService->setImageMinDimension(800);
                    $this->imageService->setGenerateThumbnails(false);

                    try {
                        $data['pic'] = $this->imageService->processSquareImage(
                            $picFile,
                            $categoryPath, 
                            $data['txt_short'],
                            $category->pic
                        );
                    } catch (\RuntimeException $e) {
                        return redirect((string) url('category_edit', ['id' => $id]))->with('error', $e->getMessage());
                    }
                }
            }

            // LÓGICA PARA LA IMAGEN DE FONDO (bg)
            if ($data['delete_bg'] == '1') {
                // ELIMINAR imagen existente
                if ($category->bg && file_exists($categoryPath . $category->bg)) {
                    $this->imageService->deleteBrandImage($category->bg, $categoryPath);
                }
                $data['bg'] = null;
            } else {
                // Verificar si hay nuevo archivo (ya obtenido al inicio)
                if ($bgFile) {

                    //$this->imageService->setImageMinRatio(1.5);
                    //$this->imageService->setImageMaxRatio(2.5);

                    try {
                        $data['bg'] = $this->imageService->processLandscapeImage(
                            $bgFile,
                            $categoryPath, 
                            $data['txt_short'], 
                            $category->bg
                        );
                    } catch (\RuntimeException $e) {
                        return redirect((string) url('category_edit', ['id' => $id]))->with('error', $e->getMessage());
                    }
                }
            }

            // Actualizar categoría en la base de datos
            $category->save($data);
            return redirect((string) url('category_index'))->with('success', 'Categoría actualizada correctamente.');

        } catch (\InvalidArgumentException $e) {
            return redirect((string) url('category_edit', ['id' => $id]))->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect((string) url('category_edit', ['id' => $id]))->with('error', $e->getMessage());
        }
    }

    /**
     * Elimina permanentemente una categoría de la base de datos
     * 
     * @param Request $request Solicitud HTTP
     * @param int $id ID de la categoría a eliminar permanentemente
     * @return \think\response\Redirect
     */
    public function forceDelete(Request $request, int $id): Redirect
    {
        try {
            $category = Category::onlyTrashed()->findOrFail($id);
            $category->force()->delete();
            return redirect((string) url('category_index'))->with('success', 'Categoría eliminada permanentemente.');
        } catch (ModelNotFoundException $e) {
            return redirect((string) url('category_index'))->with('error', 'Categoría no encontrada o ya fue eliminada permanentemente.');
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