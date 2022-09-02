<?php

namespace OCA\Cookbook\tests\Unit\Controller;

use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

abstract class AbstractControllerTestCase extends TestCase {
	abstract protected function getClassName(): string;
	abstract protected function getImplementationClassName(): string;
	abstract protected function getMethodsAndParameters(): array;

	protected $dut;
	protected $impl;

	protected function setUp(): void {
		parent::setUp();
	}

	public function dpMethodNames() {
		$data = $this->getMethodsAndParameters();
		foreach ($data as $row) {
			$methodName = $row['name'];

			$implName = isset($row['implName']) ? $row['implName'] : $methodName;

			$once = isset($row['once']) ? $row['once'] : false;

			if (isset($row['args'])) {
				foreach ($row['args'] as $args) {
					yield [$methodName, $args, $implName, $once];
				}
			} else {
				yield [$methodName, [], $implName, $once];
			}
		}
	}

	/** @dataProvider dpMethodNames */
	public function testMethod($methodName, $args, $implName, $once) {
		$request = $this->createStub(IRequest::class);
		$impl = $this->createMock($this->getImplementationClassName());

		$cn = $this->getClassName();
		$class = new ReflectionClass($cn);
		$dut = $class->newInstance('cookbook', $request, $impl);

		$expected = $this->createStub(JSONResponse::class);

		if ($once) {
			$impl->expects($this->once())->method($implName)->with(...$args)->willReturn($expected);
		} else {
			$impl->method($implName)->with(...$args)->willReturn($expected);
		}

		$method = new ReflectionMethod($dut, $methodName);
		$result = $method->invokeArgs($dut, $args);

		$this->assertSame($expected, $result);
	}
}
