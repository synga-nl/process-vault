<?php
namespace Synga\ProcessVault\Command;

use React\Promise\Deferred;
use Synga\Contracts\ProcessVault\Command\Command;

/**
 * Class CommandAbstract
 * @package Synga\ProcessVault\Command
 */
abstract class CommandAbstract implements Command
{
    /**
     * @var
     */
    protected $processId;

    /**
     * @var Deferred
     */
    protected $deferred;

    /**
     * @var
     */
    protected $payload;

    /**
     * @var integer
     */
    protected $queuedAt;

    /**
     * @var integer
     */
    protected $executedAt;

    /**
     * @var integer
     */
    protected $executeFinished;

    /**
     * @return mixed
     */
    public function getProcessId() {
        return $this->processId;
    }

    /**
     * @param int $id
     */
    public function setProcessId($id) {
        $this->processId = $id;
    }

    public function getQueuedAt() {
        return $this->queuedAt;
    }

    public function setQueuedAt() {
        if (empty($this->queuedAt)) {
            $this->queuedAt = microtime(true);

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getExecutedAt() {
        return $this->executedAt;
    }

    /**
     * @return bool
     */
    public function setExecutedAt() {
        if (empty($this->executedAt)) {
            $this->executedAt = microtime(true);

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getExecuteFinished() {
        return $this->executeFinished;
    }

    /**
     * @return bool
     */
    public function setExecuteFinished() {
        if (empty($this->executeFinished)) {
            $this->executeFinished = microtime(true);

            return true;
        }

        return false;
    }

    /**
     * @return Deferred
     */
    public function getDeferred() {
        return $this->deferred;
    }

    /**
     * @param Deferred $deferred
     * @return mixed|void
     */
    public function setDeferred(Deferred $deferred) {
        $this->deferred = $deferred;
    }

    public function removeDeferred() {
        $this->deferred = null;
    }

    /**
     * @return mixed
     */
    public function getPayload() {
        return $this->payload;
    }

    /**
     * @param $payload
     * @return mixed|void
     */
    public function setPayload($payload) {
        $this->payload = $payload;
    }
}