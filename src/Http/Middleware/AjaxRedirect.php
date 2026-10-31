<?php

namespace crudPackage\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AjaxRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->ajax() || ! $response instanceof RedirectResponse)
        {
            return $response;
        }

        $session = $request->session();
        $fresh   = $session->get('_flash.new', []);
        $read    = fn (string $key) => in_array($key, $fresh, true) ? $session->get($key) : null;

        $payload = [
            'ok'     => $read('error') === null,
            'status' => $read('status'),
            'detail' => $read('detail'),
            'error'  => $read('error'),
            'reload' => (bool) $read('reload'),
            'url'    => $response->getTargetUrl(),
        ];

        if ($payload['reload'])
        {
            $session->forget('reload');
            $session->put('_flash.new', array_values(array_diff($fresh, ['reload'])));
        }
        else
        {
            $session->forget(array_merge($fresh, ['status', 'detail', 'error']));
            $session->put('_flash.new', []);
        }

        return response()->json($payload);
    }
}
