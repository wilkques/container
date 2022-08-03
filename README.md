# Container

[![Latest Stable Version](https://poser.pugx.org/wilkques/container/v/stable)](https://packagist.org/packages/wilkques/container)
[![License](https://poser.pugx.org/wilkques/container/license)](https://packagist.org/packages/wilkques/container)

## How to use
`composer require wilkques/container`

```php

require '<project path>/vendor/Container/src/Container';
```

## Method

1. `register`

	```php
	\Wilkques\Container\Container::register(
	'<your class name>',
	new '<your class name>'
	);

	// or

	\Wilkques\Container\Container::register([
		[
			'<your class name1>',
			new '<your class name1>'
		],
		[
			'<your class name2>',
			new '<your class name2>'
		],

		...
	]);
	```

1. `get`

	```php
	\Wilkques\Container\Container::get('<your class name>');
	```

1. `resolve`

	```php
	\Wilkques\Container\Container::resolve('<your class name>');
	```