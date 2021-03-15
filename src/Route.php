<?php
declare(strict_types=1);

namespace TJCDev\Router;

use Closure;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use TJCDev\Router\Contracts\RouteContract;
use TJCDev\Router\Exceptions\InvalidHTTPMethodException;
use TJCDev\Router\Exceptions\RouteCallbackException;

class Route implements RouteContract
{
    const ALLOWED_METHODS = [
        'DELETE',
        'GET',
        'PATCH',
        'POST',
        'PUT',
    ];

    const SEGMENT_SHAPE = [
        'key' => 'string',
        'pattern' => 'string',
        'regex' => 'boolean',
    ];

    protected Closure|string $callback;
    protected array $methods = [];
    protected string $pattern = '';

    #[ArrayShape([self::SEGMENT_SHAPE])]
    protected array $segments = [];

    /**
     * @inheritDoc
     */
    public function __construct(string $pattern, array|string $methods, Closure|string $callback)
    {
        if (! is_array($methods)) {
            $methods = [$methods];
        }

        foreach ($methods as &$method) {
            if (! in_array($method = strtoupper($method), self::ALLOWED_METHODS)) {
                throw new InvalidHTTPMethodException("$method is not a valid HTTP Request Method");
            }
        }

        $this->pattern = $pattern;
        $this->methods = $methods;
        $this->callback = $callback;
        $this->breakDownPattern();
    }

    /**
     * @inheritDoc
     */
    #[Pure]
    public function checkForMatch(RequestInterface $request): array|bool
    {
        $args = [];
        if (! in_array($request->getMethod(), $this->methods)) {
            return false;
        }

        $segments = explode('/', trim($request->getUri()->getPath(), '/'));
        if (($a = count($segments)) != ($b = count($this->segments))) {
            return false;
        }

        foreach ($this->segments as $i => $segment) {
            if ($segment['regex']) {
                if (! preg_match($segment['pattern'], $segments[$i], $matches)) {
                    return false;
                }
                $args[$segment['key']] = $matches[0];
            } elseif ($segment['pattern'] != $segments[$i]) {
                return false;
            }
        }

        return $args;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Fetch the route broken down into individual segments.
     *
     * @return array
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * Break the pattern down into individual segments, each segment is separated by a slash (/). Check to see if the
     * segment is a variable and whether any regex rules have been applied to it. This sets it up to be matched
     * against given routes.
     */
    protected function breakDownPattern(): void
    {
        $segments = explode('/', trim($this->pattern, '/'));
        foreach ($segments as $segment) {
            if (preg_match('/^{(?<key>\w+)}(?:<(?<pattern>[\w\\\^\(\)+=<>?,.;:!£$%&*]+)>)?$/', $segment, $matches)) {
                if (! array_key_exists('pattern', $matches)) {
                    $matches['pattern'] = '.+';
                }
                $this->segments[] = [
                    'key' => $matches['key'],
                    'pattern' => "/{$matches['pattern']}/",
                    'regex' => true,
                ];
            } else {
                $this->segments[] = [
                    'key' => '',
                    'pattern' => $segment,
                    'regex' => false,
                ];
            }
        }
    }

    /**
     * Call the callback for this route and return the result.
     *
     * @return mixed
     * @throws RouteCallbackException
     */
    public function dispatch(): mixed
    {
        return $this->getCallable()();
    }

    /**
     * @return callable
     * @throws RouteCallbackException
     */
    protected function getCallable(): callable
    {
        if (is_callable($this->callback)) {
            return $this->callback;
        }

        if (str_contains($this->callback, '@')) {
            [$class, $method] = explode('@', $this->callback, 2);
            try {
                $reflection = new ReflectionClass($class);
                if (! $reflection->isInstantiable()) {
                    throw new RouteCallbackException(
                        sprintf(
                            'The class "%s" in route "%s" is not instantiable, only instantiable classes can be used by routes.',
                            $class,
                            $this->pattern
                        ));
                }

                if (! method_exists($class, $method))
                {
                    throw new RouteCallbackException(
                        sprintf(
                            'The method "%s::%s" in route "%s" could not be found.',
                            $class,
                            $method,
                            $this->pattern
                        ));
                }
            } catch (ReflectionException $e) {
                throw new RouteCallbackException(
                    sprintf('The class "%s" in route "%s" cannot be found.', $class, $this->pattern)
                );
            }
            $object = $this->instantiateClass($class);
            return [$object, $method];
        }

        throw new RouteCallbackException(sprintf('The callback for route "%s" could not be identified.', $this->pattern));
    }

    private function instantiateClass(string $class)
    {
        return new $class();
    }
}
