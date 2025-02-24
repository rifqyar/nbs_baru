<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

if (!function_exists('getmenu')) {
    $blade = '';
    // function to render parent menu
    function getmenu()
    {
        $id_group = Session::get('id_group');
        $cacheKey = 'menu_' . $id_group;

        if (Cache::has($cacheKey)) {
            $menu = Cache::get($cacheKey);
        } else {
            $menu = Cache::rememberForever($cacheKey, function () {
                $blade = '';
                $menu = DB::connection('default')->select("SELECT * FROM TB_MENU WHERE OTORISASI LIKE '%" . Session::get('id_group') . "%' ORDER BY PARENT_ID, MENU_ORDER");
                for ($i = 0; $i < count($menu); $i++) {
                    if ($menu[$i]->menu == 'Home') {
                        $link = route('home');
                    } else {
                        $link = $menu[$i]->linknya == null ? 'javascript:void(0)' : (Route::has($menu[$i]->linknya) ? route($menu[$i]->linknya) : 'javascript:void(0)');
                    }

                    if ($menu[$i]->parent_id == 0) {
                        $blade .= "<li><a class='has-arrow' href='" . $link . "' aria-expanded='false'>" . $menu[$i]->menu . "</a>" . getMenuChild($menu, $menu[$i]->id_menu) . "</li>";
                    }
                }
                return $blade;
            });
        }

        return $menu;
    }
}

if (!function_exists('getHistoryNotification')) {
    $blade = '';
    // function to render parent menu
    function getHistoryNotification()
    {
        // Cache the history for 5 minutes (300 seconds)
        $history = Cache::remember('history_container', 300, function () {
            return DB::connection('uster')->select("
                SELECT hc.NO_CONTAINER, hc.KEGIATAN, hc.TGL_UPDATE, hc.ID_USER, mu.NAMA_LENGKAP
                FROM HISTORY_CONTAINER hc
                LEFT JOIN USTER.MASTER_USER mu ON hc.ID_USER = TO_CHAR(mu.ID)
                WHERE hc.TGL_UPDATE IS NOT NULL
                ORDER BY hc.TGL_UPDATE DESC
                FETCH FIRST 20 ROWS ONLY
            ");
        });

        return $history;
    }
}


// function to render child menu (invinite child)
function getMenuChild($menu, $parent_id)
{
    $childData = DB::connection('default')
        ->select("SELECT * FROM TB_MENU WHERE PARENT_ID = $parent_id");

    if (count($childData) > 0) {
        $child = '<ul aria-expanded="false" class="collapse">';
        for ($i = 0; $i < count($menu); $i++) {
            if ($menu[$i]->parent_id != 0) {
                if ($menu[$i]->parent_id == $parent_id) {
                    $link = $menu[$i]->linknya == null ?
                        'javascript:void(0)' : (Route::has($menu[$i]->linknya) ?
                            route($menu[$i]->linknya) : (isset($menu[$i]->route) && Route::has($menu[$i]->route) ?
                                route($menu[$i]->route) : 'javascript:void(0)')
                        );

                    $child .= "<li><a aria-expanded='false' class='text-wrap' href='" . $link . "'>" . $menu[$i]->menu . "</a>" . getMenuChild($menu, $menu[$i]->id_menu) . "</li>";
                }
            }
        }
        $child .= '</ul>';
    }
    if (count($childData) > 0) {
        $child = '<ul aria-expanded="false" class="collapse">';
        for ($i = 0; $i < count($menu); $i++) {
            if ($menu[$i]->parent_id != 0) {
                if ($menu[$i]->parent_id == $parent_id) {
                    $link = $menu[$i]->linknya == null ?
                        'javascript:void(0)' : (Route::has($menu[$i]->linknya) ?
                            route($menu[$i]->linknya) : (isset($menu[$i]->route) && Route::has($menu[$i]->route) ?
                                route($menu[$i]->route) : 'javascript:void(0)')
                        );

                    $child .= "<li><a aria-expanded='false' class='text-wrap' href='" . $link . "'>" . $menu[$i]->menu . "</a>" . getMenuChild($menu, $menu[$i]->id_menu) . "</li>";
                }
            }
        }
        $child .= '</ul>';

        return $child;
    }
}
