<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::ordered()->get();

        // Eager Loading: Menu::with('category') prevents N+1 queries across table rows
        $query = Menu::with('category');

        if ($request->filled('category')) {
            $categorySlug = (string) $request->query('category');
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($request->filled('status')) {
            if ($request->query('status') === 'available') {
                $query->where('is_available', true);
            } elseif ($request->query('status') === 'unavailable') {
                $query->where('is_available', false);
            }
        }

        if ($request->filled('featured')) {
            if ($request->query('featured') === 'yes') {
                $query->where('is_featured', true);
            } elseif ($request->query('featured') === 'no') {
                $query->where('is_featured', false);
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $menus = $query
            ->orderByDesc('is_featured')
            ->orderBy('category_id')
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        $totalCatalogCount = Menu::count();

        return view('menus.index', compact('menus', 'categories', 'totalCatalogCount'));
    }

    public function create(Request $request): View
    {
        $categories = Category::ordered()->get();
        $preselectedCategory = null;

        if ($request->filled('category')) {
            $preselectedCategory = $categories->firstWhere('slug', (string) $request->query('category'))?->id;
        }

        return view('menus.form', [
            'menu' => new Menu([
                'category_id' => $preselectedCategory,
                'is_available' => true,
                'is_featured' => false,
            ]),
            'categories' => $categories,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:1000', 'max:10000000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'name.required' => 'Nama menu wajib diisi.',
            'category_id.required' => 'Silakan pilih kategori menu.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'price.required' => 'Harga menu wajib diisi.',
            'price.min' => 'Harga menu minimal Rp 1.000.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('menus', 'public')
            : null;

        $menu = Menu::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image' => $imagePath,
            'is_available' => $request->boolean('is_available'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()
            ->route('menus.index')
            ->with('success', "Menu \"{$menu->name}\" ({$menu->formatted_price}) berhasil disimpan ke katalog.");
    }

    public function edit(Menu $menu): View
    {
        $categories = Category::ordered()->get();

        return view('menus.form', [
            'menu' => $menu,
            'categories' => $categories,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:1000', 'max:10000000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'name.required' => 'Nama menu wajib diisi.',
            'category_id.required' => 'Silakan pilih kategori menu.',
            'price.min' => 'Harga menu minimal Rp 1.000.',
            'image.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $imagePath = $menu->image;
        if ($request->boolean('remove_image') && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('menus', 'public');
        }

        $slug = $menu->name !== $validated['name']
            ? $this->uniqueSlug($validated['name'], $menu->id)
            : $menu->slug;

        $menu->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image' => $imagePath,
            'is_available' => $request->boolean('is_available'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()
            ->route('menus.index')
            ->with('success', "Menu \"{$menu->name}\" berhasil diperbarui.");
    }

    public function toggleAvailability(Menu $menu): RedirectResponse
    {
        $menu->update([
            'is_available' => ! $menu->is_available,
        ]);

        $label = $menu->is_available ? 'Tersedia' : 'Habis';

        return back()->with('success', "Status ketersediaan \"{$menu->name}\" diubah menjadi {$label}.");
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $name = $menu->name;
        $menu->delete(); // SoftDelete: historical order_items stay intact!

        return redirect()
            ->route('menus.index')
            ->with('success', "Menu \"{$name}\" telah diarsipkan dari katalog.");
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'menu';
        $slug = $base;
        $counter = 2;

        while (
            Menu::withTrashed()
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
