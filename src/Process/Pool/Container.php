<?php
namespace Synga\ProcessVault\Process\Pool;


use Illuminate\Support\Collection;
use React\Promise\Deferred;
use Synga\Contracts\ProcessVault\Event\Dispatcher;
use Synga\ProcessVault\Configuration;
use Synga\ProcessVault\Process\ExitStatus;

/**
 * Class Container
 * @package Synga\ProcessVault\Process\Pool
 */
class Container implements \Synga\Contracts\ProcessVault\Process\Pool\Container\Container
{
    /**
     * @var \SplObjectStorage
     */
    protected $deferredStorage;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var \React\ChildProcess\Process[]
     */
    protected $processes = [];

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var array
     */
    protected $idle = [];

    /**
     * @var array
     */
    protected $usedTime = [];


    /**
     * Container constructor.
     * @param Configuration $configuration
     * @param Collection $deferredStorage
     * @param GarbageCollector $garbageCollector
     * @param Dispatcher $dispatcher
     */
    public function __construct(Configuration $configuration, Collection $deferredStorage, GarbageCollector $garbageCollector, Dispatcher $dispatcher) {
        $this->deferredStorage = $deferredStorage;
        $this->configuration   = $configuration;

        $this->spawnMinimalProcesses();
        $garbageCollector->setProcessContainer($this);
        $this->dispatcher = $dispatcher;

        $this->dispatcher->listen('proces-vault.'.$this->configuration->getConfigurationId() .'.process-released', function() use ($garbageCollector) {
            $garbageCollector->start();
        });
    }

    /**
     * @param Deferred $deferred
     * @return bool|\React\ChildProcess\Process
     */
    public function getProcess(Deferred $deferred) {
        $processCount = count($this->processes);
        $idleCount    = count($this->idle);

        if ($processCount < $this->configuration->getMaximalProcesses() && $idleCount == 0) {
            $this->spawnProcess();
        }

        $idleInformation = $this->getIdleProcessInformation();

        if ($idleInformation !== false) {
            unset($this->idle[$idleInformation['idle_key']]);

            $this->deferredStorage[$idleInformation['process_id']] = $deferred;

            return $this->processes[$idleInformation['process_id']];
        }

        return false;
    }

    /**
     *
     */
    protected function spawnProcess() {
        $spawnChildProcess = $this->configuration->getSpawnProcessCallback();
        /* @var $process \React\ChildProcess\Process */
        $process = $spawnChildProcess();

        $process->start($this->configuration->getLoop());

        $processId                   = $process->getPid();
        $this->processes[$processId] = $process;

        $process->on('exit', function ($exitCode, $termSignal) use ($processId) {
            (new ExitStatus())->determine($exitCode, $termSignal, $processId);
            if (isset($this->processes[$processId])) {
                unset($this->processes[$processId]);
            }
        });

        $process->stdout->on('data', function ($output) use ($processId) {
            if ($this->deferredStorage->offsetExists($processId)) {
                $command = unserialize($output);
                /* @var \Synga\Contracts\ProcessVault\Command\Command $command */
                $command->setExecuteFinished();
                $this->deferredStorage[$processId]->resolve($command);
                $this->deferredStorage->forget($processId);
                $this->idle[] = $processId;
                $this->usedTime[$processId] = time();
                $this->dispatcher->dispatch('proces-vault.'.$this->configuration->getConfigurationId() .'.process-released');
            }
        });

//        var_dump('PROCESS ' . $process->getPid() . ' CREATED');

        $this->idle[] = $processId;
    }

    /**
     * @return bool
     */
    protected function spawnMinimalProcesses() {
        if ($this->configuration->getMinimalProcesses() <= $this->configuration->getMaximalProcesses()) {
            for ($i = 1; $i <= $this->configuration->getMinimalProcesses(); $i++) {
                $this->spawnProcess();
            }

            return true;
        }

        throw new \LogicException('The maximal process count cannot be less than the minimal process count');
    }

    /**
     * @return array|bool
     */
    protected function getIdleProcessInformation() {
        sort($this->idle);

        if ($this->hasIdleProcess()) {
            return ['process_id' => current($this->idle), 'idle_key' => key($this->idle)];
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasIdleProcess() {
        return count($this->idle) > 0 || $this->configuration->getMaximalProcesses() > count($this->processes);
    }

    /**
     * @param $processId
     */
    public function killProcess($processId) {
        if (isset($this->processes[$processId])) {
            $this->processes[$processId]->terminate();

            unset($this->processes[$processId]);
            $this->idle = array_diff($this->idle, [$processId]);
        }
    }

    /**
     * @return int
     */
    public function getProcessCount() {
        return count($this->processes);
    }

    /**
     * @return array
     */
    public function getUsedTimes() {
        return $this->usedTime;
    }

    /**
     * @return array
     */
    public function getIdleProcesses() {
        return $this->idle;
    }
}