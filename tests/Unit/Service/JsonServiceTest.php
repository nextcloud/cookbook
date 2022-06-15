<?php

namespace OCA\Cookbook\tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Service\JsonService;

class JsonServiceTest extends TestCase {
	
	/**
	 * @var JsonService
	 */
	private $service;

	public function setUp(): void {
		$this->service = new JsonService();
	}

	public function testIsSchemaObject() {
		// Objects must be encoded as arrays in JSON
		$testData = "notAnArray";
		$result = $this->service->isSchemaObject($testData);
		self::assertFalse($result, 'The object must be an array');

		// Objects must have a property @context
		$testData = [
			"@type" => "Recipe",
			"name" => "Schema.org Ontology",
			"subjectOf" => [
				"@type" => "Book",
				"name" => "The Complete History of Schema.org"
			]
		];
		$result = $this->service->isSchemaObject($testData);
		self::assertFalse($result, 'The object must have a context');
		
		// Context must be in schema.org domain
		$testData = [
			"@context" => "https://schema.com/",
			'@type' => 'Recipe',
			"name" => "Schema.org Ontology",
			"subjectOf" => [
				"@type" => "Book",
				"name" => "The Complete History of Schema.org"
			]
		];
		$result = $this->service->isSchemaObject($testData);
		self::assertFalse($result, 'The object must be in the correct context');
		
		// Objects must have a property @type
		$testData = [
			"@context" => "https://schema.org/",
			"name" => "Schema.org Ontology",
			"subjectOf" => [
				"@type" => "Book",
				"name" => "The Complete History of Schema.org"
			]
		];
		$result = $this->service->isSchemaObject($testData);
		self::assertFalse($result, 'The object must have the property @type');
		
		// No typecheck will be requested
		$testData = [
			"@context" => "https://schema.org/",
			"@type" => "Thing",
			"name" => "Schema.org Ontology",
			"subjectOf" => [
				"@type" => "Book",
				"name" => "The Complete History of Schema.org"
			]
		];
		$result = $this->service->isSchemaObject($testData);
		self::assertTrue($result);
		$result = $this->service->isSchemaObject($testData, '');
		self::assertTrue($result);

		// Check if type matches
		$testData = [
			"@context" => "https://schema.org/",
			"@type" => "Thing",
			"name" => "Schema.org Ontology",
			"subjectOf" => [
				"@type" => "Book",
				"name" => "The Complete History of Schema.org"
			]
		];
		$result = $this->service->isSchemaObject($testData, 'Thing');
		self::assertTrue($result, 'The type match but it returned false');
		$result = $this->service->isSchemaObject($testData, 'Foo');
		self::assertFalse($result, 'The type does not match bat it returned true');
	}

	public function testHasProperty() {
		// The method isSchemaObject() is tested in another test and assumed as working properly
		$testData = [
			"@context" => "https://schema.org/",
			"@type" => "Thing",
			"name" => "Schema.org Ontology",
			"subjectOf" => [
				"@type" => "Book",
				"name" => "The Complete History of Schema.org"
			]
		];
		$result = $this->service->hasProperty($testData, 'name');
		self::assertTrue($result, 'Property name was not found.');
		$result = $this->service->hasProperty($testData, 'Bar');
		self::assertFalse($result, 'Property Bar was falsely found.');
		
		$result = $this->service->hasProperty(['foo' => 'bar'], 'foo');
		self::assertFalse($result, 'Property of a non-object must not be returned.');
	}
}
