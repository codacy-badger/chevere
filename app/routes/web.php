<?php

namespace App;

use Chevere\Route\Route;
use Chevere\Http\Method;

return [
  'index' => (new Route('/', Controllers\Index::class))
    ->setName('homepage'),
    // ->addMiddleware(Middlewares\RoleAdmin::class)
    // ->addMiddleware(Middlewares\RoleBanned::class),
  (new Route('/cache/{llave?}-{cert}-{user?}'))
    ->setWhere('llave', '[0-9]+')
    ->setMethod(new Method('GET', Controllers\Cache::class))
    ->setMethod(new Method('POST', Controllers\Cache::class))
    ->setName('cache'),
];
