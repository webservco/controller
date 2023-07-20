<?php

declare(strict_types=1);

namespace WebServCo\Controller\Contract;

use WebServCo\View\Contract\ViewServicesContainerInterface;

/**
 * A specific, module-level controller instantiator interface.
 */
interface ModuleControllerInstantiatorInterface
{
    public function instantiateSpecificModuleController(
        string $controllerClassName,
        ViewServicesContainerInterface $viewServicesContainer,
    ): ControllerInterface;
}
