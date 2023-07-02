<?php

declare(strict_types=1);

namespace WebServCo\Controller\Factory;

use WebServCo\Controller\Contract\ControllerInstantiatorInterface;
use WebServCo\Controller\Contract\SpecificModuleControllerInstantiatorInterface;
use WebServCo\Controller\Service\ControllerInstantiator;
use WebServCo\DependencyContainer\Contract\FactoryContainerInterface;
use WebServCo\DependencyContainer\Contract\ServiceContainerInterface;
use WebServCo\View\Service\ViewContainerFactoryInstantiator;
use WebServCo\View\Service\ViewRendererInstantiator;

final class ControllerInstantiatorFactory
{
    public function createControllerInstantiator(
        FactoryContainerInterface $factoryContainer,
        ServiceContainerInterface $serviceContainer,
        SpecificModuleControllerInstantiatorInterface $specificModuleControllerInstantiator,
    ): ControllerInstantiatorInterface {
        return new ControllerInstantiator(
            $factoryContainer,
            $serviceContainer,
            $specificModuleControllerInstantiator,
            new ViewContainerFactoryInstantiator(),
            new ViewRendererInstantiator(),
        );
    }
}
