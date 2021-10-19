# Container

## How to use

```php

require '<project path>/vendor/Container/src/Container';

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