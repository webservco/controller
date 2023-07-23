<?php

declare(strict_types=1);

namespace WebServCo\Controller\Contract;

use WebServCo\DependencyContainer\Contract\LocalDependencyContainerInterface;
use WebServCo\Route\Contract\RouteConfigurationInterface;

/**
 * A top level Controller instantiator interface.
 */
interface ControllerInstantiatorInterface
{
    public function instantiateController(
        LocalDependencyContainerInterface $localDependencyContainer,
        RouteConfigurationInterface $routeConfiguration,
        string $viewRendererClass,
    ): ControllerInterface;
}
