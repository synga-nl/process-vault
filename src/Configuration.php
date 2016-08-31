<?php
namespace Synga\ProcessVault;

use React\EventLoop\LoopInterface;

/**
 * Class Configuration
 * @package Synga\ProcessVault
 */
class Configuration
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var \Closure
     */
    protected $spawnProcessCallback;

    /**
     * @var string
     */
    protected $pathVendorAutoload;

    /**
     * @var string
     */
    protected $configurationId;

    /**
     * @var integer
     */
    protected $maximalProcesses = 1;

    /**
     * @var integer
     */
    protected $minimalProcesses = 0;

    /**
     * @var float
     */
    protected $timerInterval = 0.001;

    /**
     * @var float
     */
    protected $garbageCollectorInterval = 0.1;

    /**
     * @var int
     */
    protected $killAfter = 0.1;

    /**
     * Configuration constructor.
     * @param LoopInterface $loop
     * @param $pathVendorAutoload
     * @param \Closure $spawnProcessCallback
     */
    public function __construct(LoopInterface $loop = null, $pathVendorAutoload, \Closure $spawnProcessCallback = null) {
        $this->loop                 = $loop;
        $this->spawnProcessCallback = ($spawnProcessCallback == null) ? $this->getDefaultClosure() : $spawnProcessCallback;
        $this->configurationId      = uniqid();
        $this->pathVendorAutoload   = $pathVendorAutoload;
    }


    protected function getDefaultClosure() {
        return function () {
            return new \React\ChildProcess\Process('php ' . __DIR__ . '/ProcessVaultChild.php ' . $this->pathVendorAutoload);
        };
    }

    /**
     * @return LoopInterface
     */
    public function getLoop() {
        return $this->loop;
    }

    /**
     * @param LoopInterface $loop
     * @return $this
     */
    public function setLoop(LoopInterface $loop) {
        $this->loop = $loop;

        return $this;
    }

    /**
     * @return \Closure
     */
    public function getSpawnProcessCallback() {
        return $this->spawnProcessCallback;
    }

    /**
     * @param \Closure $spawnProcessCallback
     * @return $this
     */
    public function setSpawnProcessCallback(\Closure $spawnProcessCallback) {
        $this->spawnProcessCallback = $spawnProcessCallback;

        return $this;
    }

    /**
     * @return string
     */
    public function getConfigurationId() {
        return $this->configurationId;
    }

    /**
     * @param string $configurationId
     */
    public function setConfigurationId($configurationId) {
        $this->configurationId = $configurationId;
    }

    /**
     * @return int
     */
    public function getMaximalProcesses() {
        return $this->maximalProcesses;
    }

    /**
     * @param int $maximalProcesses
     * @return $this
     */
    public function setMaximalProcesses($maximalProcesses) {
        $this->maximalProcesses = $maximalProcesses;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinimalProcesses() {
        return $this->minimalProcesses;
    }

    /**
     * @param int $minimalProcesses
     * @return $this
     */
    public function setMinimalProcesses($minimalProcesses) {
        $this->minimalProcesses = $minimalProcesses;

        return $this;
    }

    /**
     * @return float
     */
    public function getTimerInterval() {
        return $this->timerInterval;
    }

    /**
     * @param float $timerInterval
     */
    public function setTimerInterval($timerInterval) {
        $this->timerInterval = $timerInterval;
    }

    /**
     * @return float
     */
    public function getGarbageCollectorInterval() {
        return $this->garbageCollectorInterval;
    }

    /**
     * @param float $garbageCollectorInterval
     * @return $this
     */
    public function setGarbageCollectorInterval($garbageCollectorInterval) {
        $this->garbageCollectorInterval = $garbageCollectorInterval;

        return $this;
    }

    /**
     * @return int
     */
    public function getKillAfter() {
        return $this->killAfter;
    }

    /**
     * @param int $killAfter
     * @return $this
     */
    public function setKillAfter($killAfter) {
        $this->killAfter = $killAfter;

        return $this;
    }
}