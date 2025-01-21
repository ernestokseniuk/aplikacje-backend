<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('register', 'AuthController::register');
$routes->post('login', 'AuthController::login');
$routes->post('refresh-token', 'AuthController::refreshToken');
$routes->options('refresh-token', 'ProjectController::optionsUpdate');
$routes->get('avatar/(:any)', 'AvatarController::show/$1');

$routes->group('project', ['filter' => 'auth'], function($routes) {
    $routes->options('', 'ProjectController::optionsUpdate');
    $routes->options('create', 'ProjectController::optionsCreate');
    $routes->post('create', 'ProjectController::create');
    $routes->options('update', 'ProjectController::optionsUpdate');
    $routes->post('update', 'ProjectController::update');
    $routes->post('show/(:num)', 'ProjectController::show/$1');
    $routes->options('show/(:num)', 'ProjectController::optionsShow/$1');
    $routes->options('show-all/', 'ProjectController::optionsUpdate');
    $routes->post('show-all/', 'ProjectController::showUserProjectsList');
    $routes->options('delete/(:num)', 'ProjectController::optionsUpdate');
    $routes->post('delete/(:num)', 'ProjectController::delete/$1');
    $routes->post('share/(:num)', 'ProjectController::setShareProject/$1');
    $routes->options('share/(:num)', 'ProjectController::optionsUpdate');
    $routes->post('copy/(:num)', 'ProjectController::copyProject/$1');
    $routes->options('copy/(:num)', 'ProjectController::optionsUpdate');
});

$routes -> group('users',['filter'=>'auth'],function($routes){
   $routes -> options('(:any)','ProjectController::optionsUpdate');
   $routes -> post('(:any)','AuthController::getUser/$1');
   $routes -> put('description','AuthController::updateDescription');
   $routes -> options('description','ProjectController::optionsUpdate');
    $routes -> put('avatar','AuthController::updateAvatar');
    $routes -> options('avatar','ProjectController::optionsUpdate');
    $routes -> put('password','AuthController::updatePassword');
    $routes -> options('password','ProjectController::optionsUpdate');
});





$routes->group('projects', function($routes) {
    $routes->options('public', 'ProjectController::optionsUpdate');
    $routes->post('public', 'ProjectController::getPublicProjects');
    $routes->get('public', 'ProjectController::getPublicProjects');
    $routes->options('public(:any)', 'ProjectController::optionsUpdate');
    $routes->post('public/(:any)', 'ProjectController::getPublicProjects/$1');
    $routes->get('public/(:any)', 'ProjectController::getPublicProjects/$1');

    $routes->options('public/(:any)/(:num)', 'ProjectController::optionsUpdate');
    $routes->post('public/(:any)/(:num)', 'ProjectController::getPublicProjects/$1/$2');
    $routes->get('public/(:any)/(:num)', 'ProjectController::getPublicProjects/$1/$2');
});
