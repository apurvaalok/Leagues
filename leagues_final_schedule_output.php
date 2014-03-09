<?php

define('IBREATHE_EXEC', 1);

require_once('leagueschedule.php');

//initialize here

$leagueScheduling = new CLeagueScheduling;

$matchUps = $leagueScheduling->roundRobin();

$leagueScheduling->fetchFieldScheduleData();
	
$scheduleData = $leagueScheduling->calculateFieldSchedules();

$f = $leagueScheduling->finalLeagueSchedule();

$fieldNames = array(20 => 'Soccer A', 21 => 'Soccer B');

?>

<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> 
<meta charset="utf-8"> 
<title>League Scheduling</title> 
<meta name="description" content="League Scheduling">
<link href="http://www.w3resource.com/twitter-bootstrap/twitter-bootstrap-v2/docs/assets/css/bootstrap.css" rel="stylesheet"> 
<style type="text/css"></style></head>
<body>
<table class="table table-striped">
        <thead>
          <tr>
            <th>Date/Time</th>
            <th>Field Id</th>
            <th>Home Team</th>
            <th>Away Team</th>
          </tr>
        </thead>
        <tbody>
        
        <?php $counter = 0;
        
        	foreach($f as $e) : ?>
        
          <tr>
            <td><?php echo $leagueScheduling->_convertBlockIdToTime($e["when"]); ?></td>
            <td><?php echo $fieldNames[$e["where"]]; ?></td>
            <td><?php echo $e["home"]; ?></td>
            <td><?php echo $e["away"]; ?></td>
          </tr>
          
        <?php endforeach; ?>
         
        </tbody>
      </table>

</body></html>
