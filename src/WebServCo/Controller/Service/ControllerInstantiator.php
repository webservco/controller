<?php

declare(strict_types=1);

namespace WebServCo\Controller\Service;

use OutOfRangeException;
use UnexpectedValueException;
use WebServCo\Controller\Contract\ControllerInstantiatorInterface;
use WebServCo\Controller\Contract\ControllerInterface;
use WebServCo\Controller\Contract\SpecificModuleControllerInstantiatorInterface;
use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\DependencyContainer\Contract\LocalDependencyContainerInterface;
use WebServCo\Reflection\Contract\ReflectionServiceInterface;
use WebServCo\Route\Contract\RouteConfigurationInterface;
use WebServCo\Route\Service\ControllerView\RouteConfiguration;
use WebServCo\View\Contract\ViewContainerFactoryInstantiatorInterface;
use WebServCo\View\Contract\ViewRendererInstantiatorInterface;
use WebServCo\View\Service\ViewServicesContainer;

use function class_exists;
use function class_implements;
use function in_array;
use function is_array;

final class ControllerInstantiator implements ControllerInstantiatorInterface
{
    public function __construct(
        private ApplicationDependencyContainerInterface $applicationDependencyContainer,
        private ReflectionServiceInterface $reflectionService,
        private SpecificModuleControllerInstantiatorInterface $specificModuleControllerInstantiator,
        private ViewContainerFactoryInstantiatorInterface $viewContainerFactoryInstantiator,
        private ViewRendererInstantiatorInterface $viewRendererInstantiator,
    ) {
    }

    public function instantiateController(
        LocalDependencyContainerInterface $localDependencyContainer,
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

        return $this->specificModuleControllerInstantiator->instantiateSpecificModuleController(
            $this->applicationDependencyContainer,
            $routeConfiguration->controllerClass,
            $interfaces,
            $localDependencyContainer,
            $this->reflectionService,
            $this->createViewServicesContainer($routeConfiguration, $viewRendererClass),
        );
    }

    private function createViewServicesContainer(
        RouteConfigurationInterface $routeConfiguration,
        string $viewRendererClass,
    ): ViewServicesContainer {
        if (!$routeConfiguration instanceof RouteConfiguration) {
            throw new UnexpectedValueException('Route configuration is not of controller/view type.');
        }

        $viewContainerFactory = $this->viewContainerFactoryInstantiator->instantiateViewContainerFactory(
            $this->applicationDependencyContainer->getDataExtractionContainer(),
            $routeConfiguration->viewContainerFactoryClass,
        );

        $viewRenderer = $this->viewRendererInstantiator->instantiateViewRenderer($viewRendererClass);

        return new ViewServicesContainer($viewContainerFactory, $viewRenderer);
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
