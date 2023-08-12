<?php

declare(strict_types=1);

namespace WebServCo\Controller\Factory;

use WebServCo\Controller\Contract\ControllerInstantiatorInterface;
use WebServCo\Controller\Contract\SpecificModuleControllerInstantiatorInterface;
use WebServCo\Controller\Service\ControllerInstantiator;
use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\Reflection\Factory\ReflectionClassFactory;
use WebServCo\Reflection\Service\ReflectionService;
use WebServCo\View\Service\ViewRendererInstantiator;

final class ControllerInstantiatorFactory
{
    public function createControllerInstantiator(
        ApplicationDependencyContainerInterface $applicationDependencyContainer,
        SpecificModuleControllerInstantiatorInterface $specificModuleControllerInstantiator,
    ): ControllerInstantiatorInterface {
        return new ControllerInstantiator(
            $applicationDependencyContainer,
            new ReflectionService(new ReflectionClassFactory()),
            $specificModuleControllerInstantiator,
            new ViewRendererInstantiator(),
        );
    }
}
