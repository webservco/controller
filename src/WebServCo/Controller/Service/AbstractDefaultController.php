<?php

declare(strict_types=1);

namespace WebServCo\Controller\Service;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;
use WebServCo\Controller\Contract\ControllerInterface;
use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\DependencyContainer\Contract\LocalDependencyContainerInterface;
use WebServCo\DependencyContainer\Helper\ApplicationDependencyServiceAccessTrait;
use WebServCo\Http\Contract\Message\Response\StatusCodeServiceInterface;
use WebServCo\View\Contract\TemplateServiceInterface;
use WebServCo\View\Contract\ViewContainerInterface;
use WebServCo\View\Contract\ViewServicesContainerInterface;

use function array_key_exists;
use function sprintf;

abstract class AbstractDefaultController implements ControllerInterface
{
    // Shortcut access to ApplicationDependencyContainerInterface service methods.
    use ApplicationDependencyServiceAccessTrait;

    /**
     * Get main ViewContainer to use.
     *
     * Should be customized at module level.
     * Eg. API module could use an API specific structure for the main View with some global data for all responses.
     */
    abstract protected function createMainViewContainer(
        ServerRequestInterface $request,
        ViewContainerInterface $viewContainer,
    ): ViewContainerInterface;

    /**
     * Create Template service (template group information).
     *
     * Should be customized at application level.
     * Idea: use a configuration of some sort eg. result of middleware processing
     * (different template group based on user preference).
     * We could get an already set attribute from route.
     */
    abstract protected function createTemplateService(string $projectPath): TemplateServiceInterface;

    public function __construct(
        /** General application level dependencies */
        protected ApplicationDependencyContainerInterface $applicationDependencyContainer,
        /** Local project dependencies. */
        protected LocalDependencyContainerInterface $localDependencyContainer,
        /** View services */
        protected ViewServicesContainerInterface $viewServicesContainer,
    ) {
    }

    /**
     * Create Response.
     *
     * Called by individual Controller `handle` method.
     */
    protected function createResponse(
        ServerRequestInterface $request,
        ViewContainerInterface $viewContainer,
        int $code = 200,
    ): ResponseInterface {
        $this->getLapTimer()->lap(
            sprintf('%s: start', __FUNCTION__),
        );

        // Create Response.
        $response = $this->applicationDependencyContainer->getFactoryContainer()->getResponseFactory()->createResponse(
            $code,
        )
            ->withHeader('Content-Type', $this->viewServicesContainer->getViewRenderer()->getContentType())
            ->withBody(
                $this->applicationDependencyContainer->getFactoryContainer()->getStreamFactory()->createStream(
                    $this->viewServicesContainer->getViewRenderer()->render(
                        $this->createAndSetupMainViewContainer($request, $viewContainer),
                    ),
                ),
            );

        $this->getLapTimer()->lap(
            sprintf('%s: end', __FUNCTION__),
        );

        return $response;
    }

    /**
     * Create redirect Response.
     *
     * Called by individual Controller `handle` method.
     */
    protected function createRedirectResponse(
        string $location,
        int $statusCode = StatusCodeInterface::STATUS_SEE_OTHER,
    ): ResponseInterface {
        if (!array_key_exists($statusCode, StatusCodeServiceInterface::REDIRECT_STATUS_CODES)) {
            throw new UnexpectedValueException('Status code not of redirect type.');
        }

        return $this->applicationDependencyContainer->getFactoryContainer()->getResponseFactory()->createResponse(
            $statusCode,
        )->withHeader('Location', $location);
    }

    /**
     * Create and set up the main ViewContainerInterface used in the response
     */
    private function createAndSetupMainViewContainer(
        ServerRequestInterface $request,
        ViewContainerInterface $viewContainer,
    ): ViewContainerInterface {
        // Set template service (template group information) to use.
        $templateService = $this->createTemplateService(
            $this->getConfigurationGetter()->getString(
                'PROJECT_PATH',
            ),
        );
        $viewContainer->setTemplateService($templateService);

        // Create main View (general page layout containing also the rendered page template).
        $mainViewContainer = $this->createMainViewContainer($request, $viewContainer);
        // Set template service (template group information) to use.
        $mainViewContainer->setTemplateService($templateService);

        return $mainViewContainer;
    }
}
