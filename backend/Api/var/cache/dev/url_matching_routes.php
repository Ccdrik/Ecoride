<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/logout' => [[['_route' => '_logout_main'], null, null, null, false, false, null]],
        '/_wdt/styles' => [[['_route' => '_wdt_stylesheet', '_controller' => 'web_profiler.controller.profiler::toolbarStylesheetAction'], null, null, null, false, false, null]],
        '/_profiler' => [[['_route' => '_profiler_home', '_controller' => 'web_profiler.controller.profiler::homeAction'], null, null, null, true, false, null]],
        '/_profiler/search' => [[['_route' => '_profiler_search', '_controller' => 'web_profiler.controller.profiler::searchAction'], null, null, null, false, false, null]],
        '/_profiler/search_bar' => [[['_route' => '_profiler_search_bar', '_controller' => 'web_profiler.controller.profiler::searchBarAction'], null, null, null, false, false, null]],
        '/_profiler/phpinfo' => [[['_route' => '_profiler_phpinfo', '_controller' => 'web_profiler.controller.profiler::phpinfoAction'], null, null, null, false, false, null]],
        '/_profiler/xdebug' => [[['_route' => '_profiler_xdebug', '_controller' => 'web_profiler.controller.profiler::xdebugAction'], null, null, null, false, false, null]],
        '/_profiler/open' => [[['_route' => '_profiler_open_file', '_controller' => 'web_profiler.controller.profiler::openAction'], null, null, null, false, false, null]],
        '/api/admin/users' => [[['_route' => 'admin_users_list', '_controller' => 'App\\Controller\\AdminController::listUsers'], null, ['GET' => 0], null, false, false, null]],
        '/api/trajets' => [
            [['_route' => 'api_trajets_list', '_controller' => 'App\\Controller\\ApiController::listTrajets'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'api_trajets_create', '_controller' => 'App\\Controller\\ApiController::createTrajet'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/mes-trajets' => [[['_route' => 'api_mes_trajets', '_controller' => 'App\\Controller\\ApiController::getMesTrajets'], null, ['GET' => 0], null, false, false, null]],
        '/api/signup' => [[['_route' => 'api_signup', '_controller' => 'App\\Controller\\AuthController::signup'], null, ['POST' => 0], null, false, false, null]],
        '/api/me' => [[['_route' => 'api_me', '_controller' => 'App\\Controller\\AuthController::getMe'], null, ['GET' => 0], null, false, false, null]],
        '/api/credits' => [[['_route' => 'api_credits_add', '_controller' => 'App\\Controller\\AuthController::ajouterCredits'], null, ['POST' => 0], null, false, false, null]],
        '/' => [[['_route' => 'home', '_controller' => 'App\\Controller\\MainController::index'], null, null, null, false, false, null]],
        '/api/preferences' => [[['_route' => 'api_preferences_save', '_controller' => 'App\\Controller\\PreferenceController::savePreferences'], null, ['POST' => 0], null, false, false, null]],
        '/api/reservations' => [[['_route' => 'api_reservation_create', '_controller' => 'App\\Controller\\ReservationController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/mes-reservations' => [[['_route' => 'api_reservations_user', '_controller' => 'App\\Controller\\ReservationController::mesReservations'], null, ['GET' => 0], null, false, false, null]],
        '/api/signin' => [[['_route' => 'api_signin', '_controller' => 'App\\Controller\\SecurityController::signin'], null, ['POST' => 0], null, false, false, null]],
        '/api/users' => [[['_route' => 'api_users_list', '_controller' => 'App\\Controller\\UserController::listUsers'], null, ['GET' => 0], null, false, false, null]],
        '/api/check-email' => [[['_route' => 'api_check_email', '_controller' => 'App\\Controller\\UserController::checkEmail'], null, ['GET' => 0], null, false, false, null]],
        '/api/check-pseudo' => [[['_route' => 'api_check_pseudo', '_controller' => 'App\\Controller\\UserController::checkPseudo'], null, ['GET' => 0], null, false, false, null]],
        '/api/voiture' => [[['_route' => 'api_voiture_create', '_controller' => 'App\\Controller\\VoitureController::createVoiture'], null, ['POST' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_(?'
                    .'|error/(\\d+)(?:\\.([^/]++))?(*:38)'
                    .'|wdt/([^/]++)(*:57)'
                    .'|profiler/(?'
                        .'|font/([^/\\.]++)\\.woff2(*:98)'
                        .'|([^/]++)(?'
                            .'|/(?'
                                .'|search/results(*:134)'
                                .'|router(*:148)'
                                .'|exception(?'
                                    .'|(*:168)'
                                    .'|\\.css(*:181)'
                                .')'
                            .')'
                            .'|(*:191)'
                        .')'
                    .')'
                .')'
                .'|/api/(?'
                    .'|admin/users/([^/]++)/toggle(*:237)'
                    .'|trajets/([^/]++)(?'
                        .'|(*:264)'
                        .'|/(?'
                            .'|start(*:281)'
                            .'|end(*:292)'
                            .'|finish(*:306)'
                        .')'
                    .')'
                    .'|users/([^/]++)/suspend(*:338)'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        38 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        57 => [[['_route' => '_wdt', '_controller' => 'web_profiler.controller.profiler::toolbarAction'], ['token'], null, null, false, true, null]],
        98 => [[['_route' => '_profiler_font', '_controller' => 'web_profiler.controller.profiler::fontAction'], ['fontName'], null, null, false, false, null]],
        134 => [[['_route' => '_profiler_search_results', '_controller' => 'web_profiler.controller.profiler::searchResultsAction'], ['token'], null, null, false, false, null]],
        148 => [[['_route' => '_profiler_router', '_controller' => 'web_profiler.controller.router::panelAction'], ['token'], null, null, false, false, null]],
        168 => [[['_route' => '_profiler_exception', '_controller' => 'web_profiler.controller.exception_panel::body'], ['token'], null, null, false, false, null]],
        181 => [[['_route' => '_profiler_exception_css', '_controller' => 'web_profiler.controller.exception_panel::stylesheet'], ['token'], null, null, false, false, null]],
        191 => [[['_route' => '_profiler', '_controller' => 'web_profiler.controller.profiler::panelAction'], ['token'], null, null, false, true, null]],
        237 => [[['_route' => 'admin_user_toggle', '_controller' => 'App\\Controller\\AdminController::toggleUser'], ['id'], ['PUT' => 0], null, false, false, null]],
        264 => [[['_route' => 'api_trajets_delete', '_controller' => 'App\\Controller\\ApiController::deleteTrajet'], ['id'], ['DELETE' => 0], null, false, true, null]],
        281 => [[['_route' => 'api_trajet_start', '_controller' => 'App\\Controller\\ApiController::startTrajet'], ['id'], ['PATCH' => 0], null, false, false, null]],
        292 => [[['_route' => 'api_trajet_end', '_controller' => 'App\\Controller\\ApiController::endTrajet'], ['id'], ['PATCH' => 0], null, false, false, null]],
        306 => [[['_route' => 'api_trajet_finish', '_controller' => 'App\\Controller\\ApiController::finishTrajet'], ['id'], ['PATCH' => 0], null, false, false, null]],
        338 => [
            [['_route' => 'api_user_suspend', '_controller' => 'App\\Controller\\UserController::suspendUser'], ['id'], ['PUT' => 0], null, false, false, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
