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
  *  public function __construct()
  *     Constructor function.  
  *  public function addProc (Process &$proc) 
  *     Adds a process to the scheduler. Sets the process ID to the next integer
  *       $proc is a Process instance.  
  *  public function tick()
  *     The primary logic of the CPU. Iterates through the added processes and
  *     simulates the actions the CPU might take according to a First-Come-
  *     First-Serve schema. Each execution of this function simulates one tick.
  *  public function printQueue()
  *     Outputs the Ready Queue of the CPU.  
  *  public function printHistory()
  *     Outputs the history of each of the processes managed by the system.    
  *         
  */  

require_once('process.class.php');

class Scheduler {
  private $tick;
  private $procQueue;
  private $procPool;
  private $procOnCPU;
  private $ticksOnCPU;
    
  public function __construct() {
    $this->tick = 0;
    $this->cpuInUse = false;
    $this->procQueue = array();
    $this->procPool = array();
  }
    
  private function processPool () {
    $cpuInUse = false;
    $procUnsubmitted = false;
    foreach ($this->procPool as $proc_key => &$proc) {
      $procState = $proc->getState();
      switch ($procState) {
        case 'unsubmitted':
          $procUnsubmitted = true;
        break;
        case 'new':
          array_push($this->procQueue,$proc->getID());
          $proc->setState('ready');
        break;
        case 'cpu':
          $cpuInUse = $proc_key;
        break;
        case 'wait':
          $thisProc = $proc->getID();
          if (array_search($thisProc,$this->procQueue) === false ) {
            array_push($this->procQueue,$proc->getID());
          }
        break;
      }
    }
    if ($cpuInUse === false) {
      $nextProc = array_shift($this->procQueue);
      if ($nextProc != NULL || $nextProc === 0) {
        $this->procOnCPU = $nextProc;
        
        // New process on CPU. Print dynamic execution details.
        echo "=============<br/>";
        echo "Tick #$this->tick<br />";
        echo "=============<br/>";
        echo "New Process (P$this->procOnCPU) on CPU! <br />";
        echo "Remaining Burst: ".$this->procPool[$nextProc]->getCurrentBurst()."<br /><br />";
             $this->printQueue();
        echo "<br/>";
        echo "I/O Pool ProcessNum (Remaining I/O):<br/>";
        foreach ($this->procPool as &$proc) {
          if ($proc->getState() == 'io') {
            echo "P".$proc->getID()." (".$proc->getCurrentBurst().")<br />";
          }
        }
        echo "<p />";
             
        $this->procPool[$nextProc]->setState('cpu');
      } else {
        // No process to put on CPU.
      }
    } else {
      $this->ticksOnCPU++;
    }
  }
  
  public function addProc (Process &$proc) {
    $arrayNum = array_push($this->procPool,$proc);
    $proc->setID(($arrayNum - 1));
    return $arrayNum;  
  }
  
  public function tick() {
    // Prepare for tick
    $this->processPool();
        
    // During tick
    $continueSim = false;
    foreach ($this->procPool as &$proc) {
      $procTick = $proc->updateProc();
      if ($procTick != $this->tick) {
        trigger_error("Process $proc->getID() is not synched. ProcTick: $procTick SchedulerTick: $this->tick",E_USER_NOTICE);
      }
      $procState = $proc->getState();
      if ($procState !== 'finished') {
        $continueSim = true;
      }
    }
    
    // Post tick
    $this->tick++;
    return $continueSim;
  }
  
  public function printQueue() {
    echo "Ready Queue:<br />";
    foreach ($this->procQueue as $proc) {
      $this->procPool[$proc]->printStatus('short');
    }
  }
  
  public function printHistory() {
    echo "<pre>Tick  ";
    for($i=0;$i<$this->tick;$i++) {
      printf('%4u',$i);
    }
    echo " |<br />";
    foreach ($this->procPool as &$proc) {
      $proc->printHistory();
      echo " |<br />";
    }
    echo "</pre>";
    printf("Took %d ticks to complete everything.<br /> CPU Utilization was %01.2f%%.<br />",$this->tick,($this->ticksOnCPU/$this->tick*100));
    $i=0;
    $TtarAvg=0;
    $TrAvg=0;
    $TwaitAvg=0;
    foreach ($this->procPool as &$proc) {
      $i++;
      $proc->printStatus('line');
      $stats = $proc->getStats();
      $TrAvg += $stats['responseTime'];
      $TtarAvg += $stats['turnaroundTime'];
      $TwaitAvg += $stats['waitTime'];
    }
    printf("Avg Tr: %2.2f Avg Twait: %2.2f Avg Ttar: %2.2f<br />",($TrAvg/$i),($TwaitAvg/$i),($TtarAvg/$i));
  }
  
}
?>
