<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isSecure = $request->isSecure() || $request->header('X-Forwarded-Proto') === 'https';

        // Enforce HTTPS redirect on cleartext HTTP in production or when FORCE_HTTPS is enabled
        if (!$isSecure && (app()->environment('production') || env('FORCE_HTTPS', false))) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($isSecure || app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Content Security Policy dinamis dari konfigurasi env dengan sanitasi origin ketat
        $rawSources = [
            config('app.url'),
            config('services.campus_api.base_url'),
            config('services.campus_api.bridge_url'),
            config('services.campus_api.sahaja_url'),
        ];

        $connectSources = ["'self'"];
        foreach ($rawSources as $source) {
            if ($origin = $this->sanitizeOrigin($source)) {
                $connectSources[] = $origin;
            }
        }
        $connectSrc = implode(' ', array_unique($connectSources));

        $csp = "default-src 'self'; "
            ."script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
            ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            ."font-src 'self' https://fonts.gstatic.com data:; "
            ."img-src 'self' data: blob: https://portal.uinssc.ac.id; "
            ."connect-src {$connectSrc}; "
            ."frame-ancestors 'none'; "
            ."form-action 'self' http://127.0.0.1:* http://localhost:*; "
            ."base-uri 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    /**
     * Ekstrak dan sanitasi origin (scheme://host[:port]) yang aman untuk CSP connect-src.
     * Mencegah injeksi semicolon (;), CRLF (\r\n), dan HTTP response splitting.
     */
    protected function sanitizeOrigin(?string $url): ?string
    {
        if (!$url || !is_string($url)) {
            return null;
        }

        $clean = trim($url);
        if ($clean === '') {
            return null;
        }

        // Tolak mutlak jika mengandung CRLF atau null-byte (anti response splitting)
        if (preg_match('/[\r\n\0]/', $clean)) {
            return null;
        }

        // Jika mengandung semicolon (;), ambil bagian sebelum semicolon pertama
        // untuk mengisolasi potensi injeksi direktif CSP
        if (str_contains($clean, ';')) {
            $clean = trim(explode(';', $clean, 2)[0]);
        }

        $parts = parse_url($clean);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower($parts['host']);
        if (!preg_match('/^[a-z0-9\.\-\[\]:]+$/', $host) || str_contains($host, '..')) {
            return null;
        }

        $origin = "{$scheme}://{$host}";

        if (isset($parts['port'])) {
            $port = (int) $parts['port'];
            if ($port > 0 && $port <= 65535) {
                $isStandard = ($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443);
                if (!$isStandard) {
                    $origin .= ":{$port}";
                }
            } else {
                return null;
            }
        }

        return $origin;
    }
}
