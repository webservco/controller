<?php

declare(strict_types=1);

namespace WebServCo\Controller\Service;

use LogicException;
use OutOfRangeException;
use WebServCo\Controller\Contract\ControllerInterface;
use WebServCo\Controller\Contract\ModuleControllerInstantiatorInterface;
use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\DependencyContainer\Contract\LocalDependencyContainerInterface;
use WebServCo\Reflection\Contract\ReflectionServiceInterface;
use WebServCo\View\Contract\ViewServicesContainerInterface;

use function class_exists;
use function interface_exists;

/**
 * AbstractModuleControllerInstantiator
 *
 * No dependencies in constructor to minimize possible problems; implementations of this class are loaded dynamically.
 * Dynamic loading is in: `AbstractSpecificModuleControllerInstantiator.instantiateModuleControllerInstantiator.
 */
abstract class AbstractModuleControllerInstantiator implements ModuleControllerInstantiatorInterface
{
    /**
     * Default functionality to instantiate controller.
     * `instantiateModuleController` implementations should call this method and further validate the resulting object.
     */
    public function instantiateModuleController(
        ApplicationDependencyContainerInterface $applicationDependencyContainer,
        string $controllerClassName,
        LocalDependencyContainerInterface $localDependencyContainer,
        ReflectionServiceInterface $reflectionService,
        ViewServicesContainerInterface $viewServicesContainer,
    ): ControllerInterface {
        if (!class_exists($controllerClassName, true)) {
            throw new OutOfRangeException('Controller class does not exist.');
        }

        /** @todo test performance. Reflection code block start. */

        /**
         * Use reflection to validate parameters.
         */
        $this->validateControllerClassParameters($controllerClassName, $reflectionService);

        /**
         * Alternative instantiation method, using reflection.
         * Note: ReflectionClass was already instantiated in the verification above, same instance will be used here.
         * (no extra overhead).
         */
        $controllerReflectionClass = $reflectionService->getReflectionClass($controllerClassName);

        // Alternative solution without reflection: class_implements.
        if (!$controllerReflectionClass->implementsInterface(ControllerInterface::class)) {
            throw new LogicException('Class does not implement the required interface.');
        }

        $object = $controllerReflectionClass->newInstance(
            // Object: \WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface
            $applicationDependencyContainer,
            // Object: \WebServCo\DependencyContainer\Contract\LocalDependencyContainerInterface
            $localDependencyContainer,
            // Object: \WebServCo\View\Contract\ViewServicesContainerInterface
            $viewServicesContainer,
        );

        /** @todo test performance. Reflection code block stop. */

        /**
         * Initial instantiation method.
         * Kept for reference, in order to run performance tests.
         * For testing: comment all reflection related code above, enable this code.
         *
         * Psalm error: "Cannot call constructor on an unknown class".
         * Use: @psalm-suppress MixedMethodCall
         *
        $object = new $controllerClassName(
            // Object: \WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface
            $this->applicationDependencyContainer,
            // Object: \WebServCo\DependencyContainer\Contract\LocalDependencyContainerInterface
            $this->localDependencyContainer,
            // Object: \WebServCo\View\Contract\ViewServicesContainerInterface
            $viewServicesContainer,
        );
        */

        if (!$object instanceof ControllerInterface) {
            throw new LogicException('Object is not an instance of the required interface.');
        }

        return $object;
    }

    /**
     * @return array<int,string>
     */
    private function getControllerConstructorParameters(): array
    {
        return [
            1 => ApplicationDependencyContainerInterface::class,
            2 => LocalDependencyContainerInterface::class,
            3 => ViewServicesContainerInterface::class,
        ];
    }

    private function validateControllerClassParameters(
        string $controllerClassName,
        ReflectionServiceInterface $reflectionService,
    ): bool {
        foreach ($this->getControllerConstructorParameters() as $parameterIndex => $parameterInterface) {
            if (!interface_exists($parameterInterface, true)) {
                throw new OutOfRangeException('Interface does not exist.');
            }
            $parameterReflectionClass = $reflectionService->getConstructorParameterReflectionClassAtIndex(
                $controllerClassName,
                $parameterIndex,
            );
            if (!$parameterReflectionClass->implementsInterface($parameterInterface)) {
                throw new LogicException('Class does not implement the required interface.');
            }
        }

        return true;
    }
}
