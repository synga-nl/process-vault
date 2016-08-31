<?php
/**
 * Synga Inheritance Finder
 * @author      Roy Pouls
 * @copytright  2016 Roy Pouls / Synga (http://www.synga.nl)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        https://github.com/synga-nl/inheritance-finder
 */

namespace Synga\ProcessVault;

use App\Console\Commands\StopWatch;
use React\Promise\Deferred;
use React\Promise\Promise;
use Synga\Contracts\ProcessVault\Command\Command;
use Synga\Contracts\ProcessVault\Event\Dispatcher;
use Synga\Contracts\ProcessVault\Process\Pool\Pool;
use Synga\Contracts\ProcessVault\Queue\Queue;

/**
 * Class ProcessVault
 * @package Synga\ProcessVault
 */
class ProcessVault
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var \SplObjectStorage
     */
    protected $deferredStorage;

    /**
     * @var bool
     */
    protected $timerActive = false;

    /**
     * @var Pool
     */
    private $pool;
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * ProcessVault constructor.
     * @param Configuration $configuration
     * @param Pool $pool
     * @param Queue $queue
     * @param Dispatcher $dispatcher
     */
    public function __construct(Configuration $configuration, Pool $pool, Queue $queue, Dispatcher $dispatcher) {
        $this->queue         = $queue;
        $this->configuration = $configuration;
        $this->pool          = $pool;
        $this->dispatcher    = $dispatcher;
        $this->setup();
    }

    /**
     *
     */
    protected function setup(){
        $this->dispatcher->listen('process-vault.' . $this->configuration->getConfigurationId() . '.start', function () {
            $this->start();
        });
    }

    /**
     * @param $data
     * @return Promise
     */
    public function execute(Command $command) {
        $this->dispatcher->dispatch('process-vault.' . $this->configuration->getConfigurationId() . '.start');

        $deferred = new Deferred();

        $command->setQueuedAt();
        $command->setDeferred($deferred);

        $this->queue->enqueue($command);

        return $deferred->promise();
    }

    /**
     * @return $this
     */
    public function start() {
        if ($this->queue->count() > 0 && $this->timerActive === false) {
            $this->configuration->getLoop()->addPeriodicTimer($this->configuration->getTimerInterval(), function ($timer) {
                /* @var $timer \React\EventLoop\Timer\TimerInterface */
                $queueCount = $this->queue->count();
                if ($queueCount > 0) {
                    $this->timerActive = true;
                    for ($i = 0; $i < $queueCount; $i++) {
                        if ($this->pool->hasIdleProcess()) {
                            StopWatch::measure('dequeue_$1');
                            $queueItem = $this->queue->dequeue();
                            StopWatch::measure('dequeue_$1');
                            $this->pool->execute($queueItem);
                            unset($queueItem);
                        } else {
                            break;
                        }
                    }
                } else {
                    $this->dispatcher->dispatch('process-vault.' . $this->configuration->getConfigurationId() . '.stop');
                    $this->timerActive = false;
                    $timer->cancel();
                }
            });
        }

        return $this;
    }

    /**
     *
     */
    public function run(){
        $this->configuration->getLoop()->run();
    }

    /**
     * @return boolean
     */
    public function isTimerActive() {
        return $this->timerActive;
    }

    /**
     * @return bool
     */
    public function isQueueEmpty(){
        return ($this->queue->count() === 0);
    }
}