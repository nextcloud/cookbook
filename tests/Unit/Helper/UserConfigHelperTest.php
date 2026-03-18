<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use NCU\Config\IUserConfig;
use OCA\Cookbook\Exception\UserNotLoggedInException;
use OCA\Cookbook\Helper\UserConfigHelper;
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
	 * @var MockObject|IUserConfig
	 */
	private $config;

	/**
	 * @var Stub|IL10N
	 */
	private $l;

	protected function setUp(): void {
		parent::setUp();

		$this->userId = 'myuserid';

		$this->config = $this->createMock(IUserConfig::class);

		$this->l = $this->createStub(IL10N::class);
		$this->l->method('t')->willReturnArgument(0);

		$this->dut = new UserConfigHelper($this->userId, $this->config, $this->l);
	}

	public function testLastIndexUpdate() {
		$originalValue = 10;
		$saveValue = 20;

		$this->config->expects($this->exactly(2))->method('getValueInt')
			->with($this->userId, 'cookbook', 'last_index_update')
			->willReturnOnConsecutiveCalls($originalValue, $saveValue);

		$this->config->expects($this->once())->method('setValueInt')
			->with($this->userId, 'cookbook', 'last_index_update', $saveValue);

		$this->assertEquals($originalValue, $this->dut->getLastIndexUpdate());
		$this->dut->setLastIndexUpdate($saveValue);
		$this->assertEquals($saveValue, $this->dut->getLastIndexUpdate());
	}

	public function testLastIndexUpdateUnset() {
		$this->config->expects($this->once())->method('getValueInt')
			->with($this->userId, 'cookbook', 'last_index_update')
			->willReturn(0);

		$this->assertEquals(0, $this->dut->getLastIndexUpdate());
	}

	public function testUpdateInterval() {
		$originalValue = 10;
		$saveValue = 20;

		$this->config->expects($this->exactly(2))->method('getValueInt')
			->with($this->userId, 'cookbook', 'update_interval')
			->willReturnOnConsecutiveCalls($originalValue, $saveValue);

		$this->config->expects($this->once())->method('setValueInt')
			->with($this->userId, 'cookbook', 'update_interval', $saveValue);

		$this->assertEquals($originalValue, $this->dut->getUpdateInterval());
		$this->dut->setUpdateInterval($saveValue);
		$this->assertEquals($saveValue, $this->dut->getUpdateInterval());
	}

	public function testUpdateIntervalUnset() {
		$this->config->expects($this->once())->method('getValueInt')
			->with($this->userId, 'cookbook', 'update_interval')
			->willReturn(5);

		$this->assertEquals(5, $this->dut->getUpdateInterval());
	}

	public function testPrintImage() {
		$this->config->expects($this->exactly(3))->method('getValueBool')
			->with($this->userId, 'cookbook', 'print_image')
			->willReturnOnConsecutiveCalls(false, true, false);

		$expectedSpyArray = [
			[$this->userId, 'cookbook', 'print_image', true, null],
			[$this->userId, 'cookbook', 'print_image', false, null]
		];
		$spyArray = [];
		$this->config->expects($this->exactly(2))->method('setValueBool')
			->willReturnCallback(function (...$args) use (&$spyArray) {
				$spyArray[] = $args;
				return $args[3];
			});

		$this->assertFalse($this->dut->getPrintImage());
		$this->dut->setPrintImage(true);
		$this->assertTrue($this->dut->getPrintImage());
		$this->dut->setPrintImage(false);
		$this->assertFalse($this->dut->getPrintImage());

		$this->assertEquals($expectedSpyArray, $spyArray);
	}

	public function testPrintImageUnset() {
		$this->config->expects($this->once())->method('getValueBool')
			->with($this->userId, 'cookbook', 'print_image')
			->willReturn(true);

		$this->assertTrue($this->dut->getPrintImage());
	}

	public function testFolderName() {
		$originalValue = '/Recipes';
		$saveValue = '/My Recipes';

		$this->config->expects($this->exactly(2))->method('getValueString')
			->with($this->userId, 'cookbook', 'folder')
			->willReturnOnConsecutiveCalls($originalValue, $saveValue);

		$this->config->expects($this->once())->method('setValueString')
			->with($this->userId, 'cookbook', 'folder', $saveValue);

		$this->assertEquals($originalValue, $this->dut->getFolderName());
		$this->dut->setFolderName($saveValue);
		$this->assertEquals($saveValue, $this->dut->getFolderName());
	}

	public function testFolderNameUnset() {
		$this->config->expects($this->once())->method('getValueString')
			->with($this->userId, 'cookbook', 'folder')
			->willReturn('');

		$this->config->expects($this->once())->method('setValueString')
			->with($this->userId, 'cookbook', 'folder', '/Recipes');

		$this->assertEquals('/Recipes', $this->dut->getFolderName());
	}

	public function testNoUser() {
		$this->dut = new UserConfigHelper(null, $this->config, $this->l);

		$this->config->expects($this->never())->method('getValueString');
		$this->config->expects($this->never())->method('setValueString');

		$this->expectException(UserNotLoggedInException::class);
		$this->dut->getFolderName();
	}

	public function testGetBrowserlessConfig() {
		$this->config->expects($this->once())->method('setValueArray')->with(
			$this->userId, 'cookbook', 'browserless_config',
			[
				'url' => 'https://example.com',
				'token' => 'token',
			], false, flags: IUserConfig::FLAG_SENSITIVE
		);
		$this->dut->setBrowserlessConfig([
			'url' => 'https://example.com',
			'token' => 'token',
		]);

		$this->config->expects($this->once())->method('getValueArray')
			->with($this->userId, 'cookbook', 'browserless_config')
			->willReturn([
				'url' => 'https://example.com',
				'token' => 'token',
			]);

		$this->assertEquals(['url' => 'https://example.com', 'token' => 'token'], $this->dut->getBrowserlessConfig());

	}
}
