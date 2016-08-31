<?php
namespace Synga\ProcessVault\tests;

use Synga\ProcessVault\ProcessVault;

class ProcessVaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessVault
     */
    protected $processVault;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockQueue;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockConfiguration;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockLoop;

    protected function setUp() {
        $this->mockQueue           = $this->getMock('SplQueue');
        $this->mockConfiguration   = $this->getMockBuilder('Synga\ProcessVault\Configuration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLoop            = $this->getMock('React\EventLoop\LoopInterface');

        $this->mockConfiguration->method('getLoop')->willReturn($this->mockLoop);

        $this->processVault = new ProcessVault($this->mockConfiguration, $this->mockQueue);
    }

    public function testExecute() {
        $this->mockQueue->expects($this->once())->method('enqueue');

        $result = $this->processVault->execute(['data' => 'pony']);

        $this->assertInstanceOf(\React\Promise\Promise::class, $result);
    }

    public function testStartWithEmptyQueue() {
        $this->processVault->start();

        $this->assertFalse($this->processVault->isTimerActive());
    }

    public function testStartWithQueueItems(){
        $this->mockConfiguration->expects($this->once())->method('getLoop');
        $this->mockLoop->expects($this->exactly(1))->method('addPeriodicTimer');
        $this->mockQueue->method('count')->willReturn(2);

        $this->processVault->start();
    }

    public function testStartWithRealLoop(){
        // to be done;)
        $this->mockConfiguration->method('getLoop')->willReturn($this->mockLoop);
    }
}