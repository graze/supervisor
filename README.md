# Supervisor #

**Version:** *1.0.x*<br/>
**Master build:** [![Master branch build status][travis-master]][travis]

This library implements CLI process supervisors and aggregate supervisor
supervisors in an attempt to limit damage done by failing scripts.<br/>
It can be installed in whichever way you prefer, but we recommend [Composer][packagist].
```json
{
    "require": {
        "graze/supervisor": "1.0.*"
    }
}
```

## Documentation
```php
<?php
use Graze\Supervisor\ProcessSupervisor;
use Symfony\Component\Process\Process;

$while = new Process('usr/bin/php whileTrueSleep.php');

$sup = new ProcessSupervisor($while);
$sup->start();
$sup->supervise(0.001); // Checks the "while" process every 0.001s (blocking)
```

Assuming everything went well with the child process, the `supervise` method
will stop watching the process and you can continue on your business. *But what
about processes that fall flat on their face?*

```text
Uncaught exception 'Graze\Supervisor\Exception\TerminatedProcessException' with message
The process was unexpectedly terminated
[process] /usr/bin/php whileTrueSleep.php
[code]    143
[text]    Termination (request to terminate)
[stderr]  Terminated
[stdout]
```

Now we've gone from one script failing, to two scripts failing, but how does
that help? Well, it doesn't, but that's where handlers come in.

### Handlers
Handlers help you control what to do when a child process fails. You can do
anything you like with the handlers:
 - Retry the process
 - Email your developers or infrastructure team
 - Requeue the job in some fancy queuing product
 - Simply throw an exception

```php
<?php
use Graze\Supervisor\Handler\RetryHandler;
use Graze\Supervisor\ProcessSupervisor;
use Symfony\Component\Process\Process;

$while = new Process('usr/bin/php whileTrueSleep.php');
$retry = new RetryHandler(3);

$sup = new ProcessSupervisor($while, $retry);
$sup->start();
$sup->supervise(0.001);
```

Now if your process dies, the retry handler will restart it up to a maximim of 3
times. We can do even better than this though; what if you want to retry 3 times
then throw an exception if we've maxed out our retries? Just decorate!

```php
<?php
use Graze\Supervisor\Handler\ExceptionHandler;
use Graze\Supervisor\Handler\RetryHandler;
use Graze\Supervisor\ProcessSupervisor;
use Symfony\Component\Process\Process;

$while = new Process('usr/bin/php whileTrueSleep.php');
$handler = new RetryHandler(3,
    new ExceptionHandler()
);

$sup = new ProcessSupervisor($while, $handler);
$sup->start();
$sup->supervise(0.001);
```

This library currently only comes bundled with a few handlers, but by
implementing a simple interface you can quickly use your own. Additional core
handlers are always welcome!


### Contributing ###
We accept contributions to the source via Pull Request,
but passing unit tests must be included before it will be considered for merge.
```bash
$ composer install
$ vendor/bin/phpunit
```

If you have [Vagrant][vagrant] installed, you can build our dev environment to assist development.
The repository will be mounted in `/srv`.
```bash
$ vagrant up
$ vagrant ssh

Welcome to Ubuntu 12.04 LTS (GNU/Linux 3.2.0-23-generic x86_64)
$ cd /srv
```

### License ###
The content of this library is released under the **MIT License** by **Nature Delivered Ltd**.<br/>
You can find a copy of this license at http://www.opensource.org/licenses/mit or in [`LICENSE`][license]

<!-- Links -->
[travis]: https://travis-ci.org/graze/supervisor
[travis-master]: https://travis-ci.org/graze/supervisor.png?branch=master
[packagist]: https://packagist.org/packages/graze/supervisor
[vagrant]: http://vagrantup.com
[license]: /LICENSE
