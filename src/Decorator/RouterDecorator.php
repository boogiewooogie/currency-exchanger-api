<?php

namespace App\Decorator;

use App\Exception\BadRequestException;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

readonly class RouterDecorator implements RouterInterface, WarmableInterface
{
    public function __construct(
        private RouterInterface $router
    ) {
    }

    /**
     * @param RequestContext $context
     * @return void
     */
    public function setContext(RequestContext $context): void
    {
        $this->router->setContext($context);
    }

    /**
     * @return RequestContext
     */
    public function getContext(): RequestContext
    {
        return $this->router->getContext();
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        return $this->router->getRouteCollection();
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * @param string $pathinfo
     * @return array
     * @throws BadRequestException
     */
    public function match(string $pathinfo): array
    {
        $pathinfo === $_ENV['APP_PATH'] ?: throw new BadRequestException('Invalid API path');
        return $this->router->match(sprintf('/%s', $this->getPathInfo()));
    }

    /**
     * @return string
     * @throws BadRequestException
     */
    private function getPathInfo(): string
    {
        $query = [];
        parse_str($this->getContext()->getQueryString(), $query);
        return !empty($query['method']) ? $query['method'] : throw new BadRequestException('Invalid method name');
    }

    /**
     * @param string $cacheDir
     * @param string|null $buildDir
     * @return array<string>
     */
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        return [];
    }
}