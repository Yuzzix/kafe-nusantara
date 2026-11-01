<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        // Eager-load counts of menus and available menus to avoid N+1 queries
        $categories = Category::ordered()
            ->withCount(['menus', 'availableMenus'])
            ->get();

        $activeCategories = $categories->where('is_active', true)->values();
        $inactiveCategories = $categories->where('is_active', false)->values();

        $totalMenus = Menu::count();
        $activeMenusCount = Menu::whereHas('category', fn ($q) => $q->where('is_active', true))->count();
        $inactiveMenusCount = $totalMenus - $activeMenusCount;

        return view('categories.index', compact(
            'categories',
            'activeCategories',
            'inactiveCategories',
            'totalMenus',
            'activeMenusCount',
            'inactiveMenusCount',
        ));
    }

    public function create(): View
    {
        $nextOrder = ((int) Category::max('sort_order')) + 1;

        return view('categories.form', [
            'category' => new Category(['is_active' => true, 'sort_order' => $nextOrder]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
        ]);

        $slug = $this->uniqueSlug($validated['name']);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?: '🍽️',
            'sort_order' => $validated['sort_order'] ?? (((int) Category::max('sort_order')) + 1),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('categories.show', $category->slug)
            ->with('success', "Kategori \"{$category->name}\" berhasil ditambahkan.");
    }

    public function show(Category $category): View
    {
        // Master-Detail: eager load all child menus belonging to this category
        $category->load([
            'menus' => fn ($query) => $query->orderByDesc('is_featured')->orderBy('name'),
        ]);

        $availableCount = $category->menus->where('is_available', true)->count();
        $missingPhotoCount = $category->menus->whereNull('image')->count();

        return view('categories.show', compact('category', 'availableCount', 'missingPhotoCount'));
    }

    public function edit(Category $category): View
    {
        $category->loadCount('menus');

        return view('categories.form', [
            'category' => $category,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
        ]);

        $slug = $category->name !== $validated['name']
            ? $this->uniqueSlug($validated['name'], $category->id)
            : $category->slug;

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?: $category->icon,
            'sort_order' => $validated['sort_order'] ?? $category->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('categories.show', $category->slug)
            ->with('success', "Kategori \"{$category->name}\" berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->menus()->exists()) {
            return back()->with(
                'error',
                "Kategori \"{$category->name}\" masih memiliki menu aktif/tersimpan. Nonaktifkan kategori atau pindahkan menunya terlebih dahulu."
            );
        }

        $name = $category->name;
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', "Kategori \"{$name}\" berhasil dihapus.");
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $counter = 2;

        while (
            Category::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
