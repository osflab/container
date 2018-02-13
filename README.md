# OSF Container: dynamic object management mecanism

OSF Container manages instances of your classes in order to optimize performance and accessibility. Define the instantiation policy of each class and let OSF Container create and manage your objects.

## Features

* Lazy classes instanciations
* Optimized management of the instantiation policy
* Dependency management
* Mock objects generation & advanced mocking policy
* Extremely simplified access to objects

## Installation

You need at least php 7.1 and `composer`:

```bash
sudo apt install composer
```

### In your application via composer

This is the recommended way to use this feature in a non-osf project.

Just add `osflab/container` in your composer.json file.

### From github

To test the component or participate in its development.

```bash
git clone https://github.com/osflab/container.git
cd container && composer update
```

Unit tests launch:

```bash
php ./vendor/osflab/test/run-tests.php
```

## Usage

For example, to use `Osf\Cache` component (anyware in your code):

```php
$cache = \Osf\Container\OsfContainer::getCache();
```

To create your own container:

```php
use Osf\Container\AbstractContainer;

class MyContainer extends AbstractContainer
{
    /**
     * Build and get on-demand \My\Class instance(s)
     * @return \My\Class
     */
    public static function getMyClass(): \My\Class
    {
        return self::buildObject('\My\Class');
    }
}
```

The `AbstractContainer::buildObject()` method takes 3 args:

* The class name
* An array of class constructor values
* A namespace string

See `OsfContainer`, `ZendContainer` and `VendorContainer` for more examples.

