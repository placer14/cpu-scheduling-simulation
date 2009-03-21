<?php
  
  require_once('scheduler.class.php');
  require_once('scheduler_MLFQ.class.php');
  
  function error_handler ($type, $msg, $file, $line) {
    echo "<p>Error in $file [$line]<br />";
    echo "Message: $msg<br /></p>";
  }
  
  set_error_handler('error_handler');
  if (isset($_GET['v'])) {
    if ($_GET['v'] == 'fcfs') {
       $cpu = new Scheduler;
    } elseif ($_GET['v'] == 'mlfb') {
      $cpu = new MLFQ_Scheduler;
    }
    $p1Bursts = array(17,52,16,61,15,53,16,52,17,64,18,78,16,71,17);
    $p2Bursts = array(5,44,6,42,4,38,6,41,5,45,5,44,6);
    $p3Bursts = array(8,24,6,31,7,26,10,24,11,27,9,28,7);
    $p4Bursts = array(11,21,12,20,13,20,11,19,12,17,10,18,12);
    $p5Bursts = array(7,25,8,15,7,16,9,14,7,14,8,16,9,18,8);
    $p6Bursts = array(8,14,5,12,6,10,3,11,7,10,6,11,5,10,6,12,7);
    $p7Bursts = array(21,33,17,31,19,32,17,33,20,31,18,32,19,33,17);
     
    $p1 = new Process($p1Bursts);
    $p2 = new Process($p2Bursts);
    $p3 = new Process($p3Bursts);
    $p4 = new Process($p4Bursts);
    $p5 = new Process($p5Bursts);
    $p6 = new Process($p6Bursts);
    $p7 = new Process($p7Bursts); 
    
    echo '<div style="font-family:monospace;">';
  
    $cpu->addProc($p1);
    $cpu->addProc($p2);
    $cpu->addProc($p3);
    $cpu->addProc($p4);
    $cpu->addProc($p5);
    $cpu->addProc($p6);
    $cpu->addProc($p7);
    $continueSim = true;
    
    while($cpu->tick()) { //tick() returns false when complete
    }
      
    $cpu->printHistory(); 
    echo '</div>';
  } else {
  ?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=windows-1250">
  <title>cpuScheduler // nobulb</title>
  </head>
  <body>
<div style="text-align:center;">
<h1>cpuScheduler</h1>
 Would you like to run the simulation for <a href="?v=fcfs">FCFS</a> or for <a href="?v=mlfb">Multilevel Feedback</a>?
</div>
<div style="text-align:center;">You might also <a href="CPU_Scheduler_Simulation_Report.pdf">read my report</a> which discusses this simulator in better detail.</div>
<?php 
  }
?>
<div class="footer" style="margin-top: 100px; text-align:center; font-size:0.6em; color:#666;">
<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/us/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-sa/3.0/us/88x31.png" /></a>
  <br />
  <span xmlns:dc="http://purl.org/dc/elements/1.1/" href="http://purl.org/dc/dcmitype/Text" property="dc:title" rel="dc:type">cpuScheduler</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="https://nobulb.com" property="cc:attributionName" rel="cc:attributionURL">Michael Greenberg</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/us/">Creative Commons Attribution-Noncommercial-Share Alike 3.0 United States License</a>.
  <br />Based on a work at <a xmlns:dc="http://purl.org/dc/elements/1.1/" href="https://nobulb.com/" rel="dc:source">nobulb.com</a>.
</div>

  </body>
</html>
