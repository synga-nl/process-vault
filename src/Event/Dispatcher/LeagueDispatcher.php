<?php
namespace Synga\ProcessVault\Event\Dispatcher;


use League\Event\Emitter;
use Synga\Contracts\ProcessVault\Event\Dispatcher;

class LeagueDispatcher implements Dispatcher
{
    /**
     * @var Emitter
     */
    protected $emitter;

    public function __construct(Emitter $emitter) {
        $this->emitter = $emitter;
    }

    public function listen($event, \Closure $closure) {
        $this->emitter->addListener($event, $closure);
    }

    public function dispatch($event, $arguments = []) {
        $this->emitter->emit($event);
    }
}