<?php

declare(strict_types=1);

namespace WebServCo\Controller\Service;

use OutOfRangeException;
use UnexpectedValueException;
use WebServCo\Controller\Contract\ControllerInstantiatorInterface;
use WebServCo\Controller\Contract\ControllerInterface;
use WebServCo\Controller\Contract\SpecificModuleControllerInstantiatorInterface;
use WebServCo\DependencyContainer\Contract\FactoryContainerInterface;
use WebServCo\DependencyContainer\Contract\ServiceContainerInterface;
use WebServCo\Route\Contract\RouteConfigurationInterface;
use WebServCo\Route\Service\ControllerView\RouteConfiguration;
use WebServCo\View\Contract\ViewContainerFactoryInstantiatorInterface;
use WebServCo\View\Contract\ViewRendererInstantiatorInterface;

use function class_exists;
use function class_implements;
use function in_array;
use function is_array;

final class ControllerInstantiator implements ControllerInstantiatorInterface
{
    public function __construct(
        private FactoryContainerInterface $factoryContainer,
        private ServiceContainerInterface $serviceContainer,
        private SpecificModuleControllerInstantiatorInterface $specificModuleControllerInstantiator,
        private ViewContainerFactoryInstantiatorInterface $viewContainerFactoryInstantiator,
        private ViewRendererInstantiatorInterface $viewRendererInstantiator,
    ) {
    }

    public function instantiateController(
        RouteConfigurationInterface $routeConfiguration,
        string $viewRendererClass,
    ): ControllerInterface {
        if (!$routeConfiguration instanceof RouteConfiguration) {
            throw new UnexpectedValueException('Route configuration is not of controller/view type.');
        }

        $this->validateController($routeConfiguration->controllerClass);

        $interfaces = $this->getImplementedInterfaces($routeConfiguration->controllerClass);
        if (!in_array(ControllerInterface::class, $interfaces, true)) {
            throw new UnexpectedValueException('Controller does not implement required interface.');
        }

        $viewContainerFactory = $this->viewContainerFactoryInstantiator->instantiateViewContainerFactory(
            $routeConfiguration->viewContainerFactoryClass,
        );

        $viewRenderer = $this->viewRendererInstantiator->instantiateViewRenderer($viewRendererClass);

        return $this->specificModuleControllerInstantiator->instantiateSpecificModuleController(
            $routeConfiguration->controllerClass,
            $this->factoryContainer,
            $interfaces,
            $this->serviceContainer,
            $viewContainerFactory,
            $viewRenderer,
        );
    }

    /**
     * "Return the interfaces which are implemented by the given class or interface".
     *
     * @return array<string,string>
     */
    private function getImplementedInterfaces(string $className): array
    {
        $interfaces = class_implements($className, false);
        if (!is_array($interfaces)) {
            throw new UnexpectedValueException('Controller interfaces list is not an array.');
        }

        return $interfaces;
    }

    private function validateController(string $controllerClassName): bool
    {
        /**
         * Validate controller.
         *
         * $autoload parameter must be "true" in order for this to work.
         */
        if (!class_exists($controllerClassName, true)) {
            throw new OutOfRangeException('Controller class does not exist.');
        }

        return true;
    }
}
