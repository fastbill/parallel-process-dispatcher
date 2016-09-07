# fastbill/parallel-process-dispatcher

## Version: 1.0.3

## Overview

This micro-library has two classes. One encapsulates a (linux commandline) process into an object and allows asynchronous
running without deadlocks. The other is a multi-process-dispatcher which takes an arbitrary number of beforementioned 
processes and runs them simultaneously (with a maximum number of concurrent processes).
Usage-Examples:
- Dispatching long running cronjobs which e.g. mostly wait for webservice responses (you can run more processes than
  max. CPUs)
- Running background workers which listen on a queue (maximum should be number of CPUs)
- Running commandline-tasks inside a web application simultaneously, e.g. PDF-Generation, Image-Processing etc.


### Installation:

composer.json:
```json
{
    "repositories": [{
        "type": "git",
        "url": "git@git2.fastbill.com:shared/parallel-process-dispatcher.git"
    }],
    "require": {
        "fastbill/parallel-process-dispatcher": "~1.0"
    }
}
```
## Known Issues

### Process

* PHP Internals: Be aware that if the child process produces output, it will write into a buffer until the buffer is
full. If the buffer is full the child pauses until the parent reads from the buffer and makes more room. This is done
in the isFinished() method. The dispatcher calls this method periodically to prevent a deadlock. If you use the process
class standalone, you have to possibilities to prevent this:
  * call isFinished() yourself in either a loop, using a tick function or otherwise during execution of your script
  * instead of writing to stdOut, divert output to a temporary file and use its output.
  
### Dispatcher

* Multiple dispatchers (in different processes) are not aware of each other. So if you have a script that uses a
dispatcher to call another script which itself uses a dispatcher to spawn multiple processes, you will end up with more
child processes than the maximum, so choose the maximum accordingly or use a queue (e.g. Redis) and make the workers
aware of each other by e.g. registering in a redis-stack for running workers.
