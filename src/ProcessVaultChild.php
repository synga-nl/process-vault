<?php
try {
    if (isset($_SERVER['argv'], $_SERVER['argv'][1])) {
        $vendorAutoload = $_SERVER['argv'][1];
        if (is_file($vendorAutoload) && strpos($vendorAutoload, 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php') !== false) {
            include $vendorAutoload;
        }
    }

    if (!class_exists(\Composer\Autoload\ClassLoader::class)) {
        throw new InvalidArgumentException('Composer not instantiated, abort!');
    }

    $kernel = new \Synga\ProcessVault\Process\Child\Kernel();

    $loop = React\EventLoop\Factory::create();

    $read = new \React\Stream\Stream(STDIN, $loop);

    $read->on('data', function ($command) use ($loop, $kernel) {
        $command = unserialize($command);
        /* @var \Synga\Contracts\ProcessVault\Command\Command $command */

        try {
            if ($command instanceof \Synga\Contracts\ProcessVault\Command\Command) {
                $kernel->handle($command);
            }
        } catch (\Exception $e) {
            $command->setPayload($e->getMessage());
        }

        echo serialize($command);
    });
    $read->pipe(new \React\Stream\Stream(STDOUT, $loop));

    $loop->run();
} catch(\Exception $e){
}