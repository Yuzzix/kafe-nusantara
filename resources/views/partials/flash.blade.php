@if (session('success'))
    <div data-kn-autodismiss class="kn-toast mb-2 flex items-center justify-between gap-3 rounded-xl bg-sage-tint border border-sage/25 px-4 py-3 text-sm text-sage font-medium shadow-2xs">
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="window.knDismissToast ? knDismissToast(this.parentElement) : this.parentElement.remove()" class="text-sage/70 hover:text-sage text-xs font-semibold cursor-pointer">Tutup</button>
    </div>
@endif

@if (session('error'))
    <div class="kn-toast mb-2 flex items-center justify-between gap-3 rounded-xl bg-clay-tint border border-clay/25 px-4 py-3 text-sm text-clay font-medium shadow-2xs">
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="window.knDismissToast ? knDismissToast(this.parentElement) : this.parentElement.remove()" class="text-clay/70 hover:text-clay text-xs font-semibold cursor-pointer">Tutup</button>
    </div>
@endif

@if ($errors->any())
    <div class="kn-toast mb-2 rounded-xl bg-clay-tint border border-clay/25 px-4 py-3 text-sm text-clay shadow-2xs">
        <p class="font-semibold mb-1">Mohon periksa kembali isian formulir:</p>
        <ul class="list-disc list-inside space-y-0.5 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
