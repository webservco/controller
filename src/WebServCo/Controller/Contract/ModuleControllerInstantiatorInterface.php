<?php

declare(strict_types=1);

namespace WebServCo\Controller\Contract;

use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\Reflection\Contract\ReflectionServiceInterface;
use WebServCo\View\Contract\ViewServicesContainerInterface;

/**
 * A module-level controller instantiator interface.
 */
interface ModuleControllerInstantiatorInterface
{
    public function instantiateModuleController(
        ApplicationDependencyContainerInterface $applicationDependencyContainer,
        string $controllerClassName,
        ReflectionServiceInterface $reflectionService,
        ViewServicesContainerInterface $viewServicesContainer,
    ): ControllerInterface;
}
