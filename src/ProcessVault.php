<?php
/**
 * Created by PhpStorm.
 * User: webdev
 * Date: 19-5-16
 * Time: 13:34
 */

namespace Synga\ProcessVault;


use Synga\ProcessVault\Pool\ProcessPool;

class ProcessVault
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var ProcessPool
     */
    protected $vault;

    /**
     * @var \SplQueue
     */
    protected $queue;

    /**
     * @var bool
     */
    protected $timerActive = false;

    public function __construct($loop, ProcessPool $vault) {
        $this->vault = $vault;
        $this->loop  = $loop;
        $this->queue = new \SplQueue();
        $this->queue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);
    }

    /**
     * @param $data
     * @param \React\Promise\Deferred $deferred
     */
    public function execute($data, \React\Promise\Deferred $deferred) {
        if ($this->timerActive === false) {
            $this->startProcessingQueue();
        }

        $this->queue->enqueue(['data' => $data, 'deferred' => $deferred]);
    }

    protected function startProcessingQueue() {
        $this->loop->addPeriodicTimer(1, function(){
            var_dump('QUEUE COUNT: ' . $this->queue->count());
            var_dump('PROCESS COUNT: ' . $this->vault->getProcessCount());
        });

        $this->loop->addPeriodicTimer(0.001, function ($timer) {
            /* @var $timer \React\EventLoop\Timer\TimerInterface */
            $queueCount = $this->queue->count();
            if ($queueCount > 0) {
                for ($i = 0; $i < $queueCount; $i++) {
                    if ($this->vault->hasIdleProcess()) {
                        $queueItem = $this->queue->dequeue();
                        $process   = $this->vault->getProcess($queueItem['deferred']);

                        $queueItem['data']['process_id'] = $process->getPid();

                        $process->stdin->write(serialize($queueItem['data']));
                    } else {
                        break;
                    }
                }
            } else {
                $this->timerActive = false;
                $timer->cancel();
            }
        });

        $this->timerActive = true;
    }
}