<?php

return '<?php

/**
 * Quantum PHP Framework
 *
 * An open source software development framework for PHP
 *
 * @package Quantum
 * @author Arman Ag. <arman.ag@softberg.org>
 * @copyright Copyright (c) 2018 Softberg LLC (https://softberg.org)
 * @link http://quantum.softberg.org/
 * @since 2.9.0
 */

namespace Modules\\' . Quantum\Libraries\Module\ModuleManager::$moduleName . '\Controllers\OpenApi;

use Quantum\Mvc\QtController;

/**
 * Class ApiController
 * @package Modules\Api
 * @OA\Info(
 *    title="Quantum API documentation",
 *    version="2.9.0",
 *    description=" *Quantum Documentation: https://quantum.softberg.org/en/docs/v1/overview"
 *  ),
 * @OA\SecurityScheme(
 *    securityScheme="bearer_token",
 *    type="apiKey",
 *    name="Authorization",
 *    in="header"
 *  )
 */
abstract class OpenApiController
{

}';