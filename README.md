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
	container()->register(
	'<your class name>',
	new '<your class name>'
	);

	// or

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

	container()->bind('<your class name>', function () use ($abstract) {
		return $abstract;
	});
	```

1. `singleton`
	The singleton method binds a class or interface into the container that should only be resolved one time. Once a singleton binding is resolved, the same object instance will be returned on subsequent calls into the container:
	```php
	$abstract = new \Your\Class\Name;

	container()->singleton('<your class name>', function () use ($abstract) {
		return $abstract;
	});
	```

1. `scoped`
	The scoped method binds a class or interface into the container that should only be resolved one time within a given Laravel request / job lifecycle. While this method is similar to the singleton method, instances registered using the scoped method will be flushed whenever the Laravel application starts a new "lifecycle"
	```php
	$abstract = new \Your\Class\Name;

	container()->scoped('<your class name>', function () use ($abstract) {
		return $abstract;
	});
	```

1. `get`

	```php
	container()->get('<your class name>');
	```

1. `make`

	```php
	container('<your class name>');

	// or

	container()->make('<your class name>');
	```

1. `call`
	```php
	container()->call(['<your class name>', '<your class method name>'], ['<your class method vars name>' => '<your class method vars value>']);

	// or

	container()->call([new '<your class name>', '<your class method name>'], ['<your class method vars name>' => '<your class method vars value>']);

	// or

	container()->call(function (\Your\Class\Name $abstract) {
		// do something
	});
	```

1. `forgetScopedInstances`
	Clear all of the scoped instances from the container.

1. `forgetInstance`
	```php
	container()->forgetInstance('<your class name>');
	```

1. `forgetInstances`
	Clear all of the instances from the container.

1. `flush`
	Flush the container of all bindings and resolved instances.