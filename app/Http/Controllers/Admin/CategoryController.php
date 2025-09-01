<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

//Models:
use App\Models\Category;

// Policies
use App\Policies\CategoryPolicy;

//Requests:
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;

class CategoryController extends Controller{
    /**
     * 1. Listado de categorías.
     * 2. Formulario nueva categoría.
     * 3. Guardar nueva categoría.
     */

    /**
     * 1. Listado de categorías.
     */
    public function index(Request $request){
        $this->authorize('viewAny', Category::class);

        $search = (string) $request->get('search', '');
        $categories = Category::query()
            ->search($search)
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Categories/Index', [
            'filters'    => ['search' => $search],
            'categories' => $categories,
            'title'      => __('Categorías'),
            'subtitle'   => __('Gestión de categorías'),
            'actions'    => [
                ['text' => __('Nueva categoría'), 'icon' => 'la-plus', 'url' => 'categories.create'],
            ],
        ]);
    }

    /**
     * 2. Formulario nueva categoría.
     */
    public function create(){
        $this->authorize('create', Category::class);

        return Inertia::render('Admin/Categories/Create', [
            'category' => null,
            'title'    => __('Nueva categoría'),
            'actions'  => [],
        ]);
    }

    /**
     * 3. Guardar nueva categoría.
     */
    public function store(CategoryStoreRequest $request){
        $this->authorize('create', Category::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id ?? null;

        $category = Category::create($data);

        return redirect()
            ->route('categories.edit', $category)
            ->with('success', __('Categoría creada correctamente.'));
    }

    public function edit(Category $category){
        $this->authorize('update', $category);

        return Inertia::render('Admin/Categories/Form', [
            'category' => $category,
            'title'    => __('Editar categoría'),
            'actions'  => [
                [
                    'text'    => __('Eliminar'),
                    'icon'    => 'la-trash',
                    'method'  => 'delete',
                    'url'     => 'categories.destroy',
                    'params'  => [$category->id],
                    'title'   => __('¿Eliminar?'),
                    'message' => __('Esta acción no se puede deshacer.'),
                ]
            ],
        ]);
    }

    public function update(CategoryUpdateRequest $request, Category $category){
        $this->authorize('update', $category);

        $category->update($request->validated());

        return back()->with('success', __('Cambios guardados.'));
    }

    public function destroy(Category $category){
        $this->authorize('delete', $category);

        $category->delete();

        return redirect()->route('categories.index')->with('success', __('Categoría eliminada.'));
    }

    /**
     * (Opcional) Endpoint genérico para asociar categorías a cualquier modelo polimórfico.
     * POST /admin/categories/attach
     * body: { model_type: "App\\Models\\Company", model_id: 1, category_ids: [2,3] }
     */
    public function attachTo(Request $request){
        $this->authorize('create', Category::class);

        $validated = $request->validate([
            'model_type'   => ['required', 'string'],
            'model_id'     => ['required', 'integer'],
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'mode'         => ['nullable', 'in:sync,attach,detach'], // comportamiento
        ]);

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = app($validated['model_type'])::query()->findOrFail($validated['model_id']);

        if (!method_exists($model, 'categories')) {
            abort(422, 'El modelo indicado no implementa la relación categories().');
        }

        $mode = $validated['mode'] ?? 'sync';
        $ids = $validated['category_ids'];

        match ($mode) {
            'attach' => $model->categories()->syncWithoutDetaching($ids),
            'detach' => $model->categories()->detach($ids),
            default  => $model->categories()->sync($ids),
        };

        return response()->json(['ok' => true]);
    }
}
