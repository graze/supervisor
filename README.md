# Supervisor

<img src="https://media.giphy.com/media/dbtDDSvWErdf2/giphy.gif" alt="Supervisor" align="right" width=310 />

[![Build Status][ico-build]][travis]
[![Latest Version][ico-package]][package]
[![PHP ~5.5][ico-engine]][lang]
[![MIT Licensed][ico-license]][license]

This library implements CLI process supervisors and aggregate supervisor supervisors in an attempt to limit damage done by failing scripts.

It can be installed in whichever way you prefer, but we recommend [Composer][packagist].

`$ composer require graze/supervisor`

<!-- Links -->
[travis]: https://travis-ci.org/graze/supervisor
[lang]: https://secure.php.net
[package]: https://packagist.org/packages/graze/supervisor
[license]: https://github.com/graze/supervisor/blob/master/LICENSE

<!-- Images -->
[ico-license]: https://img.shields.io/packagist/l/graze/supervisor.svg
[ico-package]: https://img.shields.io/packagist/v/graze/supervisor.svg
[ico-build]: https://img.shields.io/travis/graze/supervisor/master.svg
[ico-engine]: https://img.shields.io/badge/php-%3E%3D5.5-8892BF.svg

## Documentation
```php
<?php
use Graze\Supervisor\ProcessSupervisor;
use Symfony\Component\Process\Process;

$while = new Process('/usr/bin/python while_true.py');

$sup = new ProcessSupervisor($while);
$sup->start();
$sup->supervise(0.001); // Check the "while" process every 0.001s (blocking)
```

Assuming everything went well with the child process, the `supervise` method
will stop watching the process and you can continue on your business. But what
happens to processes that fall flat on their face?

```text
Uncaught exception 'Graze\Supervisor\Exception\TerminatedProcessException' with message
The process was unexpectedly terminated
[process] /usr/bin/python while_true.py
[code]    143
[text]    Termination (request to terminate)
[stderr]  Terminated
[stdout]
```

Now we've gone from one script failing to two scripts failing, but how does
that help? Well, it doesn't, but that's where handlers come in.

### Handlers
Handlers help you control what to do when a child process fails (and when they
successfully terminate). You can do anything you like with the handlers:
 - Retry the process
 - Alert your developers or infrastructure team
 - Requeue the job in some fancy queuing service
 - Alert your error logging service
 - Start a different script
 - Stop other related scripts
 - Simply throw an exception

```php
<?php
use Graze\Supervisor\Handler\RetryHandler;
use Graze\Supervisor\ProcessSupervisor;
use Symfony\Component\Process\Process;

$while = new Process('/usr/bin/python while_true.py');
$retry = new RetryHandler(3);

$sup = new ProcessSupervisor($while, $retry);
$sup->start();
$sup->supervise();
```

Now if your process dies, the retry handler will restart it up to a maximim of 3
times. We can do even better than this though; suppose you want to retry 3 times
then alert developers and log the error, all before throwing an exception.
Just decorate!

```php
<?php
use Graze\Supervisor\Handler\ExceptionHandler;
use Graze\Supervisor\Handler\RetryHandler;
use Graze\Supervisor\Handler\UnexpectedTerminationHandler;
use Graze\Supervisor\ProcessSupervisor;
use Symfony\Component\Process\Process;

$while = new Process('/usr/bin/python while_true.py');
$handler = new RetryHandler(3,
    new MyPagerDutyHandler($pagerduty,
        new MyBugSnagErrorHandler($bugsnag,
            new ExceptionHandler(
                new UnexpectedTerminationHandler()
            )
        )
    )
);

$sup = new ProcessSupervisor($while, $handler);
$sup->start();
$sup->supervise();
```

This library currently only comes bundled with a few handlers, but by
implementing a simple interface you can quickly use your own. Additional core
handlers are always welcome!


### Supervising the supervisors
So having a supervisor to watch your process is great, but what if you want to
supervise multiple processes that are logically linked (ie. batch processing)?
You can achieve this by supervising your individual process supervisors.

```php
<?php
use Graze\Supervisor\ProcessSupervisor;
use Graze\Supervisor\SupervisorSupervisor;
use Symfony\Component\Process\Process;

$batchA = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=a'));
$batchB = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=b'));
$batchC = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=c'));

$sup = new SupervisorSupervisor([$batchA, $batchB, $batchC]);
$sup->start();
$sup->supervise();
```

Just like the process supervisors, the supervisor supervisors handle successful
and unsuccessful termination with handlers.

```php
<?php
use Graze\Supervisor\Handler\ExceptionHandler;
use Graze\Supervisor\Handler\RetryHandler;
use Graze\Supervisor\Handler\UnexpectedTerminationHandler;
use Graze\Supervisor\ProcessSupervisor;
use Graze\Supervisor\SupervisorSupervisor;
use Symfony\Component\Process\Process;

$batchA = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=a'));
$batchB = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=b'));
$batchC = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=c'));

// Retry all supervised processes if one fails (max 3 times)
$handler = new RetryHandler(3,
    new ExceptionHandler(
        new UnexpectedTerminationHandler()
    )
);

$sup = new SupervisorSupervisor([$batchA, $batchB, $batchC], $handler);
$sup->start();
$sup->supervise();
```

### Who supervises the supervisor supervisor?
Depending on the complexity of logically grouped processes, you may need to have
many tiers of failure management. This is entirely possible by passing
supervisor supervisors into a parent supervisor supervisor. In fact, you can
even mix the types of supervisors you supervise!

```php
<?php
use Graze\Supervisor\Handler\ExceptionHandler;
use Graze\Supervisor\Handler\RetryHandler;
use Graze\Supervisor\Handler\UnexpectedTerminationHandler;
use Graze\Supervisor\ProcessSupervisor;
use Graze\Supervisor\SupervisorSupervisor;
use Symfony\Component\Process\Process;

$batchA = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=a --half=a'));
$batchB = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=b --half=a'));
$batchC = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=c --half=a'));
$halfA  = new SupervisorSupervisor([$batchA, $batchB, $batchC]);

$batchD = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=d --half=b'));
$batchE = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=e --half=b'));
$batchF = new ProcessSupervisor(Process('/usr/bin/python batch.py --id=f --half=b'));
$halfB  = new SupervisorSupervisor([$batchD, $batchE, $batchF]);

$daemon = new ProcessSupervisor(Process('/usr/bin/php daemon.php'), new RetryHandler(1, new ExceptionHandler()));

$handler = new RetryHandler(3,
    new ExceptionHandler(
        new UnexpectedTerminationHandler()
    )
);

$sup = new SupervisorSupervisor([$halfA, $halfB, $daemon], $handler);
$sup->start();
$sup->supervise();
```

You can see how things could get crazy pretty quickly. This library, however, is
by no means a replacement for true system daemon management. You should use
something like **systemd** or **upstart** for that.


## Contributing

We accept contributions to the source via Pull Request, but passing unit tests
must be included before it will be considered for merge.

```bash
make build test
```

If you've found a bug, please include a failing test when you [create an issue][issue].

[issue]: https://github.com/graze/supervisor/issues/new

## License

The content of this library is released under the **MIT License** by **Nature Delivered Ltd.**.

You can find a copy of this license in [`LICENSE`][license] or at http://opensource.org/licenses/mit.
