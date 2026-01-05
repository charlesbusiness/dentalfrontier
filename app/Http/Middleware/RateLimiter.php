<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacde;
use Symfony\Component\HttpFoundation\Response;

class RateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limiter = 'api'): Response
    {
        $key = $this->resolveRequestSignature($request, $limiter);

        $limit = $this->getLimit($limiter, $request);
        $maxAttempts = $limit['maxAttempts'];
        $decayMinutes = $limit['decayMinutes'];

        if (RateLimiterFacde::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiterFacde::availableIn($key);
            
            return response()->json([
                'message' => $this->getMessage($limiter),
                'retry_after' => $retryAfter,
                'success' => false
            ], 429, [
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'Retry-After' => $retryAfter,
                'X-RateLimit-Reset' => time() + $retryAfter,
            ]);
        }

        RateLimiterFacde::hit($key, $decayMinutes * 60);

        $response = $next($request);

        if (!$response instanceof Response) {
            return response($response);
        }


        $remaining = RateLimiterFacde::remaining($key, $maxAttempts);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
        ]);
    }

    /**
     * Resolve the request signature.
     */
    protected function resolveRequestSignature(Request $request, string $limiter): string
    {
        // For auth and product routes, use IP-based rate limiting
        if ($limiter === 'auth' || $limiter === 'products') {
            return $limiter . '|' . $request->ip();
        }

        // For wishlist routes, always use IP to avoid triggering user resolution
        // The route is already protected by auth:sanctum middleware
        return $limiter . '|' . $request->ip();
    }

    /**
     * Get the rate limit configuration for the given limiter.
     */
    protected function getLimit(string $limiter, Request $request): array
    {
        return match ($limiter) {
            'auth' => ['maxAttempts' => 5, 'decayMinutes' => 1],
            'products' => ['maxAttempts' => 20, 'decayMinutes' => 1],
            'wishlist' => ['maxAttempts' => 30, 'decayMinutes' => 1],
            'api' => ['maxAttempts' => 30, 'decayMinutes' => 1],
            default => ['maxAttempts' => 25, 'decayMinutes' => 1],
        };
    }

    /**
     * Get the custom message for the rate limiter.
     */
    protected function getMessage(string $limiter): string
    {
        return match ($limiter) {
            'auth' => 'Too many authentication attempts. Please try again later.',
            'wishlist' => 'Too many wishlist operations. Please slow down.',
            'products' => 'Too many requests. Please try again later.',
            default => 'Too many requests. Please try again later.',
        };
    }
}
