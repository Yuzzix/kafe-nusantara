<?php

use App\Http\Middleware\EnsureShiftRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

if (! function_exists('mb_strimwidth')) {
    function mb_strimwidth(string $string, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string
    {
        $sliced = mb_substr($string, $start, null, $encoding);
        if (mb_strwidth($sliced, $encoding) <= $width) {
            return $sliced;
        }

        $targetWidth = max(0, $width - mb_strwidth($trim_marker, $encoding));
        $result = '';
        $len = mb_strlen($sliced, $encoding);

        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($sliced, $i, 1, $encoding);
            if (mb_strwidth($result.$char, $encoding) > $targetWidth) {
                break;
            }
            $result .= $char;
        }

        return $result.$trim_marker;
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'shift.role' => EnsureShiftRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
