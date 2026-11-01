@extends('layouts.app')

@section('title', $isEdit ? 'Ubah Kategori' : 'Tambah Kategori')
@section('breadcrumb')
    <a href="{{ route('categories.index') }}" class="hover:text-copper">Ruang kerja / Kategori Menu</a> / {{ $isEdit ? 'Ubah Kategori' : 'Tambah Kategori' }}
@endsection
@section('page_title', $isEdit ? 'Ubah Kategori' : 'Tambah Kategori Baru')

@section('content')
    <div class="max-w-3xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-teak">
                    {{ $isEdit ? 'Perbarui kelompok sajian' : 'Kelompok sajian baru' }}
                </h2>
                <p class="text-sm text-taupe mt-0.5">
                    Kategori aktif akan langsung tampil pada pilihan pembuatan pesanan kasir.
                </p>
            </div>
            <a href="{{ $isEdit ? route('categories.show', $category->slug) : route('categories.index') }}"
               class="text-sm font-medium text-taupe hover:text-teak">
                ← Kembali
            </a>
        </div>

        <form action="{{ $isEdit ? route('categories.update', $category->slug) : route('categories.store') }}"
              method="POST"
              class="bg-white rounded-2xl border border-sand p-6 sm:p-8 space-y-6">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-sm font-semibold text-teak mb-1.5">
                    Nama Kategori <span class="text-clay">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       required
                       value="{{ old('name', $category->name) }}"
                       placeholder="Contoh: Kopi Spesialti, Paket Sarapan..."
                       class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-sm text-teak focus:outline-none focus:border-copper focus:ring-2 focus:ring-copper/15">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="icon" class="block text-sm font-semibold text-teak mb-1.5">
                        Ikon / Simbol (Opsional)
                    </label>
                    <input type="text"
                           id="icon"
                           name="icon"
                           value="{{ old('icon', $category->icon) }}"
                           placeholder="Contoh: ☕ atau 🍛"
                           class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-sm text-teak focus:outline-none focus:border-copper focus:ring-2 focus:ring-copper/15">
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-teak mb-1.5">
                        Urutan Tampil
                    </label>
                    <input type="number"
                           id="sort_order"
                           name="sort_order"
                           min="0"
                           value="{{ old('sort_order', $category->sort_order) }}"
                           class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-sm text-teak tabular-nums focus:outline-none focus:border-copper focus:ring-2 focus:ring-copper/15">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-teak mb-1.5">
                    Deskripsi
                </label>
                <textarea id="description"
                          name="description"
                          rows="3"
                          placeholder="Jelaskan karakter kelompok menu ini..."
                          class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-sm text-teak focus:outline-none focus:border-copper focus:ring-2 focus:ring-copper/15">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="pt-2 border-t border-sand/70">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded border-sand text-copper focus:ring-copper">
                    <div>
                        <span class="block text-sm font-semibold text-teak">Kategori Aktif</span>
                        <span class="block text-xs text-taupe">Nonaktifkan jika kategori ini bersifat musiman dan belum disajikan saat ini.</span>
                    </div>
                </label>
            </div>

            <div class="pt-4 border-t border-sand flex items-center justify-between gap-4">
                <div>
                    @if ($isEdit && ($category->menus_count ?? 0) === 0)
                        <button type="submit"
                                form="delete-category-form"
                                onclick="return confirm('Hapus kategori ini?')"
                                class="text-xs font-semibold text-clay hover:underline">
                            Hapus Kategori Kosong
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ $isEdit ? route('categories.show', $category->slug) : route('categories.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-sand bg-white text-sm font-semibold text-teak hover:bg-alabaster transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold shadow-sm transition">
                        ✓ Simpan Kategori
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
