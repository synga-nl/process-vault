<?php
namespace Synga\ProcessVault\Process;

use Psr\Log\LoggerInterface;

class ExitStatus
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger = null) {
        $this->logger = $logger;
    }

    public function determine($exitCode, $termSignal, $processId){
        var_dump('Process '. $processId .' exited with exit code: "' . $exitCode . '" terminal signal code: "' . $termSignal . '"');
    }
}