<?php

declare(strict_types=1);

namespace WebServCo\Controller\Contract;

use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\DependencyContainer\Contract\LocalDependencyContainerInterface;
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
        LocalDependencyContainerInterface $localDependencyContainer,
        ReflectionServiceInterface $reflectionService,
        ViewServicesContainerInterface $viewServicesContainer,
    ): ControllerInterface;
}
