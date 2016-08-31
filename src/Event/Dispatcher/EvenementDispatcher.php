<?php
namespace Synga\ProcessVault\Event\Dispatcher;

use Evenement\EventEmitterTrait;
use Synga\Contracts\ProcessVault\Event\Dispatcher;

class EvenementDispatcher implements Dispatcher
{
    use EventEmitterTrait;

    public function listen($event, \Closure $closure) {
        $this->on($event, $closure);
    }

    public function dispatch($event, $arguments = []) {
        $this->emit($event);
    }
}