@extends('layouts.app')

@section('title', $isEdit ? 'Ubah Kategori' : 'Tambah Kategori')
@section('breadcrumb')
    <a href="{{ route('categories.index') }}" class="hover:text-copper">Beranda / Kategori Menu</a> / {{ $isEdit ? 'Ubah' : 'Tambah Baru' }}
@endsection
@section('page_title', $isEdit ? 'Ubah Kategori Menu' : 'Tambah Kategori Menu')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-4">
            <a href="{{ $isEdit ? route('categories.show', $category->slug) : route('categories.index') }}"
               class="text-xs font-semibold text-taupe hover:text-copper">
                ← Kembali ke daftar kategori
            </a>
        </div>

        <form action="{{ $isEdit ? route('categories.update', $category->slug) : route('categories.store') }}"
              method="POST"
              class="bg-white rounded-lg border border-sand p-6 space-y-5 shadow-2xs">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-xs font-bold text-teak mb-1.5">
                    Nama Kategori <span class="text-clay">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       required
                       value="{{ old('name', $category->name) }}"
                       placeholder="Contoh: Kopi Spesialti, Paket Sarapan..."
                       class="w-full rounded-lg border border-sand bg-white px-3.5 py-2 text-sm text-teak focus:outline-none focus:border-copper">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="icon" class="block text-xs font-bold text-teak mb-1.5">
                        Simbol / Ikon (Opsional)
                    </label>
                    <input type="text"
                           id="icon"
                           name="icon"
                           value="{{ old('icon', $category->icon) }}"
                           placeholder="Contoh: ☕"
                           class="w-full rounded-lg border border-sand bg-white px-3.5 py-2 text-sm text-teak focus:outline-none focus:border-copper">
                </div>

                <div>
                    <label for="sort_order" class="block text-xs font-bold text-teak mb-1.5">
                        Urutan Tampil
                    </label>
                    <input type="number"
                           id="sort_order"
                           name="sort_order"
                           min="0"
                           value="{{ old('sort_order', $category->sort_order) }}"
                           class="w-full rounded-lg border border-sand bg-white px-3.5 py-2 text-sm text-teak tabular-nums focus:outline-none focus:border-copper">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-bold text-teak mb-1.5">
                    Keterangan Singkat
                </label>
                <textarea id="description"
                          name="description"
                          rows="3"
                          placeholder="Deskripsi kategori menu..."
                          class="w-full rounded-lg border border-sand bg-white px-3.5 py-2 text-sm text-teak focus:outline-none focus:border-copper">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="pt-2 border-t border-sand/70">
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 rounded border-sand accent-copper">
                    <div>
                        <span class="block text-xs font-bold text-teak">Tampilkan sebagai Kategori Aktif</span>
                        <span class="block text-xs text-taupe">Jika dimatikan, seluruh menu di kategori ini disembunyikan dari form kasir.</span>
                    </div>
                </label>
            </div>

            <div class="pt-4 border-t border-sand flex items-center justify-between gap-4">
                <div>
                    @if ($isEdit && ($category->menus_count ?? 0) === 0)
                        <button type="submit"
                                form="delete-category-form"
                                onclick="return confirm('Hapus kategori kosong ini?')"
                                class="text-xs font-semibold text-clay hover:underline cursor-pointer">
                            Hapus Kategori
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-2.5">
                    <a href="{{ $isEdit ? route('categories.show', $category->slug) : route('categories.index') }}"
                       class="px-4 py-2 rounded-lg border border-sand bg-white text-xs font-semibold text-teak hover:bg-alabaster transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition cursor-pointer">
                        Simpan Kategori
                    </button>
                </div>
            </div>
        </form>

        @if ($isEdit && ($category->menus_count ?? 0) === 0)
            <form id="delete-category-form" action="{{ route('categories.destroy', $category->slug) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
@endsection
