<?php

declare(strict_types=1);

namespace WebServCo\Controller\Contract;

use WebServCo\View\Contract\ViewServicesContainerInterface;

/**
 * A module-level controller instantiator interface.
 */
interface ModuleControllerInstantiatorInterface
{
    public function instantiateModuleController(
        string $controllerClassName,
        ViewServicesContainerInterface $viewServicesContainer,
    ): ControllerInterface;
}
