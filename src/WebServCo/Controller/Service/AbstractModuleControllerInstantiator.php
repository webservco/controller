<?php

declare(strict_types=1);

namespace WebServCo\Controller\Service;

use LogicException;
use OutOfRangeException;
use WebServCo\Controller\Contract\ControllerInterface;
use WebServCo\Controller\Contract\ModuleControllerInstantiatorInterface;
use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\DependencyContainer\Contract\LocalDependencyContainerInterface;
use WebServCo\View\Contract\ViewServicesContainerInterface;

use function class_exists;

abstract class AbstractModuleControllerInstantiator implements ModuleControllerInstantiatorInterface
{
    public function __construct(
        protected ApplicationDependencyContainerInterface $applicationDependencyContainer,
        protected LocalDependencyContainerInterface $localDependencyContainer,
    ) {
    }

    public function instantiateSpecificModuleController(
        string $controllerClassName,
        ViewServicesContainerInterface $viewServicesContainer,
    ): ControllerInterface {
        if (!class_exists($controllerClassName, true)) {
            throw new OutOfRangeException('Controller class does not exist.');
        }

        /**
         * Psalm error: "Cannot call constructor on an unknown class".
         *
         * @psalm-suppress MixedMethodCall
         */
        $object = new $controllerClassName(
            // Object: \WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface
            $this->applicationDependencyContainer,
            // Object: \WebServCo\DependencyContainer\Contract\LocalDependencyContainerInterface
            $this->localDependencyContainer,
            // Object: \WebServCo\View\Contract\ViewServicesContainerInterface
            $viewServicesContainer,
        );

        if (!$object instanceof ControllerInterface) {
            throw new LogicException('Object is not an instance of the required interface.');
        }

        return $object;
    }
}
