<?php

namespace WebDreamt\Filler;

use Faker\Generator;
use Propel\Generator\Model\PropelTypes;
use Propel\Runtime\Map\ColumnMap;

class ColumnTypeGuesser {

	public static $StartDate = '-30 years';
	public static $EndDate = 'now';
	protected $generator;

	public function __construct(Generator $generator) {
		$this->generator = $generator;
	}

	public function guessFormat(ColumnMap $column) {
		$generator = $this->generator;
		$type = $column->getType();
		if ($column->isTemporal()) {
			if (in_array($type, array(
						PropelTypes::TIMESTAMP,
						PropelTypes::DATE,
						PropelTypes::TIME))) {
				return function () use ($generator) {
					return $generator->dateTimeBetween(ColumnTypeGuesser::$StartDate, ColumnTypeGuesser::$EndDate);
				};
			} else {
				return function () use ($generator) {
					return $generator->dateTimeAD;
				};
			}
		}
		switch ($type) {
			case PropelTypes::BOOLEAN:
			case PropelTypes::BOOLEAN_EMU:
				return function () use ($generator) {
					return $generator->boolean;
				};
			case PropelTypes::NUMERIC:
				$size = $column->getSize();
				return function () use ($generator, $size) {
					return $generator->randomNumber($size + 2) / 100;
				};
			case PropelTypes::DECIMAL:
				return function () use ($generator) {
					return $generator->randomFloat(2, 0, 1000);
				};
			case PropelTypes::TINYINT:
				return function () {
					return mt_rand(0, 127);
				};
			case PropelTypes::SMALLINT:
				return function () {
					return mt_rand(0, 32767);
				};
			case PropelTypes::INTEGER:
				return function () {
					return mt_rand(0, intval('2147483647'));
				};
			case PropelTypes::BIGINT:
				return function () {
					return mt_rand(0, intval('9223372036854775807'));
				};
			case PropelTypes::FLOAT:
				return function () {
					return mt_rand(0, intval('2147483647')) / mt_rand(1, intval('2147483647'));
				};
			case PropelTypes::DOUBLE:
			case PropelTypes::REAL:
				return function () {
					return mt_rand(0, intval('9223372036854775807')) / mt_rand(1, intval('9223372036854775807'));
				};
			case PropelTypes::CHAR:
			case PropelTypes::VARCHAR:
			case PropelTypes::BINARY:
			case PropelTypes::VARBINARY:
				$size = $column->getSize();
				$size = ($size < 5 ? 5 : $size);
				return function () use ($generator, $size) {
					return $generator->text($size);
				};
			case PropelTypes::LONGVARCHAR:
			case PropelTypes::LONGVARBINARY:
			case PropelTypes::CLOB:
			case PropelTypes::CLOB_EMU:
			case PropelTypes::BLOB:
				return function () use ($generator) {
					return $generator->text;
				};
			case PropelTypes::ENUM:
				$valueSet = $column->getValueSet();

				return function () use ($generator, $valueSet) {
					return $generator->randomElement($valueSet);
				};
			case PropelTypes::OBJECT:
			case PropelTypes::PHP_ARRAY:
				// no smart way to guess what the user expects here
				return null;
		}
	}

}
