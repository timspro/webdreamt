<?php

namespace WebDreamt\Filler;

use Faker\Generator;
use Faker\Provider\Base;
use Propel\Generator\Model\PropelTypes;

class Name {

	protected $generator;

	public function __construct(Generator $generator) {
		$this->generator = $generator;
	}

	public function guessFormat($name, $type) {
		$name = Base::toLower($name);
		$generator = $this->generator;
		if (preg_match('/^is[_A-Z]/', $name)) {
			return function () use ($generator) {
				return $generator->boolean;
			};
		}
		if (preg_match('/(_a|A)t$/', $name)) {
			return function () use ($generator) {
				return $generator->dateTime;
			};
		}
		if ($type === PropelTypes::VARCHAR) {
			switch ($name) {
				case 'first_name':
				case 'firstname':
					return function () use ($generator) {
						return $generator->firstName;
					};
				case 'last_name':
				case 'lastname':
					return function () use ($generator) {
						return $generator->lastName;
					};
				case 'username':
				case 'login':
					return function () use ($generator) {
						return $generator->userName;
					};
				case 'email':
					return function () use ($generator) {
						return $generator->email;
					};
				case 'phone_number':
				case 'phonenumber':
				case 'phone':
					return function () use ($generator) {
						return $generator->phoneNumber;
					};
				case 'streetaddress':
				case 'street_address':
				case 'address':
					return function () use ($generator) {
						return $generator->address;
					};
				case 'city':
					return function () use ($generator) {
						return $generator->city;
					};
				case 'zip':
				case 'postcode':
				case 'zipcode':
					return function () use ($generator) {
						return $generator->postcode;
					};
				case 'state':
					return function () use ($generator) {
						return $generator->state;
					};
				case 'country':
					return function () use ($generator) {
						return $generator->country;
					};
				case 'title':
					return function () use ($generator) {
						return $generator->sentence;
					};
				case 'body':
				case 'summary':
					return function () use ($generator) {
						return $generator->text;
					};
				case 'password':
					return function () use ($generator) {
						return $generator->sha256;
					};
			}
		}

		switch ($name) {
			case 'price':
			case 'cost':
			case 'salary':
				return function () use ($generator) {
					return $generator->randomFloat(2, 0, 1000);
				};
		}

		if (preg_match('/name$/', $name)) {
			return function () use ($generator) {
				return substr(ucwords($generator->text(rand(10, 30))), 0, -1);
			};
		}
		if (preg_match('/street_address$/', $name)) {
			return function () use ($generator) {
				return $generator->streetAddress;
			};
		}
		if (preg_match('/mileage$/', $name)) {
			return function () use ($generator) {
				return $generator->numberBetween(10, 50);
			};
		}
	}

}
