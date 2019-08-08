<?php

namespace App;

use Chevere\Route\Route;
use Chevere\HttpFoundation\Method;

return [
  'index' => (new Route('/', Controllers\Index::class))
    ->setName('homepage')
    ->addMiddleware('middleware:RoleBanned')
    ->addMiddleware('middleware:RoleAdmin'),
  (new Route('/cache/{key?}-{cert}-{user?}'))
    ->setWhere('key', '[0-9]+')
    ->setMethod(new Method('GET', Controllers\Cache::class))
    ->setMethod(new Method('POST', Controllers\Cache::class))
    ->setName('cache'),
];
