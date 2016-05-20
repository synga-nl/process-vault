<?php
/**
 * Created by PhpStorm.
 * User: webdev
 * Date: 19-5-16
 * Time: 13:46
 */

namespace Synga\ProcessVault\Pool;


class ProcessPool
{
    /**
     * @var ProcessPoolConfig
     */
    private $config;

    /**
     * @var \React\ChildProcess\Process[]
     */
    protected $processes = [];

    /**
     * @var array
     */
    protected $idle = [];

    /**
     * @var \React\Promise\Deferred[]
     */
    protected $deferred = [];

    public function __construct(ProcessPoolConfig $config) {
        $this->config = $config;
    }

    public function getProcess(\React\Promise\Deferred $deferred) {
        $processCount = count($this->processes);
        $idleCount    = count($this->idle);

        if ($processCount < $this->config->getMaxProcesses() && $idleCount == 0) {
            $this->spawnProcess();
        }

        if (count($this->idle) > 0) {
            reset($this->idle);
            $processId = current($this->idle);
            $idleKey   = key($this->idle);

            unset($this->idle[$idleKey]);

            $this->deferred[$processId] = $deferred;

            return $this->processes[$processId];
        }

        return false;
    }

    public function hasIdleProcess() {
        return count($this->idle) > 0 || $this->config->getMaxProcesses() > count($this->processes);
    }

    public function getProcessCount() {
        return count($this->processes);
    }

    protected function spawnProcess() {
        /* @var $loop \React\EventLoop\LoopInterface */
        $loop = $this->config->getLoop();

        $spawnChildProcess = $this->config->getSpawnProcessCallback();
        /* @var $process \React\ChildProcess\Process */
        $process = $spawnChildProcess();

        $process->start($loop);

        $processId                   = $process->getPid();
        $this->processes[$processId] = $process;

        $process->on('exit', function () use ($loop, $processId) {
            if (isset($this->processes[$processId])) {
                unset($this->processes[$processId]);
            }
        });

        $process->stdout->on('data', function ($output) use ($processId) {
            if (isset($this->deferred[$processId])) {
                $this->deferred[$processId]->resolve($output);
                unset($this->deferred[$processId]);
                $this->idle[] = $processId;
            }
        });

        $this->idle[] = $processId;
    }
}