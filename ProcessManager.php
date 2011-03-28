<?php
/**
 * @class ProcessManager
 * Class that handle creating multiple process
 * 
 * @licence GNU/GPLv3
 * 
 * @author Cyril Nicodème
 * 
 * 
 * @note : This doesn't work on Windows machine
 * @note : It is recommended to use this class in an cli environnement, 
 * 			forking is NOT recommended running from an Apache (or some other preforking web server) module
 * 
 * @see : http://www.ibuildings.com/blog/archives/1539-Boost-performance-with-parallel-processing.html
 */
class ProcessManager {
	/**
	 * Contain the Processus Id of the current processus
	 * 
	 * @var Integer $_iPid
	 */
	private $_iPid;
	
	/**
	 * Contain the priority for the current processus
	 * 
	 * @var Integer $_iPriority
	 */
	private $_iPriority = 0;

	/**
	 * Contain a list of all the childrens
	 * (in case the current processus is the father)
	 * 
	 * @var Array $_aChildrens
	 */
	private $_aChildrens = array ();

	/**
	 * Contain the number of max allowed childrens
	 * 
	 * @var Integer $_iMaxChildrens
	 */
	private $_iMaxChildrens = 2;

	/**
	 * Constructor
	 * Test if this application can be used, set the MaxChildren value, 
	 * retrieve his Process ID and set the signals
	 * 
	 * @param Integer $iMaxChildrens (optional)
	 * 
	 * @return ProcessManager
	 */
	public function __construct ($iMaxChildrens = 2) {
		if (!function_exists ('pcntl_fork'))
			throw new Exception ('Your configuration does not include pcntl functions.');
		
		if (!is_int ($iMaxChildrens) || $iMaxChildrens < 1)
			throw new Exception ('Childrens must be an Integer');

		$this->_iMaxChildrens = $iMaxChildrens;
		$this->_iPid = getmypid ();

		// Setting up the signal handlers
		$this->addSignal (SIGTERM, array ($this, 'signalHandler'));
		$this->addSignal (SIGQUIT, array ($this, 'signalHandler'));
		$this->addSignal (SIGINT, array ($this, 'signalHandler'));
	}

	
	public function __destruct () {
		foreach ($this->_aChildrens as $iChildrensPid)
			pcntl_waitpid ($iChildrensPid, $iStatus);
	}

	/**
	 * Fork a Processus
	 * 
	 * @return void
	 */
	public function fork ($mFunction, $aParams = array ()) {
		if (!is_string ($mFunction) && !is_array ($mFunction))
			throw new Exception ('Function given must be a String or an Array');
		
		if (!is_array ($aParams))
			throw new Exception ('Parameters must be an Array');
		
		$iPid = pcntl_fork ();

		if ($iPid === -1)
			throw new Exception ('Unable to fork.');
		elseif ($iPid > 0) {
			// We are in the parent process
			$this->_aChildrens[] = $iPid;

			if (count ($this->_aChildrens) >= $this->_iMaxChildrens) {
				pcntl_waitpid (array_shift ($this->_aChildrens), $iStatus);
			}
		}
		elseif ($iPid === 0) { // We are in the child process
			call_user_func_array ($mFunction, $aParams);
			exit (0);
		}
	}

	/**
	 * Add a new signal that will be called to the given function with some optionnals parameters
	 * 
	 * @param Integer $iSignal
	 * @param Mixed $mFunction
	 * @param Array $aParams[optional]
	 * 
	 * @return void
	 */
	public function addSignal ($iSignal, $mFunction) {
		if (!is_int ($iSignal))
			throw new Exception ('Signal must be an Integer.');

		if (!is_string ($mFunction) && !is_array ($mFunction))
			throw new Exception ('Function to callback must be a String or an Array.');

		if (!pcntl_signal ($iSignal, $mFunction))
			throw new Exception ('Unable to set up the signal.');
	}

	/**
	 * The default signal handler, to avoid Zombies
	 * 
	 * @param Integer $iSignal
	 * 
	 * @return void
	 */
	public function signalHandler ($iSignal = SIGTERM) {
		switch ($iSignal) {
			case SIGTERM: // Finish
				exit (0);
				break;
			case SIGQUIT: // Quit
			case SIGINT:  // Stop from the keyboard
			case SIGKILL: // Kill
				exit (1);
				break;
		}
	}

	/**
	 * Set the number of max childrens
	 * 
	 * @param Integer $iMaxChildren
	 * 
	 * @return void
	 */
	public function setMaxChildren ($iMaxChildren) {
		if (!is_int ($iMaxChildrens) || $iMaxChildrens < 1)
			throw new Exception ('Childrens must be an Integer');

		$this->_iMaxChildrens = $iMaxChildrens;
	}

	/**
	 * Return the current number of MaxChildrens
	 * 
	 * @return Integer
	 */
	public function getMaxChildrens () {
		return self::$_iMaxChildrens;
	}

	/**
	 * Set the priority of the current processus.
	 * 
	 * @param Integer $iPriority
	 * @param Integer $iProcessIdentifier[optional]
	 * 
	 * @return void
	 */
	public function setPriority ($iPriority, $iProcessIdentifier = PRIO_PROCESS) {
		if (!is_int ($iPriority) || $iPriority < -20 || $iPriority > 20)
			throw new Exception ('Invalid priority.');

		if ($iProcessIdentifier != PRIO_PROCESS 
				|| $iProcessIdentifier != PRIO_PGRP 
				|| $iProcessIdentifier != PRIO_USER)
			throw new Exception ('Invalid Process Identifier type.');

		if (!pcntl_setpriority ($iPriority, $this->_iPid, $iProcessIdentifier))
			throw new Exception ('Unable to set the priority.');
		
		self::$_iPriority = $iPriority;
	}

	/**
	 * Get the priority of the current processus.
	 * 
	 * @return Integer
	 */
	public function getPriority () {
		return self::$_iPriority;
	}

	/**
	 * Return the PID of the current process
	 * 
	 * @return Integer
	 */
	public function getMyPid () {
		return $this->_iPid;
	}
}
?>
