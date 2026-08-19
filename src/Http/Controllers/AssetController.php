<?php

namespace SalvatoreCervone\FilterByModel\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class AssetController extends Controller
{
    /**
     * Serve gli asset statici (CSS, JS) direttamente dal package come fallback.
     */
    public function __invoke(string $path): Response
    {
        $sanitizedPath = str_replace(['..', "\0"], '', $path);
        $fullPath = realpath(__DIR__ . '/../../../public/' . $sanitizedPath);
        $basePublic = realpath(__DIR__ . '/../../../public');

        if (!$fullPath || !$basePublic || !str_starts_with($fullPath, $basePublic) || !file_exists($fullPath)) {
            abort(404);
        }

        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        $contentType = match ($extension) {
            'css' => 'text/css; charset=utf-8',
            'js'  => 'application/javascript; charset=utf-8',
            'svg' => 'image/svg+xml',
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'ttf' => 'font/ttf',
            default => 'text/plain',
        };

        return response(file_get_contents($fullPath), 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
