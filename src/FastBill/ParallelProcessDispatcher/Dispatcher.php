<?php

namespace FastBill\ParallelProcessDispatcher;

class Dispatcher
{
	/** @var Process[] */
	private $processQueue = [];

	/** @var Process[] */
	private $runningProcesses = [];

	/** @var Process[] */
	private $finishedProcesses = [];

	/** @var int number of maximum parallel running processes */
	private $maxProcesses = 2;

	public function __construct($maxProcesses = 2)
	{
		if ($maxProcesses < 1) {
			throw new \InvalidArgumentException('number of processes must be at least 1');
		}
		$this->maxProcesses = $maxProcesses;
	}

	/**
	 * @param Process $process
	 * @param boolean $start if $start is true, after pushing the process to the queue, the running-processes-stack is checked for finished jobs and new
	 *                       ones will be taken from the queue until the maximum is reached.
	 */
	public function addProcess(Process $process, $start = false)
	{
		$this->processQueue[] = $process;

		if ($start) {
			$this->checkAndRemoveFinishedProcessesFromStack();
			$this->fillRunningStackAndStartJobs();
			$this->checkAndRemoveFinishedProcessesFromStack();
		}
	}

	/**
	 * this works over the whole queue and starts maxProcesses processes in parallel.
	 * returns if all are through.
	 */
	public function dispatch()
	{
		while ($this->hasProcessesInQueue() || $this->hasRunningProcesses()) {
			$this->fillRunningStackAndStartJobs();
			usleep(500);
			$this->checkAndRemoveFinishedProcessesFromStack();
			usleep(500);
		}
	}

	/**
	 * @return bool
	 */
	public function hasProcessesInQueue()
	{
		return count($this->processQueue) > 0;
	}

	/**
	 * @return bool
	 */
	public function hasRunningProcesses()
	{
		return count($this->runningProcesses) > 0;
	}


	/**
	 * @return Process[]
	 */
	public function getFinishedProcesses()
	{
		return $this->finishedProcesses;
	}

	/**
	 *
	 */
	protected function checkAndRemoveFinishedProcessesFromStack()
	{
		// check all running processes if they are still running,
		$finishedProcIds = [];
		foreach ($this->runningProcesses as $key => $runningProc) {
			// if one is finished, move to finishedProcesses
			if ($runningProc->isFinished()) {
				$finishedProcIds[] = $key;
				$this->finishedProcesses[] = $runningProc;
			}
		}

		// remove the finished ones from the running stack (has to be outside of loop
		foreach ($finishedProcIds as $procId) {
			unset ($this->runningProcesses[$procId]);
		}
	}

	/**
	 *
	 */
	protected function fillRunningStackAndStartJobs()
	{
		while ($this->hasProcessesInQueue() && count($this->runningProcesses) < $this->maxProcesses) {
			// get process from queue
			/** @var Process $proc */
			$proc = array_shift($this->processQueue);

			// start process
			$proc->start();

			// move to runningStack
			$this->runningProcesses[] = $proc;

			usleep (100);
		}
	}
}