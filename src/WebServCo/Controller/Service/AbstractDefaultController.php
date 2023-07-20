<?php

declare(strict_types=1);

namespace WebServCo\Controller\Service;

use Psr\Http\Message\ResponseInterface;
use WebServCo\Controller\Contract\ControllerInterface;
use WebServCo\DependencyContainer\Contract\ApplicationDependencyContainerInterface;
use WebServCo\DependencyContainer\Helper\ApplicationDependencyServiceAccessTrait;
use WebServCo\View\Contract\TemplateServiceInterface;
use WebServCo\View\Contract\ViewContainerInterface;
use WebServCo\View\Contract\ViewServicesContainerInterface;

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
    abstract protected function createMainViewContainer(ViewContainerInterface $viewContainer): ViewContainerInterface;

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
        protected ApplicationDependencyContainerInterface $applicationDependencyContainer,
        protected ViewServicesContainerInterface $viewServicesContainer,
    ) {
    }

    /**
     * Create Response.
     *
     * Called by individual Controller `handle` method.
     */
    protected function createResponse(ViewContainerInterface $viewContainer, int $code = 200): ResponseInterface
    {
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
                        $this->createAndSetupMainViewContainer($viewContainer),
                    ),
                ),
            );

        $this->getLapTimer()->lap(
            sprintf('%s: end', __FUNCTION__),
        );

        return $response;
    }

    /**
     * Create and set up the main ViewContainerInterface used in the response
     */
    private function createAndSetupMainViewContainer(ViewContainerInterface $viewContainer): ViewContainerInterface
    {
        // Set template service (template group information) to use.
        $templateService = $this->createTemplateService(
            $this->getConfigurationGetter()->getString(
                'PROJECT_PATH',
            ),
        );
        $viewContainer->setTemplateService($templateService);

        // Create main View (general page layout containing also the rendered page template).
        $mainViewContainer = $this->createMainViewContainer($viewContainer);
        // Set template service (template group information) to use.
        $mainViewContainer->setTemplateService($templateService);

        return $mainViewContainer;
    }
}
