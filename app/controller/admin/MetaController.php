<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\facade\View;
use think\facade\Session;
use think\Request;
use app\model\Meta;
use app\validate\MetaFormValidator;
use think\response\Redirect;
use think\db\exception\ModelNotFoundException;
use think\db\exception\DataNotFoundException;
use RuntimeException;
use InvalidArgumentException;
use Exception;

class MetaController extends BaseController
{
    /**
     * @var MetaFormValidator
     */
    private MetaFormValidator $metaValidator;

    /**
     * Inicializa el controlador
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->metaValidator = app(MetaFormValidator::class);
    }

    /**
     * Muestra el listado de metas con paginación
     */
    public function index(): string
    {
        $successMessage = Session::get('success');
        $errorMessage = Session::get('error');

        $metas = Meta::withTrashed()->order('id', 'desc')->paginate([
            'list_rows' => 10,
            'query'     => request()->param(),
        ]);

        View::assign([
            'metas' => $metas,
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);

        return View::fetch('/admin/meta/list');
    }

    /**
     * Muestra el formulario para crear una nueva meta
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

        return View::fetch('/admin/meta/create');
    }

    /**
     * Guarda una nueva meta en la base de datos
     */
    public function save(Request $request): Redirect
    {
        $data = $request->post();
        $cleanData = $data;

        // 1. PRIMERO validar (FUERA del try-catch)
        if (!$this->metaValidator->check($data)) {
            $error = $this->metaValidator->getError();
            $errorField = $error['code'];
            $errorMessage = $error['msg'];

            // Limpiar solo el campo con error
            if (isset($cleanData[$errorField])) {
                $cleanData[$errorField] = '';
            }

            return redirect((string) url('meta_create'))
                ->with('old_data', $cleanData)
                ->with('error', $errorMessage)
                ->with('error_field', $errorField);
        }

        // 2. LUEGO procesar guardado (DENTRO del try-catch)
        try {
            Meta::create($data);
            return redirect((string) url('meta_index'))->with('success', 'Meta creada correctamente.');

        } catch (RuntimeException | InvalidArgumentException | Exception $e) {
            return redirect((string) url('meta_create'))
                ->with('old_data', $cleanData)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para editar una meta existente
     */
    public function edit(int $id): string|Redirect
    {
        $errorMessage = Session::get('error');
        $oldData = Session::get('old_data');
        $errorField = Session::get('error_field');

        try {
            $meta = Meta::findOrFail($id);
            
            View::assign([
                'meta' => $meta,
                'old_data' => $oldData,
                'error_field' => $errorField,
                'error' => $errorMessage,
            ]);

            return View::fetch('/admin/meta/edit');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('meta_index'))->with('error', 'Meta no encontrada.');
        }
    }

    /**
     * Actualiza una meta existente en la base de datos
     */
    public function update(Request $request, int $id): Redirect
    {
        $meta = Meta::findOrFail($id);
        $data = $request->post();
        $cleanData = $data;

        // 1. PRIMERO validar (FUERA del try-catch)
        if (!$this->metaValidator->sceneUpdate($id)->check($data)) {
            $error = $this->metaValidator->getError();
            $errorField = $error['code'];
            $errorMessage = $error['msg'];

            // Limpiar solo el campo con error
            if (isset($cleanData[$errorField])) {
                $cleanData[$errorField] = '';
            }

            return redirect((string) url('meta_edit', ['id' => $id]))
                ->with('old_data', $cleanData)
                ->with('error', $errorMessage)
                ->with('error_field', $errorField);
        }

        try {
            $meta->save($data);
            return redirect((string) url('meta_index'))->with('success', 'Meta actualizada correctamente.');

        } catch (RuntimeException | InvalidArgumentException | Exception $e) {
            return redirect((string) url('meta_edit', ['id' => $id]))
                ->with('old_data', $cleanData)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Elimina una meta (soft delete)
     */
    public function delete(int $id): Redirect
    {
        try {
            $meta = Meta::findOrFail($id);
            $meta->delete();
            return redirect((string) url('meta_index'))->with('success', 'Meta eliminada correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('meta_index'))->with('error', 'Meta no encontrada.');
        } catch (Exception $e) {
            return redirect((string) url('meta_index'))->with('error', 'Error al eliminar la meta: ' . $e->getMessage());
        }
    }

    /**
     * Elimina permanentemente una meta
     */
    public function forceDelete(int $id): Redirect
    {
        try {
            $meta = Meta::onlyTrashed()->findOrFail($id);
            $meta->force()->delete();
            return redirect((string) url('meta_index'))->with('success', 'Meta eliminada permanentemente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('meta_index'))->with('error', 'Meta no encontrada.');
        } catch (Exception $e) {
            return redirect((string) url('meta_index'))->with('error', 'Error al eliminar la meta: ' . $e->getMessage());
        }
    }

    /**
     * Restaura una meta previamente eliminada
     */
    public function restore(int $id): Redirect
    {
        try {
            $meta = Meta::onlyTrashed()->findOrFail($id);
            $meta->restore();
            return redirect((string) url('meta_index'))->with('success', 'Meta restaurada correctamente.');

        } catch (ModelNotFoundException $e) {
            return redirect((string) url('meta_index'))->with('error', 'Meta no encontrada.');
        } catch (Exception $e) {
            return redirect((string) url('meta_index'))->with('error', 'Error al restaurar la meta: ' . $e->getMessage());
        }
    }
}