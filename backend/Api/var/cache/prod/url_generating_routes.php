<?php

// This file has been auto-generated by the Symfony Routing Component.

return [
    'api_trajets_list' => [[], ['_controller' => 'App\\Controller\\ApiController::listTrajets'], [], [['text', '/api/trajets']], [], [], []],
    'api_trajets_create' => [[], ['_controller' => 'App\\Controller\\ApiController::createTrajet'], [], [['text', '/api/trajets']], [], [], []],
    'api_reservations_create' => [[], ['_controller' => 'App\\Controller\\ApiController::createReservation'], [], [['text', '/api/reservations']], [], [], []],
    'api_trajets_show' => [['id'], ['_controller' => 'App\\Controller\\ApiController::showTrajet'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/api/trajets']], [], [], []],
    'api_user_reservations' => [[], ['_controller' => 'App\\Controller\\ApiController::getUserReservations'], [], [['text', '/api/mes-reservations']], [], [], []],
    'api_trajets_search' => [[], ['_controller' => 'App\\Controller\\ApiController::searchTrajets'], [], [['text', '/api/trajets/search']], [], [], []],
    'api_trajets_update' => [['id'], ['_controller' => 'App\\Controller\\ApiController::updateTrajet'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/api/trajets']], [], [], []],
    'api_trajets_delete' => [['id'], ['_controller' => 'App\\Controller\\ApiController::deleteTrajet'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/api/trajets']], [], [], []],
    'api_reservations_delete' => [['id'], ['_controller' => 'App\\Controller\\ApiController::deleteReservation'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/api/reservations']], [], [], []],
    'api_signup' => [[], ['_controller' => 'App\\Controller\\AuthController::signup'], [], [['text', '/api/signup']], [], [], []],
    'api_me' => [[], ['_controller' => 'App\\Controller\\AuthController::me'], [], [['text', '/api/me']], [], [], []],
    'home' => [[], ['_controller' => 'App\\Controller\\MainController::index'], [], [['text', '/']], [], [], []],
    'reservation_index' => [[], ['_controller' => 'App\\Controller\\ReservationController::index'], [], [['text', '/reservations/']], [], [], []],
    'reservation_create' => [[], ['_controller' => 'App\\Controller\\ReservationController::create'], [], [['text', '/reservations/create']], [], [], []],
    'api_signin' => [[], ['_controller' => 'App\\Controller\\SecurityController::signin'], [], [['text', '/api/signin']], [], [], []],
    'api_users_list' => [[], ['_controller' => 'App\\Controller\\UserController::listUsers'], [], [['text', '/api/users']], [], [], []],
    'api_check_email' => [[], ['_controller' => 'App\\Controller\\UserController::checkEmail'], [], [['text', '/api/check-email']], [], [], []],
    'api_check_pseudo' => [[], ['_controller' => 'App\\Controller\\UserController::checkPseudo'], [], [['text', '/api/check-pseudo']], [], [], []],
    'App\Controller\ApiController::listTrajets' => [[], ['_controller' => 'App\\Controller\\ApiController::listTrajets'], [], [['text', '/api/trajets']], [], [], []],
    'App\Controller\ApiController::createTrajet' => [[], ['_controller' => 'App\\Controller\\ApiController::createTrajet'], [], [['text', '/api/trajets']], [], [], []],
    'App\Controller\ApiController::createReservation' => [[], ['_controller' => 'App\\Controller\\ApiController::createReservation'], [], [['text', '/api/reservations']], [], [], []],
    'App\Controller\ApiController::showTrajet' => [['id'], ['_controller' => 'App\\Controller\\ApiController::showTrajet'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/api/trajets']], [], [], []],
    'App\Controller\ApiController::getUserReservations' => [[], ['_controller' => 'App\\Controller\\ApiController::getUserReservations'], [], [['text', '/api/mes-reservations']], [], [], []],
    'App\Controller\ApiController::searchTrajets' => [[], ['_controller' => 'App\\Controller\\ApiController::searchTrajets'], [], [['text', '/api/trajets/search']], [], [], []],
    'App\Controller\ApiController::updateTrajet' => [['id'], ['_controller' => 'App\\Controller\\ApiController::updateTrajet'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/api/trajets']], [], [], []],
    'App\Controller\ApiController::deleteTrajet' => [['id'], ['_controller' => 'App\\Controller\\ApiController::deleteTrajet'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/api/trajets']], [], [], []],
    'App\Controller\ApiController::deleteReservation' => [['id'], ['_controller' => 'App\\Controller\\ApiController::deleteReservation'], [], [['variable', '/', '[^/]++', 'id', true], ['text', '/api/reservations']], [], [], []],
    'App\Controller\AuthController::signup' => [[], ['_controller' => 'App\\Controller\\AuthController::signup'], [], [['text', '/api/signup']], [], [], []],
    'App\Controller\AuthController::checkEmail' => [[], ['_controller' => 'App\\Controller\\UserController::checkEmail'], [], [['text', '/api/check-email']], [], [], []],
    'App\Controller\AuthController::me' => [[], ['_controller' => 'App\\Controller\\AuthController::me'], [], [['text', '/api/me']], [], [], []],
    'App\Controller\MainController::index' => [[], ['_controller' => 'App\\Controller\\MainController::index'], [], [['text', '/']], [], [], []],
    'App\Controller\ReservationController::index' => [[], ['_controller' => 'App\\Controller\\ReservationController::index'], [], [['text', '/reservations/']], [], [], []],
    'App\Controller\ReservationController::create' => [[], ['_controller' => 'App\\Controller\\ReservationController::create'], [], [['text', '/reservations/create']], [], [], []],
    'App\Controller\SecurityController::signin' => [[], ['_controller' => 'App\\Controller\\SecurityController::signin'], [], [['text', '/api/signin']], [], [], []],
    'App\Controller\UserController::listUsers' => [[], ['_controller' => 'App\\Controller\\UserController::listUsers'], [], [['text', '/api/users']], [], [], []],
    'App\Controller\UserController::checkEmail' => [[], ['_controller' => 'App\\Controller\\UserController::checkEmail'], [], [['text', '/api/check-email']], [], [], []],
    'App\Controller\UserController::checkPseudo' => [[], ['_controller' => 'App\\Controller\\UserController::checkPseudo'], [], [['text', '/api/check-pseudo']], [], [], []],
];
