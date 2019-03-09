<?php
declare(strict_types=1);

namespace App;

use function Chevereto\Core\dump;
use Chevereto\Core\Json;
use Chevereto\Core\Http\JsonResponse;

return new class extends Controller {
    public function __invoke()
    {
        $json = new Json();
        $json->setResponse('Hello, World!', 100);
        $json
            ->addData('api', ['entry' => 'HTTP GET /api', 'description' => 'Retrieves the exposed API.'])
            ->addData('cli', ['entry' => 'php app/console list', 'description' => 'Lists console commands.']);
        
        return (new JsonResponse())->setContent($json)->setStatusCode(200);
    }
};
