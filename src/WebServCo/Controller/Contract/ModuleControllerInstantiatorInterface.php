<?php

declare(strict_types=1);

namespace WebServCo\Controller\Contract;

use WebServCo\View\Contract\ViewContainerFactoryInterface;
use WebServCo\View\Contract\ViewRendererInterface;

/**
 * A specific, module-level controller instantiator interface.
 */
interface ModuleControllerInstantiatorInterface
{
    public function instantiateSpecificModuleController(
        string $controllerClassName,
        ViewContainerFactoryInterface $viewContainerFactory,
        ViewRendererInterface $viewRenderer,
    ): ControllerInterface;
}
