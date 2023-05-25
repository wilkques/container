# Container

[![Latest Stable Version](https://poser.pugx.org/wilkques/container/v/stable)](https://packagist.org/packages/wilkques/container)
[![License](https://poser.pugx.org/wilkques/container/license)](https://packagist.org/packages/wilkques/container)

## How to use
`composer require wilkques/container`

```php

require '<project path>/vendor/Container/src/helpers.php';
require '<project path>/vendor/Container/src/Container.php';
```

## Method

1. `register`

	```php
	\Wilkques\Container\Container::register(
	'<your class name>',
	new '<your class name>'
	);

	container()->register(
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

	container()->register([
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

1. `bind`
	```php

	$abstract = new \Your\Class\Name;

	\Wilkques\Container\Container::bind('<your class name>', function () use ($abstract) {
		return $abstract;
	});

	// or

	container()->bind('<your class name>', function () use ($abstract) {
		return $abstract;
	});
	```

1. `get`

	```php
	\Wilkques\Container\Container::get('<your class name>');

	// or

	container()->get('<your class name>');
	```

1. `make`

	```php
	\Wilkques\Container\Container::make('<your class name>');

	// or

	container('<your class name>');

	// or

	container()->make('<your class name>');
	```

1. `call`
	```php
	\Wilkques\Container\Container::call(['<your class name>', '<your class method name>'], ['<your class method vars name>' => '<your class method vars value>']);

	// or

	\Wilkques\Container\Container::call([new '<your class name>', '<your class method name>'], ['<your class method vars name>' => '<your class method vars value>']);

	// or

	\Wilkques\Container\Container::call(function (\Your\Class\Name $abstract) {
		// do something
	});

	// or

	container()->call(['<your class name>', '<your class method name>'], ['<your class method vars name>' => '<your class method vars value>']);

	// or

	container()->call([new '<your class name>', '<your class method name>'], ['<your class method vars name>' => '<your class method vars value>']);

	// or

	container()->call(function (\Your\Class\Name $abstract) {
		// do something
	});
	```