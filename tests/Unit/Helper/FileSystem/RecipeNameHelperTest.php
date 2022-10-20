<?php

namespace OCA\Cookbook\tests\Unit\Helper\FileSystem;

use OCA\Cookbook\Helper\FileSystem\RecipeNameHelper;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RecipeNameHelperFilter extends TestCase {
	/** @var RecipeNameHelper */
	private $dut;

	protected function setUp(): void {
		/** @var IL10N|Stub */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);
		$logger = $this->createStub(LoggerInterface::class);

		$this->dut = new RecipeNameHelper($l, $logger);
	}

	public function dpGetFolderName() {
		$tenChars = 'abcdefghij';
		$ninetyChars = str_repeat($tenChars, 9);

		return [
			'short name' => ['recipe name', 'recipe name'],
			'95 chars' => ["${ninetyChars}12345", "${ninetyChars}12345"],
			'99 chars' => ["${ninetyChars}123456789", "${ninetyChars}123456789"],
			'100 chars' => ["${ninetyChars}1234567890", "${ninetyChars}1234567890"],
			'101 chars' => ["${ninetyChars}12345678901", "${ninetyChars}1234567..."],
			'102 chars' => ["${ninetyChars}123456789012", "${ninetyChars}1234567..."],
			'105 chars' => ["${ninetyChars}123456789012345", "${ninetyChars}1234567..."],
			'special chars' => ['a/b:c?d!e"f|g\\h\'i^j&k#l', 'a_b_c_d_e_f_g_h_i_j_k_l'],
		];
	}

	/** @dataProvider dpGetFolderName */
	public function testGetFolderName($recipeName, $expected) {
		$this->assertEquals($expected, $this->dut->getFolderName($recipeName));
	}
}
