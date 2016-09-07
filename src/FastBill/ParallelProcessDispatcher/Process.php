<?php

namespace FastBill\ParallelProcessDispatcher;

/**
 * Class Process
 *
 * represents a process (commandline-call) for multithreading. wraps popen(), inspired bei jakub-onderka/parallel-lint
 *
 * @package FastBill\TraceAnalyzer
 */
class Process
{
	const STDIN  = 0;
	const STDOUT = 1;
	const STDERR = 2;

	const READ  = 'r';
	const WRITE = 'w';

	/** @var bool */
	protected $nonblockingMode = false;

	/** @var string */
	protected $command = '';

	/** @var resource */
	protected $process = null;

	/** @var resource */
	protected $stdout = null;

	/** @var resource */
	protected $stderr = null;

	/** @var string */
	private $output = '';

	/** @var string */
	private $errorOutput = '';

	/** @var int */
	private $statusCode = null;

	/** @var string */
	private $name = '';

	/**
	 * @param string $command
     * @param string $name
	 * @throws \RuntimeException
	 */
	public function __construct($command, $name = '')
	{
		$this->command = $command;
		$this->name = $name;
	}

	/**
	 * @param string $stdInInput
	 */
	public function start($stdInInput = null)
	{
		$descriptors = [
			self::STDIN  => array('pipe', self::READ),
			self::STDOUT => array('pipe', self::WRITE),
			self::STDERR => array('pipe', self::WRITE),
		];

		$this->process = proc_open(
			$this->command,
			$descriptors,
			$pipes,
			null,
			null,
			['bypass_shell' => true]
		);

		if ($this->process === false) {
			throw new \RuntimeException("Cannot create new process {$this->command}");
		}

		$stdin = $pipes[0];
		$this->stdout = $pipes[1];
		$this->stderr = $pipes[2];

		if ($stdInInput !== null) {
			fwrite($stdin, $stdInInput);
		}

		fclose($stdin);
	}


	/**
	 * @return bool
	 */
	public function isFinished()
	{
		if ($this->statusCode !== null) {
			return true;
		}

		$status = proc_get_status($this->process);

		if ($status['running']) {
			if (! $this->nonblockingMode) {
				stream_set_blocking($this->stdout, false);
				stream_set_blocking($this->stderr, false);
				$this->nonblockingMode = true;
			}
			$this->output      .= stream_get_contents($this->stdout);
			$this->errorOutput .= stream_get_contents($this->stderr);
			return false;
		} elseif ($this->statusCode === null) {
			$this->statusCode = (int) $status['exitcode'];
		}

		// Process remainder of outputs
		$this->output .= stream_get_contents($this->stdout);
		fclose($this->stdout);

		$this->errorOutput .= stream_get_contents($this->stderr);
		fclose($this->stderr);

		$statusCode = proc_close($this->process);

		if ($this->statusCode === null) {
			$this->statusCode = $statusCode;
		}

		$this->process = null;

		return true;
	}

	/**
	 * @return string
	 * @throws \RuntimeException
	 */
	public function getOutput()
	{
		if (!$this->isFinished()) {
			throw new \RuntimeException("Cannot get output for running process");
		}

		return $this->output;
	}

	/**
	 * @return string
	 * @throws \RuntimeException
	 */
	public function getErrorOutput()
	{
		if (!$this->isFinished()) {
			throw new \RuntimeException("Cannot get error output for running process");
		}

		return $this->errorOutput;
	}

	/**
	 * @return int
	 * @throws \RuntimeException
	 */
	public function getStatusCode()
	{
		if (!$this->isFinished()) {
			throw new \RuntimeException("Cannot get status code for running process");
		}

		return $this->statusCode;
	}

	/**
	 * @return bool
	 */
	public function isFail()
	{
		return $this->getStatusCode() === 1;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name = '')
	{
		$this->name = $name;
	}

}