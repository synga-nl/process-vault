<?php
namespace Synga\ProcessVault\Command;

class ExampleHandler
{
    public function handle(ExampleCommand $command){
        $command->setPony();
    }
}