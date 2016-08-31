<?php
namespace Synga\ProcessVault\Process\Child;

use Illuminate\Contracts\Container\Container;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\Locator\CallableLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use Synga\ProcessVault\Command\Bus\ClassNameExtractor;
use Synga\ProcessVault\Command\Bus\Middleware\ServiceProviderMiddleWare;

class Kernel
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var string[]
     */
    protected $serviceProviders = [];

    /**
     * @return CommandBus
     */
    protected function buildCommandBus() {
        $extractor = new ClassNameExtractor();

        if (empty($this->container)) {
            $this->container = new \Illuminate\Container\Container();
        }

        $serviceProviderMiddleWare = new ServiceProviderMiddleware($this->container, $extractor);
        $handlerMiddleWare         = new CommandHandlerMiddleware($extractor, new CallableLocator([$this->container, 'make']), new HandleInflector());

        return new CommandBus([$serviceProviderMiddleWare, $handlerMiddleWare]);
    }

    public function handle($command) {
        $this->getCommandBus();

        return $this->commandBus->handle($command);
    }

    /**
     * @return Container
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container) {
        $this->container = $container;
    }

    /**
     * @return CommandBus
     */
    public function getCommandBus() {
        if (empty($this->commandBus)) {
            $this->commandBus = $this->buildCommandBus();
        }

        return $this->commandBus;
    }

    /**
     * @return \string[]
     */
    public function getServiceProviders() {
        return $this->serviceProviders;
    }

    /**
     * @param string $serviceProvider
     */
    public function addServiceProvider($serviceProvider) {
        $this->serviceProviders[] = $serviceProvider;
    }

    /**
     * @param string[] $serviceProviders
     */
    public function addServiceProviders(array $serviceProviders) {
        $this->serviceProviders = array_unique(array_merge($this->serviceProviders, $serviceProviders));
    }
}