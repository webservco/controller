<?php

declare(strict_types=1);

namespace WebServCo\Controller\Service;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;
use WebServCo\Controller\Contract\ControllerInterface;
use WebServCo\Http\Contract\Message\Response\StatusCodeServiceInterface;
use WebServCo\View\Contract\ViewContainerInterface;
use WebServCo\View\View\MainView;

use function array_key_exists;
use function sprintf;

abstract class AbstractDefaultController extends AbstractDefaultControllerBase implements ControllerInterface
{
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
        $this->applicationDependencyContainer->getServiceContainer()->getLapTimer()
        ->lap(sprintf('%s: start', __FUNCTION__));

        $responseBody = $this->createResponseBody($request, $viewContainer);

        // Create Response.
        $response = $this->applicationDependencyContainer->getFactoryContainer()->getResponseFactory()->createResponse(
            $code,
        )
        ->withHeader('Content-Type', $this->viewServicesContainer->getViewRenderer()->getContentType())
        ->withBody($responseBody);

        $bodySize = $responseBody->getSize();

        if ($bodySize !== null) {
            $response = $response->withHeader('Content-Length', (string) $bodySize);
        }

        $this->applicationDependencyContainer->getServiceContainer()->getLapTimer()
        ->lap(sprintf('%s: end', __FUNCTION__));

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
}
