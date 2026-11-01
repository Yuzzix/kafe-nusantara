@extends('layouts.app')

@section('title', $isEdit ? 'Ubah Menu' : 'Tambah Menu Baru')
@section('breadcrumb')
    <a href="{{ route('menus.index') }}" class="hover:text-copper">Ruang kerja / Daftar Menu</a> / {{ $isEdit ? 'Ubah Menu' : 'Tambah Menu Baru' }}
@endsection
@section('page_title', $isEdit ? 'Ubah Menu' : 'Tambah Menu Baru')

@section('header_actions')
    <button type="submit"
            form="menu-main-form"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold shadow-sm transition cursor-pointer">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/>
        </svg>
        <span>Simpan Menu</span>
    </button>
@endsection

@section('content')
    {{-- Page Intro (Matches PDF Page 4) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-teak">
                {{ $isEdit ? 'Perbarui informasi sajian' : 'Sajian baru untuk pelanggan' }}
            </h2>
            <p class="text-sm text-taupe mt-0.5">
                Lengkapi informasi menu sebelum ditampilkan kepada staf kasir.
            </p>
        </div>

        <a href="{{ route('menus.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-taupe hover:text-teak">
            <span>←</span>
            <span>Kembali ke daftar menu</span>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left 2/3 Card: Basic Info & Settings --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-sand p-6 sm:p-8 space-y-6">
                <div>
                    <h3 class="text-base font-bold text-teak">Informasi dasar</h3>
                    <p class="text-xs text-taupe mt-0.5">Kolom bertanda <span class="text-clay">*</span> wajib diisi.</p>
                </div>

                {{-- Menu Name --}}
                <div>
                    <label for="name" class="block text-xs font-semibold text-teak mb-1.5">
                        Nama Menu <span class="text-clay">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           required
                           value="{{ old('name', $menu->name) }}"
                           placeholder="Masukkan nama menu"
                           class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-sm text-teak placeholder:text-taupe/60 focus:outline-none focus:border-copper focus:ring-2 focus:ring-copper/15">
                </div>

                {{-- Category Dropdown & Rupiah Price --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-xs font-semibold text-teak mb-1.5">
                            Kategori <span class="text-clay">*</span>
                        </label>
                        <select id="category_id"
                                name="category_id"
                                required
                                class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-sm text-teak focus:outline-none focus:border-copper focus:ring-2 focus:ring-copper/15">
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (int) old('category_id', $menu->category_id) === $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}{{ ! $cat->is_active ? ' (Tidak aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-semibold text-teak mb-1.5">
                            Harga (Rp) <span class="text-clay">*</span>
                        </label>
                        <input type="number"
                               id="price"
                               name="price"
                               required
                               min="1000"
                               step="500"
                               value="{{ old('price', $menu->price ?? '') }}"
                               placeholder="Contoh: 25000"
                               class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-sm text-teak tabular-nums placeholder:text-taupe/60 focus:outline-none focus:border-copper focus:ring-2 focus:ring-copper/15">
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-xs font-semibold text-teak mb-1.5">
                        Deskripsi
                    </label>
                    <textarea id="description"
                              name="description"
                              rows="4"
                              placeholder="Ceritakan bahan utama dan karakter rasa sajian..."
                              class="w-full rounded-xl border border-sand bg-white px-4 py-2.5 text-sm text-teak placeholder:text-taupe/60 focus:outline-none focus:border-copper focus:ring-2 focus:ring-copper/15">{{ old('description', $menu->description) }}</textarea>
                </div>

                {{-- Menu Toggles --}}
                <div class="pt-4 border-t border-sand/70 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-teak">Pengaturan menu</h4>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox"
                               name="is_available"
                               value="1"
                               {{ old('is_available', $menu->is_available ?? true) ? 'checked' : '' }}
                               class="mt-0.5 w-4 h-4 rounded border-sand accent-copper">
                        <div>
                            <span class="block text-sm font-semibold text-teak">Tersedia Hari Ini</span>
                            <span class="block text-xs text-taupe">Menu dapat dipilih saat membuat pesanan baru.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox"
                               name="is_featured"
                               value="1"
                               {{ old('is_featured', $menu->is_featured ?? false) ? 'checked' : '' }}
                               class="mt-0.5 w-4 h-4 rounded border-sand accent-copper">
                        <div>
                            <span class="block text-sm font-semibold text-teak">Menu Unggulan (Best Seller)</span>
                            <span class="block text-xs text-taupe">Tambahkan lencana Best Seller pada menu ini.</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Right 1/3 Column: Photo Upload + Guidance Tip Card --}}
            <div class="space-y-5">
                <div class="bg-white rounded-2xl border border-sand p-6">
                    <h3 class="text-base font-bold text-teak mb-4">Unggah Foto</h3>

                    <label for="image-input"
                           class="block rounded-xl border-2 border-dashed border-sand bg-alabaster/70 hover:border-copper/50 p-6 text-center cursor-pointer transition">
                        <div id="image-preview-wrapper" class="{{ $menu->image ? '' : 'hidden' }} mb-4">
                            <img id="image-preview"
                                 src="{{ $menu->image ? asset('storage/'.$menu->image) : '' }}"
                                 alt="Pratinjau foto"
                                 class="w-36 h-36 mx-auto rounded-xl object-cover border border-sand">
                        </div>

                        <div id="image-placeholder-icon" class="{{ $menu->image ? 'hidden' : '' }} w-11 h-11 mx-auto rounded-xl bg-copper-tint text-copper flex items-center justify-center mb-3">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                            </svg>
                        </div>

                        <p class="text-xs font-bold text-teak">Tarik foto ke sini</p>
                        <p class="text-[11px] text-taupe mt-0.5 mb-3">atau pilih dari perangkat</p>

                        <span class="inline-block px-3.5 py-1.5 rounded-lg bg-white border border-sand text-xs font-semibold text-teak shadow-2xs">
                            Pilih Foto
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
                            <span>Hapus foto saat ini</span>
                        </label>
                    @endif

                    <p class="text-[11px] text-taupe mt-3 leading-relaxed">
                        JPG, PNG, atau WEBP, maksimal 5 MB. Rasio 1:1 disarankan. Foto bersifat opsional.
                    </p>
                </div>

                {{-- Tip Card (Matches PDF Page 4) --}}
                <div class="rounded-2xl bg-sand-soft/85 border border-sand p-5">
                    <div class="w-8 h-8 rounded-lg bg-white/80 text-copper flex items-center justify-center mb-2.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.75c.6.54 1 1.33 1 2.15V17h6v-.1c0-.82.4-1.61 1-2.15A7 7 0 0 0 12 2Z"/>
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-teak">Sajikan informasi yang jelas</h4>
                    <p class="text-xs text-taupe mt-1 leading-relaxed">
                        Gunakan nama yang mudah dikenali, harga per porsi, dan deskripsi singkat untuk membantu staf melayani dengan tepat.
                    </p>
                </div>
            </div>
        </div>

        {{-- Bottom Bar Actions (Matches PDF Page 4) --}}
        <div class="mt-6 pt-4 border-t border-sand flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <p class="text-xs text-taupe">
                {{ $isEdit ? 'Perubahan akan langsung diperbarui pada katalog kasir.' : 'Menu belum disimpan.' }}
            </p>

            <div class="flex items-center gap-3">
                <a href="{{ route('menus.index') }}"
                   class="px-5 py-2.5 rounded-xl bg-white border border-sand text-sm font-semibold text-teak hover:bg-alabaster transition">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-copper hover:bg-copper-dark text-white text-sm font-semibold shadow-sm transition cursor-pointer">
                    <span>✓</span>
                    <span>Simpan Menu</span>
                </button>
            </div>
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
