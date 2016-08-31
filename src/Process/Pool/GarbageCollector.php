<?php
namespace Synga\ProcessVault\Process\Pool;


use React\EventLoop\Timer\TimerInterface;
use Synga\Contracts\ProcessVault\Event\Dispatcher;
use Synga\Contracts\ProcessVault\Process\Pool\Container\Container;
use Synga\Contracts\ProcessVault\Queue\Queue;
use Synga\ProcessVault\Configuration;

class GarbageCollector
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Container
     */
    protected $processContainer;

    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var bool
     */
    protected $timerActive = false;

    public function __construct(Configuration $configuration, Dispatcher $dispatcher, Queue $queue) {
        $this->configuration = $configuration;
        $this->queue = $queue;
        $dispatcher->listen('process-vault.' . $this->configuration->getConfigurationId() . '.stop', function(){
            $this->timerActive = false;
        });
    }

    /**
     * @param Container $processContainer
     * @return $this
     */
    public function setProcessContainer(Container $processContainer) {
        $this->processContainer = $processContainer;

        return $this;
    }

    public function start() {
        if ($this->timerActive === false) {
            $loop = $this->configuration->getLoop();
            $loop->addPeriodicTimer($this->configuration->getGarbageCollectorInterval(), function (TimerInterface $timer) {
                if($this->timerActive === false){
                    $timer->cancel();
                }
                $processCount = $this->processContainer->getProcessCount();
                $usedTimes    = $this->processContainer->getUsedTimes();
                $idleProcesses = $this->processContainer->getIdleProcesses();

                asort($usedTimes);

                foreach ($idleProcesses as $processId) {
                    if(!isset($usedTimes[$processId])){
                        continue;
                    }

                    $time = $usedTimes[$processId];

                    if ($this->queue->count() == 0 && $time != 0 && ($this->configuration->getKillAfter() === -1 || ($time + $this->configuration->getKillAfter()) <= time())) {
                        $processCount--;
                        if ($processCount >= $this->configuration->getMinimalProcesses()) {
                            $this->processContainer->killProcess($processId);

                            continue;
                        }

                        break;
                    }

                }

                if ($processCount < $this->configuration->getMinimalProcesses() || $processCount === 0) {
                    $timer->cancel();
                }
            });

            $this->timerActive = true;
        }
    }
}