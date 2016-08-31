<?php
namespace Synga\ProcessVault\Command\Bus\Middleware;

use Illuminate\Contracts\Container\Container;
use League\Tactician\Middleware;
use Synga\ProcessVault\Command\Bus\ClassNameExtractor;
use Synga\Contracts\ProcessVault\Command\Bus\ServiceProvider;

class ServiceProviderMiddleWare implements Middleware
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var ClassNameExtractor
     */
    private $extractor;

    /**
     * @var string
     */
    private $cache = [];

    /**
     * ServiceProviderMiddleware constructor.
     * @param Container $container
     * @param ClassNameExtractor $extractor
     */
    public function __construct(Container $container, ClassNameExtractor $extractor) {
        $this->extractor = $extractor;
        $this->container = $container;
    }


    /**
     * @param object $command
     * @param callable $next
     */
    public function execute($command, callable $next) {
        $class = $this->extractor->extractProvider($command);
        if (!in_array($class, $this->cache)) {
            if (class_exists($class)) {
                $serviceProvider = new $class();
                if ($serviceProvider instanceof ServiceProvider) {
                    $serviceProvider->setup($this->container);
                }
            }

            $cache[] = $class;
        }

        $next($command);
    }
}