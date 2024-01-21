<?php

declare(strict_types=1);

namespace WebServCo\Controller\Service;

use OutOfRangeException;
use UnexpectedValueException;
use WebServCo\Controller\Contract\ControllerInterface;
use WebServCo\Controller\Contract\ModuleControllerInstantiatorInterface;
use WebServCo\Controller\Contract\SpecificModuleControllerInstantiatorInterface;
use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\Reflection\Contract\ReflectionServiceInterface;
use WebServCo\View\Contract\ViewServicesContainerInterface;

use function class_exists;
use function in_array;

abstract class AbstractSpecificModuleControllerInstantiator implements SpecificModuleControllerInstantiatorInterface
{
    /**
     * Instantiate specific controller, based on a more general "module" controller.
     *
     * @param array<string,string> $interfaces
     */
    public function instantiateSpecificModuleController(
        ApplicationDependencyContainerInterface $applicationDependencyContainer,
        string $controllerClass,
        array $interfaces,
        ReflectionServiceInterface $reflectionService,
        ViewServicesContainerInterface $viewServicesContainer,
    ): ControllerInterface {

        $availableModuleControllerInstantiators = $this->getAvailableModuleControllerInstantiators();

        foreach ($availableModuleControllerInstantiators as $controllerInterfaceClass => $instantiatorClass) {
            if (in_array($controllerInterfaceClass, $interfaces, true)) {
                $instantiator = $this->instantiateModuleControllerInstantiator($instantiatorClass);

                return $instantiator->instantiateModuleController(
                    $applicationDependencyContainer,
                    $controllerClass,
                    $reflectionService,
                    $viewServicesContainer,
                );
            }
        }

        throw new UnexpectedValueException('Controller not handled.');
    }

    private function instantiateModuleControllerInstantiator(
        string $instantiatorClass,
    ): ModuleControllerInstantiatorInterface {
        if (!class_exists($instantiatorClass, true)) {
            throw new OutOfRangeException('Instantiator class does not exist.');
        }
        /**
         * Magic functionality; no static analysis.
         *
         * Psalm error: "Cannot call constructor on an unknown class".
         *
         * @psalm-suppress MixedMethodCall
         */
        $object = new $instantiatorClass();

        if (!$object instanceof ModuleControllerInstantiatorInterface) {
            throw new OutOfRangeException('Object is not an instance of the required interface.');
        }

        return $object;
    }
}
