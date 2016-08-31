<?php
namespace Synga\ProcessVault\Process;


use React\Promise\Deferred;
use Synga\Contracts\ProcessVault\Command\Command;
use Synga\Contracts\ProcessVault\Process\Pool\Container\Container;

/**
 * Class Pool
 * @package Synga\ProcessVault\Process
 */
class Pool implements \Synga\Contracts\ProcessVault\Process\Pool\Pool
{
    /**
     * @var Container
     */
    private $poolContainer;

    /**
     * Pool constructor.
     * @param Container $poolContainer
     */
    public function __construct(Container $poolContainer) {
        $this->poolContainer = $poolContainer;
    }

    /**
     * @param $data
     * @param Deferred $deferred
     */
    public function execute(Command $command) {
        $process = $this->poolContainer->getProcess($command->getDeferred());

        $command->setExecutedAt();
        $command->setProcessId($process->getPid());
        $command->removeDeferred();

        $process->stdin->write(serialize($command));
        unset($command);
    }

    /**
     * @return mixed
     */
    public function hasIdleProcess() {
        return $this->poolContainer->hasIdleProcess();
    }
}