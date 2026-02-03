<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceConnectionMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't redirect for command line, webhooks, or ajax
        if (app()->runningInConsole() || $request->ajax() || $request->is('payment/webhook*')) {
            return $next($request);
        }

        try {
            $setting = \App\Models\SiteSetting::first();
            if ($setting) {
                if ($setting->connection_mode === 'https' && !$request->secure()) {
                    return redirect()->secure($request->getRequestUri());
                } elseif ($setting->connection_mode === 'http' && $request->secure()) {
                    return redirect()->to('http://' . $request->getHttpHost() . $request->getRequestUri());
                }
            }
        } catch (\Exception $e) {
            // Log or ignore if table doesn't exist yet
        }

        return $next($request);
    }
}
