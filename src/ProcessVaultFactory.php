<?php
namespace Synga\ProcessVault;

use Synga\Contracts\ProcessVault\Event\Dispatcher;
use Synga\ProcessVault\Event\Dispatcher\EvenementDispatcher;
use Synga\ProcessVault\Queue\ArrayQueue;
use Synga\ProcessVault\Queue\SplQueue;

class ProcessVaultFactory
{
    public static function getProcessVault(Configuration $configuration, Dispatcher $dispatcher = null) {
        if ($dispatcher === null) {
            $dispatcher = new EvenementDispatcher();
        }

        if ($configuration->getLoop() === null) {
            $configuration->setLoop(\React\EventLoop\Factory::create());
        }

        $queue      = new ArrayQueue();
//        $queue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);
        $collection = new \Illuminate\Support\Collection();

        $garbageCollector = new \Synga\ProcessVault\Process\Pool\GarbageCollector($configuration, $dispatcher, $queue);
        $processPool      = new \Synga\ProcessVault\Process\Pool(new \Synga\ProcessVault\Process\Pool\Container($configuration, $collection, $garbageCollector, $dispatcher));

        return new \Synga\ProcessVault\ProcessVault($configuration, $processPool, $queue, $dispatcher);
    }
}