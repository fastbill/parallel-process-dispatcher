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
