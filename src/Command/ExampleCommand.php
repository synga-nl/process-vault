<?php
namespace Synga\ProcessVault\Command;

class ExampleCommand extends CommandAbstract
{
    protected $pony;

    public function setPony(){
        $this->pony = rand(1,10000);
    }
}