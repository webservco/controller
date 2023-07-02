<?php

declare(strict_types=1);

namespace WebServCo\Controller\Contract;

use WebServCo\DependencyContainer\Contract\FactoryContainerInterface;
use WebServCo\DependencyContainer\Contract\ServiceContainerInterface;
use WebServCo\View\Contract\ViewContainerFactoryInterface;
use WebServCo\View\Contract\ViewRendererInterface;

interface SpecificModuleControllerInstantiatorInterface
{
    /**
     * @return array<string,string>
     */
    public function getAvailableModuleControllerInstantiators(): array;

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
    ): ControllerInterface;
}
