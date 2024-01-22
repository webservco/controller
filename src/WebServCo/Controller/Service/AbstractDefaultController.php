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
use WebServCo\View\CommonView;
use WebServCo\View\Contract\HTMLRendererInterface;
use WebServCo\View\Contract\TemplateServiceInterface;
use WebServCo\View\Contract\ViewContainerInterface;
use WebServCo\View\Contract\ViewServicesContainerInterface;
use WebServCo\View\MainView;

use function array_key_exists;
use function sprintf;

abstract class AbstractDefaultController implements ControllerInterface
{
    use ApplicationDependencyServiceAccessTrait;

    // Shortcut access to ApplicationDependencyContainerInterface service methods.
    private ?CommonView $commonView = null;

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

    protected function createCommonView(ServerRequestInterface $request): CommonView
    {
        if ($this->commonView === null) {
            $this->commonView = new CommonView(
                $this->getConfigurationGetter()->getString('BASE_URL'),
                $request->getUri()->__toString(),
            );
        }

        return $this->commonView;
    }

    /**
     * Create main view container along with the template to use.
     *
     * Should be called from implementing code.
     */
    protected function createMainViewContainerWithTemplate(
        ServerRequestInterface $request,
        string $templateName,
        ViewContainerInterface $viewContainer,
    ): ViewContainerInterface {
        return $this->viewServicesContainer->getViewContainerFactory()->createViewContainerFromView(
            new MainView(
                $this->createCommonView($request),
                // data
                $this->viewServicesContainer->getViewRenderer()->render($viewContainer),
            ),
            // Set main template to use (can be customized - eg. different "theme" - based on user preference).
            $templateName,
        );
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
        /**
         * Handle non-templated-text (HTML) output.
         *
         * Examples: JSON, PDF, multimedia, etc.
         *
         * In this case:
         * - do not create a main View container,
         * - instead simply return the existing View container.
         *
         * Otherwise: main view data would be rendered as a string and placed in the `data` of the main view.
         */
        if ($this->viewServicesContainer->getViewRenderer()->getContentType() !== HTMLRendererInterface::CONTENT_TYPE) {
            return $viewContainer;
        }

        // Set template service (template group information) to use.
        $templateService = $this->createTemplateService(
            $this->getConfigurationGetter()->getString(
                'PROJECT_PATH',
            ),
        );
        $viewContainer->setTemplateService($templateService);

        /**
         * Create main View (general page layout containing also the rendered page template).
         * `createMainViewContainer` is located in implementing code.
         */
        $mainViewContainer = $this->createMainViewContainer($request, $viewContainer);
        // Set template service (template group information) to use.
        $mainViewContainer->setTemplateService($templateService);

        return $mainViewContainer;
    }
}
