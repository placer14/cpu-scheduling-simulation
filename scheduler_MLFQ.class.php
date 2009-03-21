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

class MLFQ_Scheduler {
  private $tick;
  private $procQueue;
  private $procPool;
  private $procOnCPU;
  private $ticksOnCPU;
  private $timeQ;
    
  public function __construct() {
    $this->tick = 0;
    $this->cpuInUse = false;
    $this->procQueue = array(0=>array(),1=>array(),2=>array());
    $this->procPool = array();
    $this->timeQ = -1;
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
          array_push($this->procQueue[$proc->getPriority()],$proc->getID());
          $proc->setState('ready');
        break;
        case 'cpu':
          $cpuInUse = $proc_key;
          if ($this->timeQ > 0) {
            $this->timeQ -= 1;
          } else if ($this->timeQ == 0) {
            $this->timeQ = -1;
            $newP = $proc->getPriority() + 1;
            settype($newP,'int');
            echo "New Priority on P".$proc->getID().": $newP <br />";
            $proc->setPriority($newP);
            $proc->setState('wait');
            $cpuInUse = false;
            $this->addToQueue($proc->getID());
          }
        break;
        case 'wait':
          $thisProc = $proc->getID();
          if ($this->findInQueue($proc->getID()) === false) { 
            $this->addToQueue($proc->getID());
          }
        break;
      }
    }
    if ($cpuInUse === false) {
      $this->procOnCPU = $this->getNextProc();
      if ($this->procOnCPU !== false) {
        
        // New process on CPU. Print dynamic execution details.
        echo "=============<br/>";
        echo "Tick #$this->tick<br />";
        echo "=============<br/>";
        echo "New Process (P$this->procOnCPU) on CPU! <br />";
        echo "Priority ".$this->procPool[$this->procOnCPU]->getPriority()." <br />";
        echo "Remaining Burst: ".$this->procPool[$this->procOnCPU]->getCurrentBurst()."<br /><br />";
             $this->printQueue();
        echo "<br/>";
        echo "I/O Pool ProcessNum (Remaining I/O):<br/>";
        foreach ($this->procPool as &$proc) {
          if ($proc->getState() == 'io') {
            echo "P".$proc->getID()." (".$proc->getCurrentBurst().")<br />";
          }
        }
        echo "<p />";

        $procPriority = $this->procPool[$this->procOnCPU]->getPriority();
        switch ($procPriority) {
          case 0:
            $this->timeQ = 6;
            break;
          case 1: 
            $this->timeQ = 13;
            break;
          case 2:
            $this->timeQ = -1;
            break;
        }
        $this->procPool[$this->procOnCPU]->setState('cpu');
        $this->ticksOnCPU++;
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
  
  private function getNextProc() {
    foreach ($this->procQueue as &$procs) {
      $nextProc = array_shift($procs);
      if ($nextProc !== NULL) {
        return $nextProc;
      }
    }
    return false;
  }
  
  static private function sortSJF($a, $b) {
    if ($a['burst'] == $b['burst']) {
        return 0;
    }
    return ($a['burst'] < $b['burst']) ? -1 : 1;
  }
  
  private function addToQueue($procID) {
    $oldProcPriority = $this->procPool[$this->procOnCPU]->getPriority();
    $procPriority = $this->procPool[$procID]->getPriority();
    /*
    if ($this->procOnCPU !== false && $procPriority < $oldProcPriority)
    {
      // Move old process off the CPU and back into Queue
      $oldProc = $this->procPool[$this->procOnCPU]->getID();
      $this->procPool[$oldProc]->setState('wait');
      array_unshift($this->procQueue[$oldProcPriority],$oldProc);
      
      // Move preempting process directly onto CPU
      $this->procOnCPU = $procID;
      $this->procPool[$this->procOnCPU]->setState('cpu');
      
     // New process on CPU. Print dynamic execution details.
      echo "INTERRUPTED!<br />";
      echo "=============<br/>";
      echo "Tick #$this->tick<br />";
      echo "=============<br/>";
      echo "New Process (P$this->procOnCPU) on CPU! <br />";
      echo "Priority ".$this->procPool[$this->procOnCPU]->getPriority()." <br />";
      echo "Remaining Burst: ".$this->procPool[$this->procOnCPU]->getCurrentBurst()."<br /><br />";
           $this->printQueue();
      echo "<br/>";
      echo "I/O Pool ProcessNum (Remaining I/O):<br/>";
      foreach ($this->procPool as &$proc) {
        if ($proc->getState() == 'io') {
          echo "P".$proc->getID()." (".$proc->getCurrentBurst().")<br />";
        }
      }
      echo "<p />";
      return;
    } */
    switch ($procPriority) {
      case 0:
        array_push($this->procQueue[0],$this->procPool[$procID]->getID());
        break;
      case 1:
        array_push($this->procQueue[1],$this->procPool[$procID]->getID());
        break;
      case 2:
        array_push($this->procQueue[2],$this->procPool[$procID]->getID());
        $spareQueue = array();
        $procBurst = -1;
        $lastBurst = -1;
        foreach ($this->procQueue[2] as $procB) {
          $procBurst = $this->procPool[$procB]->getCurrentBurst();
          $spareQueue[] = array('procID' => $procB, 'burst' => $procBurst);
        }
        usort($spareQueue,array("MLFQ_Scheduler","sortSJF"));
        $this->procQueue[2] = array();
        foreach ($spareQueue as $procA) {
          array_push($this->procQueue[2],$procA['procID']);
        }
        unset($spareQueue);
        break;
    }  
    return;
  }
  
  private function findInQueue($procID) {
    $procInQueue = false;
    foreach ($this->procQueue as $qLvl=>$qProcs) {
      if (array_search($procID,$qProcs) !== false) {
        $procInQueue = true;
      }
    }
    return $procInQueue;
  }
  
  public function printQueue() {
    echo "Ready Queue:<br />";
    foreach ($this->procQueue as $priority=>$procLvl) {
      foreach ($procLvl as $proc) {
        $this->procPool[$proc]->printStatus('short');  
      }
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
