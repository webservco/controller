<?php

declare(strict_types=1);

namespace WebServCo\Controller\Contract;

use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\Reflection\Contract\ReflectionServiceInterface;
use WebServCo\View\Contract\ViewServicesContainerInterface;

interface SpecificModuleControllerInstantiatorInterface
{
    /**
     * List of available controller interfaces and their instantiators.
     *
     * Can be
     * - more general (eg. APIControllerInterface)
     * - more specific (eg. FooAPIControllerInterface, BarAPIControllerInterface)
     * - or both (in that case make sure to put more specific items before the more general)
     *
     * key: controller interface class
     * value: instantiator class
     *
     * @return array<string,string>
     */
    public function getAvailableModuleControllerInstantiators(): array;

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
    ): ControllerInterface;
}
