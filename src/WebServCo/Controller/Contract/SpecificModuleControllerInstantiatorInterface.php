<?php

declare(strict_types=1);

namespace WebServCo\Controller\Contract;

use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\View\Contract\ViewServicesContainerInterface;

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
        ApplicationDependencyContainerInterface $applicationDependencyContainer,
        string $controllerClass,
        array $interfaces,
        ViewServicesContainerInterface $viewServicesContainer,
    ): ControllerInterface;
}
