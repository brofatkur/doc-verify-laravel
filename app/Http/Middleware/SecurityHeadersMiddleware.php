<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Add HTTP security headers to protect against Clickjacking, MIME-sniffing, and XSS.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (method_exists($response, 'headers')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
            $response->headers->set('X-Content-Type-Options', 'nosniff', false);
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
            $response->headers->set('X-XSS-Protection', '1; mode=block', false);
            $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=()', false);
        }

        return $response;
    }
}
