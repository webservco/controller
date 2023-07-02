<?php

declare(strict_types=1);

namespace WebServCo\Controller\Service;

use OutOfRangeException;
use UnexpectedValueException;
use WebServCo\Controller\Contract\ControllerInterface;
use WebServCo\Controller\Contract\ModuleControllerInstantiatorInterface;
use WebServCo\Controller\Contract\SpecificModuleControllerInstantiatorInterface;
use WebServCo\DependencyContainer\Contract\FactoryContainerInterface;
use WebServCo\DependencyContainer\Contract\ServiceContainerInterface;
use WebServCo\View\Contract\ViewContainerFactoryInterface;
use WebServCo\View\Contract\ViewRendererInterface;

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
        string $controllerClass,
        FactoryContainerInterface $factoryContainer,
        array $interfaces,
        ServiceContainerInterface $serviceContainer,
        ViewContainerFactoryInterface $viewContainerFactory,
        ViewRendererInterface $viewRenderer,
    ): ControllerInterface {

        $availableModuleControllerInstantiators = $this->getAvailableModuleControllerInstantiators();

        foreach ($availableModuleControllerInstantiators as $controllerInterfaceClass => $instantiatorClass) {
            if (in_array($controllerInterfaceClass, $interfaces, true)) {
                $instantiator = $this->instantiateModuleControllerInstantiator(
                    $instantiatorClass,
                    $factoryContainer,
                    $serviceContainer,
                );

                return $instantiator->instantiateSpecificModuleController(
                    $controllerClass,
                    $viewContainerFactory,
                    $viewRenderer,
                );
            }
        }

        throw new UnexpectedValueException('Controller not handled.');
    }

    private function instantiateModuleControllerInstantiator(
        string $instantiatorClass,
        FactoryContainerInterface $factoryContainer,
        ServiceContainerInterface $serviceContainer,
    ): ModuleControllerInstantiatorInterface {
        if (!class_exists($instantiatorClass, true)) {
            throw new OutOfRangeException('Instantiator class does not exist.');
        }
        /**
         * Psalm error: "Cannot call constructor on an unknown class".
         *
         * @psalm-suppress MixedMethodCall
         */
        $object = new $instantiatorClass($factoryContainer, $serviceContainer);

        if (!$object instanceof ModuleControllerInstantiatorInterface) {
            throw new OutOfRangeException('Object is not an instance of the required interface.');
        }

        return $object;
    }
}
