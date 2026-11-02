@props([
    'payload' => 'KAFE-NUSANTARA',
    'size' => 164,
])

@php
    $grid = 25;
    $hashBytes = hash('sha256', (string) $payload, true).hash('sha256', 'KN:'.(string) $payload, true);
    $bitIndex = 0;

    $isFinder = static function (int $r, int $c, int $top, int $left): ?bool {
        $dr = $r - $top;
        $dc = $c - $left;
        if ($dr < -1 || $dr > 7 || $dc < -1 || $dc > 7) {
            return null;
        }
        if ($dr === -1 || $dr === 7 || $dc === -1 || $dc === 7) {
            return false; // Quiet separator
        }
        if ($dr === 0 || $dr === 6 || $dc === 0 || $dc === 6) {
            return true; // Outer ring
        }
        if ($dr >= 2 && $dr <= 4 && $dc >= 2 && $dc <= 4) {
            return true; // Inner 3x3 eye
        }

        return false;
    };

    $cells = [];
    for ($r = 0; $r < $grid; $r++) {
        for ($c = 0; $c < $grid; $c++) {
            // Center 5x5 reserved for Kafe Nusantara brass crest
            if ($r >= 10 && $r <= 14 && $c >= 10 && $c <= 14) {
                continue;
            }

            // Three 7x7 corner finders
            $f1 = $isFinder($r, $c, 0, 0);
            $f2 = $isFinder($r, $c, 0, $grid - 7);
            $f3 = $isFinder($r, $c, $grid - 7, 0);

            if ($f1 !== null) {
                if ($f1) {
                    $cells[] = [$r, $c];
                }

                continue;
            }
            if ($f2 !== null) {
                if ($f2) {
                    $cells[] = [$r, $c];
                }

                continue;
            }
            if ($f3 !== null) {
                if ($f3) {
                    $cells[] = [$r, $c];
                }

                continue;
            }

            // Alignment 5x5 pattern at bottom-right (16..20, 16..20)
            if ($r >= 16 && $r <= 20 && $c >= 16 && $c <= 20) {
                $onRing = $r === 16 || $r === 20 || $c === 16 || $c === 20 || ($r === 18 && $c === 18);
                if ($onRing) {
                    $cells[] = [$r, $c];
                }

                continue;
            }

            // Timing lines
            if ($r === 6) {
                if ($c % 2 === 0) {
                    $cells[] = [$r, $c];
                }

                continue;
            }
            if ($c === 6) {
                if ($r % 2 === 0) {
                    $cells[] = [$r, $c];
                }

                continue;
            }

            // Deterministic data module from SHA-256 bit stream
            $bytePos = intdiv($bitIndex, 8) % strlen($hashBytes);
            $bitOffset = $bitIndex % 8;
            $bitIndex++;

            if (((ord($hashBytes[$bytePos]) >> $bitOffset) & 1) === 1) {
                $cells[] = [$r, $c];
            }
        }
    }
@endphp

<svg width="{{ $size }}"
     height="{{ $size }}"
     viewBox="0 0 {{ $grid + 2 }} {{ $grid + 2 }}"
     fill="none"
     xmlns="http://www.w3.org/2000/svg"
     {{ $attributes->merge(['class' => 'mx-auto block select-none']) }}
     role="img"
     aria-label="Kode QR {{ $payload }}">
    <rect width="{{ $grid + 2 }}" height="{{ $grid + 2 }}" rx="1.6" fill="#FFFFFF"/>
    @foreach ($cells as [$r, $c])
        <rect x="{{ $c + 1 }}" y="{{ $r + 1 }}" width="0.92" height="0.92" rx="0.2" fill="#1F1A17"/>
    @endforeach

    {{-- Center Heritage Crest --}}
    <rect x="11.15" y="11.15" width="4.7" height="4.7" rx="1.05" fill="#1F1A17" stroke="#C69C6D" stroke-width="0.35"/>
    <text x="13.5" y="14.25" text-anchor="middle" fill="#C69C6D" font-family="Georgia, serif" font-weight="bold" font-size="2.15">KN</text>
</svg>
