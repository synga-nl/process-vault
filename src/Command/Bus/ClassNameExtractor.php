<?php
namespace Synga\ProcessVault\Command\Bus;

use League\Tactician\Handler\CommandNameExtractor\CommandNameExtractor;

/**
 * Class ClassNameExtractor
 * @package Synga\ProcessVault\Command\Bus
 */
class ClassNameExtractor implements CommandNameExtractor
{
    /**
     * @param object $command
     * @return string
     */
    public function extract($command)
    {
        return $this->replaceClassNamePart($command, 'Command', 'Handler');
    }

    /**
     * @param $command
     * @return string
     */
    public function extractProvider($command){
        return $this->replaceClassNamePart($command, 'Command', 'ServiceProvider');
    }

    /**
     * @param $command
     * @param $replace
     * @param $with
     * @return string
     */
    protected function replaceClassNamePart($command, $replace, $with){
        list($explodedClassName, $key) = $this->getUnqualifiedClassKey($command);

        $explodedClassName[$key] = str_replace($replace, $with, $explodedClassName[$key]);

        return implode('\\', $explodedClassName);
    }

    /**
     * @param $command
     * @return array
     */
    protected function getUnqualifiedClassKey($command){
        $className = get_class($command);
        $explodedClassName = explode('\\', $className);
        end($explodedClassName);

        return [$explodedClassName, key($explodedClassName)];
    }
}