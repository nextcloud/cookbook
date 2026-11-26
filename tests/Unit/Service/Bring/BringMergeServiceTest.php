<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace OCA\Cookbook\tests\Unit\Service\Bring;

use OCA\Cookbook\Service\Bring\BringItemParser;
use OCA\Cookbook\Service\Bring\BringMergeService;
use PHPUnit\Framework\TestCase;

class BringMergeServiceTest extends TestCase {
	private BringItemParser $parser;
	private BringMergeService $service;

	protected function setUp(): void {
		parent::setUp();
		$this->parser = new BringItemParser();
		$this->service = new BringMergeService($this->parser);
	}

	private function listA(): array {
		return [
			'purchase' => [
				['uuid' => 'u1', 'itemId' => 'Zwiebel', 'spec' => '2 Stück'],
				['uuid' => 'u2', 'itemId' => 'Salz', 'spec' => ''],
				['uuid' => 'u3', 'itemId' => 'Milch', 'spec' => 'ca. 1 Liter'],
			],
			'recently' => [
				['uuid' => 'u4', 'itemId' => 'Butter', 'spec' => '250 g'],
			],
		];
	}

	private function listB(): array {
		return [
			'purchase' => [
				['uuid' => 'u5', 'itemId' => 'Zwiebel (Test)', 'spec' => '1'],
			],
			'recently' => [],
		];
	}

	public function testMergeSumIntoExistingPieceCount(): void {
		$rows = $this->service->plan([['line' => '1 Zwiebel']], $this->listA(), 'Test', 'merge');

		$this->assertSame('sum', $rows[0]['action']);
		$this->assertSame('Zwiebel', $rows[0]['itemId']);
		$this->assertSame('u1', $rows[0]['uuid']);
		$this->assertSame('2 Stück', $rows[0]['existingSpec']);
		$this->assertSame('3 Stück', $rows[0]['resultSpec']);
	}

	public function testMergeCaseInsensitiveMatchKeepsExistingName(): void {
		$rows = $this->service->plan([['line' => '1 zwiebel']], $this->listA(), 'Test', 'merge');

		$this->assertSame('sum', $rows[0]['action']);
		$this->assertSame('Zwiebel', $rows[0]['itemId']);
		$this->assertSame('u1', $rows[0]['uuid']);
	}

	public function testMergeNotesAppendedToSum(): void {
		$rows = $this->service->plan([['line' => '1 Zwiebel (rot)']], $this->listA(), 'Test', 'merge');

		$this->assertSame('3 Stück (rot)', $rows[0]['resultSpec']);
	}

	public function testMergeSumIntoExistingSpecWithNoteKeepsNoteOnce(): void {
		$list = [
			'purchase' => [
				['uuid' => 'u6', 'itemId' => 'Schweinebauch', 'spec' => '1 kg (mit Schwarte)'],
			],
			'recently' => [],
		];
		$rows = $this->service->plan([['line' => '1 kg Schweinebauch (mit Schwarte)']], $list, 'Test', 'merge');

		$this->assertSame('sum', $rows[0]['action']);
		$this->assertSame('Schweinebauch', $rows[0]['itemId']);
		$this->assertSame('u6', $rows[0]['uuid']);
		$this->assertSame('1 kg (mit Schwarte)', $rows[0]['existingSpec']);
		$this->assertSame('2 kg (mit Schwarte)', $rows[0]['resultSpec']);
	}

	public function testMergeSumCombinesDifferentNotes(): void {
		$list = [
			'purchase' => [
				['uuid' => 'u6', 'itemId' => 'Schweinebauch', 'spec' => '1 kg (mit Schwarte)'],
			],
			'recently' => [],
		];
		$rows = $this->service->plan([['line' => '1 kg Schweinebauch (ohne Knochen)']], $list, 'Test', 'merge');

		$this->assertSame('sum', $rows[0]['action']);
		$this->assertSame('2 kg (mit Schwarte) (ohne Knochen)', $rows[0]['resultSpec']);
	}

	public function testMergeBareIngredientAlreadyOnListIsUnchanged(): void {
		$rows = $this->service->plan([['line' => 'Salz', 'enabled' => true]], $this->listA(), 'Test', 'merge');

		$this->assertSame('unchanged', $rows[0]['action']);
		$this->assertSame('Salz', $rows[0]['itemId']);
		$this->assertSame('u2', $rows[0]['uuid']);
	}

	public function testMergeBareStapleDisabledByDefault(): void {
		$rows = $this->service->plan([['line' => 'Salz']], $this->listA(), 'Test', 'merge');

		$this->assertSame('skipped', $rows[0]['action']);
		$this->assertFalse($rows[0]['enabled']);
		$this->assertTrue($rows[0]['isStaple']);
	}

	public function testMergeUnreadableExistingSpecGivesSeparateEntry(): void {
		$rows = $this->service->plan([['line' => '500 ml Milch']], $this->listA(), 'Test', 'merge');

		$this->assertSame('add', $rows[0]['action']);
		$this->assertSame('Milch (Test)', $rows[0]['itemId']);
		$this->assertSame('', $rows[0]['uuid']);
		$this->assertNull($rows[0]['existingSpec']);
		$this->assertSame('500 ml', $rows[0]['resultSpec']);
	}

	public function testMergeCheckedOffEntryReaddedWithNewSpec(): void {
		$rows = $this->service->plan([['line' => '100 g Butter']], $this->listA(), 'Test', 'merge');

		$this->assertSame('readd', $rows[0]['action']);
		$this->assertSame('Butter', $rows[0]['itemId']);
		$this->assertSame('u4', $rows[0]['uuid']);
		$this->assertNull($rows[0]['existingSpec']);
		$this->assertSame('100 g', $rows[0]['resultSpec']);
	}

	public function testMergeNewIngredientAdded(): void {
		$rows = $this->service->plan([['line' => '3 Eier']], $this->listA(), 'Test', 'merge');

		$this->assertSame('add', $rows[0]['action']);
		$this->assertSame('Eier', $rows[0]['itemId']);
		$this->assertSame('', $rows[0]['uuid']);
		$this->assertNull($rows[0]['existingSpec']);
		$this->assertSame('3', $rows[0]['resultSpec']);
	}

	public function testMergeSequentialIdenticalLinesAccumulate(): void {
		$rows = $this->service->plan(
			[['line' => '1 Zwiebel'], ['line' => '1 Zwiebel']],
			$this->listA(),
			'Test',
			'merge'
		);

		$this->assertSame('3 Stück', $rows[0]['resultSpec']);
		$this->assertSame('4 Stück', $rows[1]['resultSpec']);
		$this->assertSame('u1', $rows[1]['uuid']);
	}

	public function testMergeRangeCannotBeSummed(): void {
		$rows = $this->service->plan([['line' => '2-3 Zwiebel']], $this->listA(), 'Test', 'merge');

		$this->assertSame('add', $rows[0]['action']);
		$this->assertSame('Zwiebel (Test)', $rows[0]['itemId']);
		$this->assertSame('2-3', $rows[0]['resultSpec']);
	}

	public function testMergeChosenAlternativeUsed(): void {
		$rows = $this->service->plan(
			[['line' => '100 ml Weißwein / Bier', 'name' => 'Bier']],
			$this->listA(),
			'Test',
			'merge'
		);

		$this->assertSame('Bier', $rows[0]['name']);
		$this->assertSame('Bier', $rows[0]['itemId']);
		$this->assertSame('add', $rows[0]['action']);
		$this->assertSame('100 ml', $rows[0]['resultSpec']);
		$this->assertSame(['Weißwein', 'Bier'], $rows[0]['alternatives']);
	}

	public function testMergeUnknownAlternativeIgnored(): void {
		$rows = $this->service->plan(
			[['line' => '100 ml Weißwein / Bier', 'name' => 'Wodka']],
			$this->listA(),
			'Test',
			'merge'
		);

		$this->assertSame('Weißwein', $rows[0]['name']);
	}

	public function testHeadersProduceNoRow(): void {
		$rows = $this->service->plan(
			[['line' => '## Soße'], ['line' => '3 Eier']],
			$this->listA(),
			'Test',
			'merge'
		);

		$this->assertCount(1, $rows);
		$this->assertSame(1, $rows[0]['index']);
	}

	public function testSeparateSameRecipeAndSpecUnchanged(): void {
		$rows = $this->service->plan([['line' => '1 Zwiebel']], $this->listB(), 'Test', 'separate');

		$this->assertSame('unchanged', $rows[0]['action']);
		$this->assertSame('Zwiebel (Test)', $rows[0]['itemId']);
		$this->assertSame('u5', $rows[0]['uuid']);
	}

	public function testSeparateChangedSpecUpdatesEntry(): void {
		$rows = $this->service->plan([['line' => '2 Zwiebel']], $this->listB(), 'Test', 'separate');

		$this->assertSame('update', $rows[0]['action']);
		$this->assertSame('Zwiebel (Test)', $rows[0]['itemId']);
		$this->assertSame('u5', $rows[0]['uuid']);
		$this->assertSame('1', $rows[0]['existingSpec']);
		$this->assertSame('2', $rows[0]['resultSpec']);
	}

	public function testSeparateNewEntryCarriesRecipeName(): void {
		$rows = $this->service->plan([['line' => '3 Eier']], $this->listB(), 'Test', 'separate');

		$this->assertSame('add', $rows[0]['action']);
		$this->assertSame('Eier (Test)', $rows[0]['itemId']);
	}

	public function testIsWrite(): void {
		$this->assertTrue($this->service->isWrite('sum'));
		$this->assertTrue($this->service->isWrite('add'));
		$this->assertTrue($this->service->isWrite('readd'));
		$this->assertTrue($this->service->isWrite('update'));
		$this->assertFalse($this->service->isWrite('unchanged'));
		$this->assertFalse($this->service->isWrite('skipped'));
	}
}
