<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Exception\UserNotLoggedInException;
use OCA\Cookbook\Helper\UserConfigHelper;
use OCP\IConfig;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cookbook\Helper\UserConfigHelper
 * @covers \OCA\Cookbook\Exception\UserNotLoggedInException
 */
class UserConfigHelperTest extends TestCase {
	/**
	 * @var UserConfigHelper
	 */
	private $dut;

	/**
	 * @var string
	 */
	private $userId;

	/**
	 * @var MockObject|IConfig
	 */
	private $config;

	/**
	 * @var Stub|IL10N
	 */
	private $l;

	protected function setUp(): void {
		$this->userId = 'myuserid';

		$this->config = $this->createMock(IConfig::class);

		$this->l = $this->createStub(IL10N::class);
		$this->l->method('t')->willReturnArgument(0);

		$this->dut = new UserConfigHelper($this->userId, $this->config, $this->l);
	}

	public function testLastIndexUpdate() {
		$originalValue = 10;
		$saveValue = 20;

		$this->config->expects($this->exactly(2))->method('getUserValue')
			->with($this->userId, 'cookbook', 'last_index_update')
			->willReturnOnConsecutiveCalls(strval($originalValue), strval($saveValue));

		$this->config->expects($this->once())->method('setUserValue')
			->with($this->userId, 'cookbook', 'last_index_update', strval($saveValue));

		$this->assertEquals($originalValue, $this->dut->getLastIndexUpdate());
		$this->dut->setLastIndexUpdate($saveValue);
		$this->assertEquals($saveValue, $this->dut->getLastIndexUpdate());
	}

	public function testLastIndexUpdateUnset() {
		$this->config->expects($this->once())->method('getUserValue')
			->with($this->userId, 'cookbook', 'last_index_update')
			->willReturn('');

		$this->assertEquals(0, $this->dut->getLastIndexUpdate());
	}

	public function testUpdateInterval() {
		$originalValue = 10;
		$saveValue = 20;

		$this->config->expects($this->exactly(2))->method('getUserValue')
			->with($this->userId, 'cookbook', 'update_interval')
			->willReturnOnConsecutiveCalls(strval($originalValue), strval($saveValue));

		$this->config->expects($this->once())->method('setUserValue')
			->with($this->userId, 'cookbook', 'update_interval', strval($saveValue));

		$this->assertEquals($originalValue, $this->dut->getUpdateInterval());
		$this->dut->setUpdateInterval($saveValue);
		$this->assertEquals($saveValue, $this->dut->getUpdateInterval());
	}

	public function testUpdateIntervalUnset() {
		$this->config->expects($this->once())->method('getUserValue')
			->with($this->userId, 'cookbook', 'update_interval')
			->willReturn('');

		$this->assertEquals(5, $this->dut->getUpdateInterval());
	}

	public function testPrintImage() {
		$this->config->expects($this->exactly(3))->method('getUserValue')
			->with($this->userId, 'cookbook', 'print_image')
			->willReturnOnConsecutiveCalls('0', '1', '0');

		$this->config->expects($this->exactly(2))->method('setUserValue')
		->withConsecutive(
				[$this->userId, 'cookbook', 'print_image', '1'],
				[$this->userId, 'cookbook', 'print_image', '0']
			);

		$this->assertFalse($this->dut->getPrintImage());
		$this->dut->setPrintImage(true);
		$this->assertTrue($this->dut->getPrintImage());
		$this->dut->setPrintImage(false);
		$this->assertFalse($this->dut->getPrintImage());
	}

	public function testPrintImageUnset() {
		$this->config->expects($this->once())->method('getUserValue')
			->with($this->userId, 'cookbook', 'print_image')
			->willReturn('');

		$this->assertTrue($this->dut->getPrintImage());
	}

	public function testFolderName() {
		$originalValue = '/Recipes';
		$saveValue = '/My Recipes';

		$this->config->expects($this->exactly(2))->method('getUserValue')
			->with($this->userId, 'cookbook', 'folder')
			->willReturnOnConsecutiveCalls($originalValue, $saveValue);

		$this->config->expects($this->once())->method('setUserValue')
			->with($this->userId, 'cookbook', 'folder', $saveValue);

		$this->assertEquals($originalValue, $this->dut->getFolderName());
		$this->dut->setFolderName($saveValue);
		$this->assertEquals($saveValue, $this->dut->getFolderName());
	}

	public function testFolderNameUnset() {
		$this->config->expects($this->once())->method('getUserValue')
			->with($this->userId, 'cookbook', 'folder')
			->willReturn('');

		$this->config->expects($this->once())->method('setUserValue')
			->with($this->userId, 'cookbook', 'folder', '/Recipes');

		$this->assertEquals('/Recipes', $this->dut->getFolderName());
	}

	public function testNoUser() {
		$this->dut = new UserConfigHelper(null, $this->config, $this->l);

		$this->config->expects($this->never())->method('getUserValue');
		$this->config->expects($this->never())->method('setUserValue');

		$this->expectException(UserNotLoggedInException::class);
		$this->dut->getFolderName();
	}
}
