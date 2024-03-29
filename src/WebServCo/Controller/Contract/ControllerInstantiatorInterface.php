<?php

declare(strict_types=1);

namespace WebServCo\Controller\Contract;

use WebServCo\Route\Contract\RouteConfigurationInterface;

/**
 * A top level Controller instantiator interface.
 */
interface ControllerInstantiatorInterface
{
    public function instantiateController(
        RouteConfigurationInterface $routeConfiguration,
        string $viewRendererClass,
    ): ControllerInterface;
}
