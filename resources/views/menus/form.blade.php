@extends('layouts.app')

@section('title', $isEdit ? 'Ubah Menu' : 'Tambah Menu Baru')
@section('breadcrumb')
    <a href="{{ route('menus.index') }}" class="hover:text-copper">Beranda / Daftar Menu</a> / {{ $isEdit ? 'Edit Menu' : 'Tambah Baru' }}
@endsection
@section('page_title', $isEdit ? 'Edit Data Menu' : 'Tambah Menu Baru')

@section('header_actions')
    <button type="submit"
            form="menu-main-form"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition cursor-pointer">
        <span>Simpan Menu</span>
    </button>
@endsection

@section('content')
    <div class="mb-4">
        <a href="{{ route('menus.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-taupe hover:text-copper">
            <span>← Kembali ke daftar menu</span>
        </a>
    </div>

    <form id="menu-main-form"
          action="{{ $isEdit ? route('menus.update', $menu->slug) : route('menus.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Left 2/3 Card: Menu Fields --}}
            <div class="lg:col-span-2 bg-white rounded-lg border border-sand p-6 space-y-5 shadow-2xs">
                <div class="pb-3 border-b border-sand">
                    <h2 class="text-sm font-bold text-teak">Spesifikasi Menu</h2>
                    <p class="text-xs text-taupe mt-0.5">Kolom dengan tanda <span class="text-clay">*</span> wajib dilengkapi.</p>
                </div>

                <div>
                    <label for="name" class="block text-xs font-bold text-teak mb-1.5">
                        Nama Menu <span class="text-clay">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           required
                           value="{{ old('name', $menu->name) }}"
                           placeholder="Contoh: Nasi Goreng Kambing"
                           class="w-full rounded-lg border border-sand bg-white px-3.5 py-2 text-sm text-teak placeholder:text-taupe/60 focus:outline-none focus:border-copper">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-xs font-bold text-teak mb-1.5">
                            Kategori Menu <span class="text-clay">*</span>
                        </label>
                        <select id="category_id"
                                name="category_id"
                                required
                                class="w-full rounded-lg border border-sand bg-white px-3.5 py-2 text-sm text-teak focus:outline-none focus:border-copper">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (int) old('category_id', $menu->category_id) === $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}{{ ! $cat->is_active ? ' (Nonaktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-bold text-teak mb-1.5">
                            Harga Jual (Rp) <span class="text-clay">*</span>
                        </label>
                        <input type="number"
                               id="price"
                               name="price"
                               required
                               min="1000"
                               step="500"
                               value="{{ old('price', $menu->price ?? '') }}"
                               placeholder="25000"
                               class="w-full rounded-lg border border-sand bg-white px-3.5 py-2 text-sm text-teak tabular-nums placeholder:text-taupe/60 focus:outline-none focus:border-copper">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold text-teak mb-1.5">
                        Deskripsi / Komposisi
                    </label>
                    <textarea id="description"
                              name="description"
                              rows="3"
                              placeholder="Komposisi bahan atau keterangan singkat sajian..."
                              class="w-full rounded-lg border border-sand bg-white px-3.5 py-2 text-sm text-teak placeholder:text-taupe/60 focus:outline-none focus:border-copper">{{ old('description', $menu->description) }}</textarea>
                </div>

                <div class="pt-4 border-t border-sand/70 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="flex items-start gap-2.5 p-3 rounded-lg border border-sand bg-alabaster/50 cursor-pointer">
                        <input type="checkbox"
                               name="is_available"
                               value="1"
                               {{ old('is_available', $menu->is_available ?? true) ? 'checked' : '' }}
                               class="mt-0.5 w-4 h-4 rounded border-sand accent-copper">
                        <div>
                            <span class="block text-xs font-bold text-teak">Stok Tersedia Hari Ini</span>
                            <span class="block text-[11px] text-taupe mt-0.5">Dapat dipilih oleh kasir pada transaksi baru.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-3 rounded-lg border border-sand bg-alabaster/50 cursor-pointer">
                        <input type="checkbox"
                               name="is_featured"
                               value="1"
                               {{ old('is_featured', $menu->is_featured ?? false) ? 'checked' : '' }}
                               class="mt-0.5 w-4 h-4 rounded border-sand accent-copper">
                        <div>
                            <span class="block text-xs font-bold text-teak">Tandai Menu Unggulan</span>
                            <span class="block text-[11px] text-taupe mt-0.5">Tampil di urutan atas katalog & buku menu.</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Right 1/3 Column: Photo Upload --}}
            <div class="space-y-4">
                <div class="bg-white rounded-lg border border-sand p-5 shadow-2xs">
                    <h2 class="text-sm font-bold text-teak mb-3">Foto Produk</h2>

                    <label for="image-input"
                           class="block rounded-lg border border-dashed border-sand bg-alabaster/70 hover:border-copper p-5 text-center cursor-pointer transition">
                        <div id="image-preview-wrapper" class="{{ $menu->image_url ? '' : 'hidden' }} mb-3">
                            <img id="image-preview"
                                 src="{{ $menu->image_url ?? '' }}"
                                 alt="Pratinjau foto"
                                 class="w-36 h-28 mx-auto rounded-md object-cover border border-sand">
                        </div>

                        <div id="image-placeholder-icon" class="{{ $menu->image_url ? 'hidden' : '' }} w-10 h-10 mx-auto rounded-md bg-sand-soft text-taupe flex items-center justify-center mb-2.5">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                            </svg>
                        </div>

                        <span class="inline-block px-3 py-1.5 rounded-md bg-white border border-sand text-xs font-semibold text-teak">
                            Pilih File Gambar
                        </span>

                        <input type="file"
                               id="image-input"
                               name="image"
                               accept="image/jpeg,image/png,image/webp"
                               class="hidden">
                    </label>

                    @if ($isEdit && $menu->image)
                        <label class="mt-3 flex items-center gap-2 text-xs text-clay cursor-pointer">
                            <input type="checkbox" name="remove_image" value="1" class="rounded border-sand accent-clay">
                            <span>Hapus foto saat disimpan</span>
                        </label>
                    @endif

                    <p class="text-[11px] text-taupe mt-2.5">
                        Format JPG, PNG, atau WEBP (maks. 5 MB).
                    </p>
                </div>

                {{-- Technical Price Snapshot Note --}}
                <div class="rounded-lg bg-white border border-sand p-4 text-xs text-taupe space-y-1">
                    <p class="font-bold text-teak">Ketentuan Harga Katalog</p>
                    <p class="leading-relaxed">
                        Harga disimpan dalam satuan Rupiah penuh sebelum PB1 (10%) dan biaya layanan makan di tempat (5%). Perubahan harga tidak mengubah histori transaksi lama (<em>price snapshot</em>).
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-4 border-t border-sand flex items-center justify-end gap-2.5">
            <a href="{{ route('menus.index') }}"
               class="px-4 py-2 rounded-lg bg-white border border-sand text-xs font-semibold text-teak hover:bg-alabaster transition">
                Batal
            </a>
            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-copper hover:bg-copper-dark text-white text-xs font-semibold shadow-2xs transition cursor-pointer">
                Simpan Menu
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('image-input')?.addEventListener('change', function (event) {
        const file = event.target.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById('image-preview');
            const wrapper = document.getElementById('image-preview-wrapper');
            const icon = document.getElementById('image-placeholder-icon');
            if (img && wrapper) {
                img.src = e.target.result;
                wrapper.classList.remove('hidden');
                icon?.classList.add('hidden');
            }
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush
