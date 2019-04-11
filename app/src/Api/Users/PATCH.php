<?php

declare(strict_types=1);

namespace App\Api\Users;

use App\User;
use Chevereto\Core\Controller;

class PATCH extends Controller
{
    const DESCRIPTION = 'Updates an user.';
    const RESOURCES = [
        'user' => User::class,
    ];
    const PARAMETERS = [
        'email' => [
            'description' => 'User email.',
        ],
    ];

    public function __construct(User $user)
    {
    }

    public function __invoke()
    {
    }
}
