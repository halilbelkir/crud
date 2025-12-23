<?php

use crudPackage\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
function menuGenerate($menu)
{
    if (strstr($menu->route,','))
    {
        $arr         = explode(',',$menu->route);
        $menu->route = $arr[0];
        $parameter   = $arr[1];
        $route       = route($menu->route,$parameter);
    }
    else
    {
        $route = $menu->dynamic_route == 1 ? route($menu->route) : $menu->route;
    }

    $activeRoute      = explode('.',$menu->route);
    $activeRoute      = $activeRoute[0];
    $activeRoute     .= '*';
    $target           = $menu->target == 1 ? 'target="_blank"' : null;
    $children         = $menu->children;
    $childrenControl  = count($children) > 0 ? 1 : 0;
    $html             = '';
    $htmlChildren     = '';

    if ($childrenControl)
    {
        $childrenRoutes = [];

        foreach ($children as $child)
        {
            if (strstr($child->route,','))
            {
                $arr         = explode(',',$child->route);
                $child->route = $arr[0];
                $parameter   = $arr[1];
                $routeChild  = route($child->route,$parameter);
            }
            else
            {
                $routeChild = $child->dynamic_route == 1 ? route($child->route) : $child->route;
            }

            $activeRouteChild  = explode('.',$child->route);
            $activeRouteChild  = $activeRouteChild[0];
            $activeRouteChild .= '*';
            $htmlChildren     .= '<div class="menu-item">';
            $htmlChildren     .= '<a href="'.$routeChild.'" '. $target .' class="menu-link ' . (request()->routeIs($activeRouteChild) ? 'active' : null) . '">';
            $htmlChildren     .= '<span class="menu-icon">'. ($child->icon ?? '<i class="ki-outline ki-bookmark "></i>') .'</span>';
            $htmlChildren     .= '<span class="menu-title">'. $child->title .'</span>';
            $htmlChildren     .= '</a>';
            $htmlChildren     .= '</div>';

            array_push($childrenRoutes, $activeRouteChild);
        }

        $html  = '<div data-kt-menu-trigger="click" class="menu-item menu-accordion '. (request()->routeIs($childrenRoutes) ? 'hover show' : null) .'">';
        $html .= '<span class="menu-link ' . (request()->routeIs($childrenRoutes) ? 'active' : null) . '">';
    }
    else
    {
        $html  = '<div data-kt-menu-trigger="click" class="menu-item menu-accordion">';
        $html .= '<a href="'.$route.'" '. $target .' class="menu-link ' . (request()->routeIs($activeRoute) ? 'active' : null) . '">';
    }

    $html .= '<span class="menu-icon">'. ($menu->icon ?? '<i class="ki-outline ki-bookmark "></i>') .'</span>';
    $html .= '<span class="menu-title">'. $menu->title.'</span>';

    if ($childrenControl)
    {
        $html .= '<span class="menu-arrow"></span>';
        $html .= '</span>';
        $html .= '<div class="menu-sub menu-sub-accordion">';
        $html .= $htmlChildren;
        $html .= '</div>';
    }
    else
    {
        $html .= '</a>';
    }

    $html .= '</div>';

    return $html;
}

function settings($column)
{
    $cacheName = 'settings';
    $cache     = Cache::remember($cacheName, Carbon::now()->addMinutes(480), function () use ($cacheName,$column)
    {
        $settings = Setting::find(1);

        if($settings)
        {
            return $settings;
        }
        else
        {
            return null;
        }
    });

    $columns =
        [
            'logo'     => 'crud/images/logo.svg',
            'loader'   => 'crud/images/loading.gif',
            'bg_image' => 'crud/images/guest.jpg',
            'icon'     => 'crud/images/fav/android-icon-192x192.png',
            'color_1'  => '#2900FF',
            'color_2'  => '#001244',
            'title'    => 'Zaurac Teknoloji',
        ];

    return $cache->$column ?? $columns[$column];
}

function diffFields(array $old, array $new): array
{
    $diff = [];

    foreach ($new as $key => $value)
    {
        if (!array_key_exists($key, $old) || $key == 'created_at' || $key == 'updated_at')
        {
            continue;
        }

        if ($old[$key] != $value)
        {
            $diff[$key] =
                [
                    'old' => $old[$key],
                    'new' => $value,
                ];
        }
    }

    return $diff;
}
