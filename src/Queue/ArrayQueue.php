<?php
namespace Synga\ProcessVault\Queue;

use Synga\Contracts\ProcessVault\Queue\Queue;

class ArrayQueue implements Queue
{
    protected $queue = [];

    public function count() {
        return count($this->queue);
    }

    public function enqueue($data) {
        array_push($this->queue, $data);
    }

    public function dequeue() {
        return array_pop($this->queue);
    }
}