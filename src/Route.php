<?php
declare(strict_types = 1);

namespace TJCDev\Router;

use Closure;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use TJCDev\Router\Contracts\RouteContract;
use TJCDev\Router\Exceptions\InvalidHTTPMethodException;

class Route implements RouteContract
{
    const METHODS = [
        'DELETE',
        'GET',
        'POST',
        'PUT'
    ];
    
    const PATTERN_SHAPE = [
        'key'     => 'string',
        'pattern' => 'string',
        'regex'   => 'boolean',
    ];
    
    protected Closure|string $callback;
    protected array $methods = [];
    protected string $pattern = '';
    
    #[ArrayShape([self::PATTERN_SHAPE])]
    protected array $segments = [];
    
    /**
     * @inheritDoc
     */
    public function __construct(string $pattern, array|string $methods, Closure|string $callback)
    {
        if ( ! is_array($methods)) {
            $methods = [$methods];
        }
        
        foreach ($methods as $method) {
            if ( ! in_array($method, self::METHODS)) {
                throw new InvalidHTTPMethodException("$method is not a valid HTTP Request Method");
            }
        }
        
        $this->pattern  = $pattern;
        $this->methods  = $methods;
        $this->callback = $callback;
        $this->breakDownPattern();
    }
    
    /**
     * @inheritDoc
     */
    #[Pure]
    public function checkForMatch(array $parts, string $method): bool
    {
        if ( ! in_array($method, $this->methods)) {
            return false;
        }
        
        if (($a = count($parts)) != ($b = count($this->segments))) {
            return false;
        }
        
        foreach ($this->segments as $i => $part) {
            if ($part['regex']) {
                if ( ! preg_match($part['pattern'], $parts[$i])) {
                    return false;
                }
            } elseif ($part['pattern'] != $parts[$i]) {
                return false;
            }
        }
        
        return true;
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
        $parts = explode('/', trim($this->pattern, '/'));
        foreach ($parts as $part) {
            if (preg_match('/^{(?<key>\w+)}(?:<(?<pattern>[\w\\\^\(\)+=<>?,.;:!£$%&*]+)>)?$/', $part, $matches)) {
                if ( ! array_key_exists('pattern', $matches)) {
                    $matches['pattern'] = '.+';
                }
                $this->segments[] = [
                    'key'     => $matches['key'],
                    'pattern' => "/{$matches['pattern']}/",
                    'regex'   => true
                ];
            } else {
                $this->segments[] = [
                    'key'     => '',
                    'pattern' => $part,
                    'regex'   => false
                ];
            }
        }
    }
}
