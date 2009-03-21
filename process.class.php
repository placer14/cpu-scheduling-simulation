<?php

  /*
  *  process.class.php
  *  Written by: Michael Greenberg
  *  Date: March 20, 2009
  *  =============================  
  *  This work is licensed under the Creative Commons Attribution-Noncommercial-
  *  Share Alike 3.0 United States License. To view a copy of this license, 
  *  visit http://creativecommons.org/licenses/by-nc-sa/3.0/us/ or send a letter 
  *  to Creative Commons, 171 Second Street, Suite 300, San Francisco, 
  *  California, 94105, USA.
  *  =============================
  *  
  *  public function __construct ($inputBursts, $arrTime=0)
  *     Constuctor function. 
  *       $inputBursts is an array with an odd number of values.
  *       $arrTime is the time which the process becomes "new".
  *  public function getStats ()
  *     Returns an associative array with the following indices: 
  *     'responseTime', 'waitTime', 'turnaroundTime'
  *  public function getState()
  *     Returns a string identifing the state of the process. Can be any of the 
  *     following values:
  *     'unsubmitted', 'new', 'ready', 'cpu', 'io', 'wait', 'finished' 
  *  public function setState ($toState)
  *     Sets the state of the process.
  *       $toState is a string with the following values:
  *       'new', 'ready', 'cpu', 'io', 'wait', 'finished'
  *  public function getPriority ()
  *     Returns an integer value identifying the priority of the process. 
  *     Lower value has higher priority.
  *  public function setPriority ($priority)
  *     Sets the priority of the process.
  *       $priority is an integer >= 0.
  *  public function getID()
  *     Returns a int value representing the Process ID.
  *  public function setID($name)
  *     Sets the process ID of the process.
  *       $name is an integer >=0.
  *  public function getArrival ()
  *     Returns an integer value which is the time the process "new".
  *  public function getCurrentBurst()
  *     Returns an integer value which identifies the remaining burst for the 
  *     current process state.
  *  public function updateProc()
  *     Pushes the process through a tick and changes internal variables
  *     according to their state.
  *  public function printStatus ($format="full")
  *     Outputs the current status of the process.
  *       $format defaults 'full' but can also be set to 'short' or 'line'.
  *  public function printHistory()
  *     Outputs one line which shows the change of states from one tick to the 
  *     next using the first letter of the state name.
  */  

class Process {        

  private $tick;
  private $process_id;
  private $priority;
  private $state;
  private $arrivalTime;
  private $responseTime;
  private $waitTime;
  private $turnAroundTime;
  private $burstStack;
  private $currentBurst;  
  private $history;

  
  public function __construct ($inputBursts, $arrTime=0) {
    if ((count($inputBursts)%2) != 1) {
      trigger_error("Must use an odd number of input bursts!",E_USER_ERROR);
      die();
    }
    $this->tick = 0;
    $this->process_id = '';
    $this->priority = 0;
    if ($arrTime == 0) {
      $this->state = 'new';
    } else {
      $this->state = 'unsubmitted';
    }
    $this->arrivalTime = $arrTime;
    $this->responseTime = 0;
    $this->waitTime = 0;
    $this->turnaroundTime = 0;
    $this->burstStack = array();
    $this->burstStack = $inputBursts;
    $this->currentBurst = array_shift($this->burstStack);
    $this->history = array();
  }
  
  public function getStats () {
    return array(
      'responseTime' => $this->responseTime,
      'waitTime' => $this->waitTime,
      'turnaroundTime' => $this->turnaroundTime
    );
  }
  
  public function getState() {
    return $this->state;
  }
  
  public function setState ($toState) {
    switch ($toState) {
      case 'new':
        $this->state = 'new';
      return true;
      case 'ready':
        $this->state = 'ready';
      return true;
      case 'cpu':
        $this->state = 'cpu';
      return true;
      case 'io':
        $this->state = 'io';
      return true;
      case 'wait':
        $this->state = 'wait';
      return true;
      case 'finished':
        $this->state = 'finished';
        echo "Process P".$this->getID()." has completed!<br />";
      return true;
      default:
        trigger_error("Undefined state switch to '$toState' on Process '$this->process_id'",E_USER_NOTICE);
      return false;
    }
  }
  
  public function getPriority () {
    return $this->priority;
  }
  
  public function setPriority ($priority) {    
    if (is_int($priority) && $priority >= 0) {
      $this->priority = $priority;
      return true;
    } else {
      echo "Priority is $priority <br />";
      trigger_error("Priority must be set with an integer value greater than or equal to zero.",E_USER_ERROR);
      return false;
    }
  }
  
  public function getID() {
    return $this->process_id;
  }
  
  public function setID($name) {
    if (is_int($name) && $name >= 0) {
      $this->process_id = $name;
      return true;
    } else {
      trigger_error("Process ID must be set with an integer value greater than or equal to zero.",E_USER_ERROR);
      return false;
    }
  }
  
  public function getArrival () {
    return $this->arrivalTime;
  }
  
  public function getCurrentBurst() {
    return $this->currentBurst;
  }
  
  private function updateBurstStack () {
    $this->currentBurst--;
    if ($this->currentBurst == 0) {
      $this->currentBurst = array_shift($this->burstStack);
      if ($this->currentBurst == false) {
        return -1; // burstStack is empty.
      }
      return 0; // burstStack is switching states.
    }
    return 1; // burstStack has decreased by one.
  }
  
  public function updateProc() {
    // Pre ppdate
    if($this->tick == $this->arrivalTime && $this->getState() == 'unsubmitted') {
      $this->setState('new');
    }
    
    // Update
    if($this->getState() == 'unsubmitted') {
      $this->history[$this->tick] = 'U';
    }elseif($this->getState() == 'new') {
      $this->history[$this->tick] = 'N';
    }elseif($this->getState() == 'ready') {
      $this->history[$this->tick] = 'R';
      $this->turnaroundTime++;
      $this->waitTime++;
      $this->responseTime++;
    }elseif($this->getState() == 'cpu') {
      $this->history[$this->tick] = 'C';
      $this->turnaroundTime++;
      $status = $this->updateBurstStack();
    }elseif($this->getState() == 'io') {
      $this->history[$this->tick] = 'I';
      $this->turnaroundTime++;
      $status = $this->updateBurstStack();
    }elseif($this->getState() == 'wait') {
      $this->history[$this->tick] = 'W';
      $this->turnaroundTime++;
      $this->waitTime++;
    }elseif($this->getState() == 'finished') {
      $this->history[$this->tick] = 'F';
    }
    //$this->printStatus();
    
    // Post update
    if (isset($status)) {
      if($status == -1)
        $this->setState('finished');
      elseif($status == 0 && $this->state == 'cpu')
        $this->setState('io');
      elseif($status == 0 && $this->state == 'io')
        $this->setState('wait');
    }
    $this->tick++;
    return $this->tick - 1;
  }
  
  public function printStatus ($format="full") {
    switch ($format) {
      case 'short':
        echo "Process P$this->process_id (Priority: ".$this->getPriority().")".
        	 " Next Burst: $this->currentBurst".
             " Wait Total: $this->waitTime<br />";  
      break;
      case 'line':
        echo "<p>P$this->process_id ($this->state): ".
      		 "Tr: $this->responseTime Twait: $this->waitTime Ttar: $this->turnaroundTime <br />";
        
      break;  
      case 'full':
        echo "<p>P$this->process_id ($this->state): At: $this->arrivalTime CurrBurst: $this->currentBurst ".
             "Rt: $this->responseTime Wt: $this->waitTime Tt: $this->turnaroundTime ";
        echo '<br /> Burst Stack: ';
        print_r($this->burstStack);
        echo '</p>';
        break;
      default:
        trigger_error("Invalid format. Please use 'full', 'short', or 'line'.",E_USER_WARNING);
        printStatus();
        break;
    }
  }
  
  public function printHistory() {  
    printf('P%-5u',$this->getID());
    foreach ($this->history as $action) {
      printf('%4s',$action);
    }
  }

}
?>
