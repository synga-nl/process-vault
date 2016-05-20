<?php
/**
 * Created by PhpStorm.
 * User: webdev
 * Date: 19-5-16
 * Time: 13:46
 */

namespace Synga\ProcessVault\Pool;


class ProcessPoolConfig
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
     * @var int
     */
    protected $maxProcesses;

    /**
     * @var int
     */
    protected $minProcesses;

    /**
     * ProcessVaultConfig constructor.
     * @param array $config
     */
    public function __construct(array $config = null) {
        if ($config !== null) {
            foreach ($config as $configKey => $configValue) {
                if (property_exists($this, $configKey)) {
                    $this->{$configKey} = $configValue;
                }
            }
        }
    }

    /**
     * @return \React\EventLoop\LoopInterface
     */
    public function getLoop() {
        return $this->loop;
    }

    /**
     * @param $loop
     * @return $this
     */
    public function setLoop($loop) {
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
     * @param $spawnProcessCallback
     * @return $this
     */
    public function setSpawnProcessCallback($spawnProcessCallback) {
        $this->spawnProcessCallback = $spawnProcessCallback;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxProcesses() {
        return $this->maxProcesses;
    }

    /**
     * @param $maxProcesses
     * @return $this
     */
    public function setMaxProcesses($maxProcesses) {
        $this->maxProcesses = $maxProcesses;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinProcesses() {
        return $this->minProcesses;
    }

    /**
     * @param $minProcesses
     * @return $this
     */
    public function setMinProcesses($minProcesses) {
        $this->minProcesses = $minProcesses;

        return $this;
    }
}