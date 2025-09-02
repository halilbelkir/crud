<?php

namespace crudPackage\Http\Middleware;

use crudPackage\Models\MenuItem;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = Route::currentRouteName();

        if (!auth()->user()->hasPermission($routeName))
        {
            $mainMenus = MenuItem::where('menu_id',1)->where('main',1)->where('parent_id',0)->orderBy('order')->get();
            $menus     = MenuItem::where('menu_id',1)->where('main',0)->where('parent_id',0)->orderBy('order')->get();

            View::share([
                'mainMenus' => $mainMenus,
                'menus'     => $menus,
            ]);

            abort(401, 'Maalesef bu işlem için yetkiniz yok!');
        }

        return $next($request);
    }
}
