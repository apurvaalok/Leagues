<?php
include('includes/db.php');
include('includes/encryption.php');

	
	$singleVenue = true;
	$singleField = true;
	$userID = 0;
	$stadiumID = 10;
	$field = 1;
	$duration = 5;
	
	

	$encryption = new CEncryption();
	
	$startDate = (isset($_GET['startdate'])) ? $_GET['startdate'] : '';
	$endDate = (isset($_GET['enddate'])) ? $_GET['enddate'] : '';
	
	if(isset($_GET['userid'])) {
		//$userID = $encryption->decode($_GET['userid']);
		$userID = $_GET['userid'];
		if ($userID <= 0)
			return null;
	}
	else
		return null;
	
	
	if(isset($_GET['stadium'])) {
		$stadiumID = $_GET['stadium'];
		if ($stadiumID > 0)
			$singleVenue = true;
	}
	
	if(isset($_GET['field'])) {
		$field = $_GET['field'];
		if ($field > 0)
			$singleField = true;
	}
	
	if(isset($_GET['duration'])) {
		$duration = $_GET['duration'];
	}


	$year = date('Y');
	$month = date('m');
	
	$startHour = 8;
	$endHour = 18;
	
	//game length 
	$length = 60;

	
	if ($singleVenue) {
	
		if ($singleField) {
			
			
			$events = getVenueScheduleEvents($userID, $stadiumID, $field, $startDate, $endDate);
			
			$usedBlocks = eventsToBlockIds($events, $startDate, $duration);	
			
			$freeBlocks = getScheduleFreeBlockIds($startDate, $endDate, $duration);
			
			//remove used blocks
			$freeBlocks = array_diff($freeBlocks, $usedBlocks);

     		$start_date = strtotime($startDate);
     		
     		$end_date = strtotime($endDate);
     		
     		$datediff = $end_date - $start_date;
     		
     		$totalDays = floor($datediff/(60*60*24));
     		
     		
			//remove business off hours - morning & afternoon
			
     		for ($i = 0; $i <= $totalDays; $i++)
     		{
     			$startDiff = $i*24 + $startHour;
     	
     			$freeBlocks = array_diff($freeBlocks, range($duration*12*$i*24, convertTimeToBlockId(date('Y-m-d H:i:s', strtotime($startDate . ' + '.$startDiff.' Hours')), $startDate), $duration));
			
				$endDiff = $i*24 + $endHour;
     	
     			$freeBlocks = array_diff($freeBlocks, range(convertTimeToBlockId(date('Y-m-d H:i:s', strtotime($startDate . ' + '.$endDiff.' Hours')), $startDate), $duration*12*($i+1)*24, $duration));
			}			
			
			$finalBlockIds = array();			

			foreach ($freeBlocks as $blockId)
			{
				$finalBlockIds[] = (int)$blockId;
			}
			
			echo json_encode($finalBlockIds);
			//var_dump($finalBlockIds);
			//var_dump(calculateParentBlockIds($finalBlockIds, $duration, $length));
			
			
		}
		
		else 
		{
		
			$results = array();
			$i = 0;
			
			foreach($ids as $id) {
				$temp = array();
				$temp = getVenueScheduleEvents($id,$names[$i]);
				$results = array_merge($results,$temp);
				$i++;
			}
			
			echo json_encode($results);
		}
	}
	else  {
		
		if ($singleField) {
			$temp = explode("::",$venueInfo);
			$id = $temp[0];
			$name = $temp[1];
			$events = getVenueScheduleEvents($userID, $id,$name,$startDate,$endDate);
			
			echo json_encode($events);
			
			
			//$blockIds = eventsToBlockIds($events, $startDate, $duration);
			
			//echo json_encode(getScheduleFreeBlockIds($startDate, $endDate, '8', '17', $blockIds, $duration));
		}
		
	}
	
	function getScheduleFreeBlockIds($startDate, $endDate, $duration)
	{
		
		return range(0, convertTimeToBlockId($endDate, $startDate), $duration);

	}
	
	function convertTimeToBlockId($date, $refDate)
	{
		
		return round(abs(strtotime($date) - strtotime($refDate)) /60,2);
	}
	
	function calculateParentBlockIds($blockIds, $duration, $length)
	{
	
		$start = NULL;
		$last = NULL;
		
	
		$total = count($blockIds);
		
	
		$finalIds = array();
		
		$parent = $blockIds[0];
		
		var_dump($blockIds);
		
		for ($i = 0; $i < $total; $i++)
		{
			
			$start = $parent;
			
			if (isset($blockIds[$i + 1]))
			{
			
				if (($blockIds[$i + 1] - $start) == $duration)
				{
					
					while (($blockIds[$i + 1] - $start) == $duration)
					{
						$start = $blockIds[$i + 1];
						$i++;
						var_dump('in');
					}
					$finalIds[] = $blockIds[$i];
				}
				else
				{
					$finalIds[] = $parent;
					$parent = $blockIds[$i + 1];
				}
			}
			else
				return $finalIds;
		}
	}
	
	function eventsToBlockIds($events, $startDate, $duration)
	{
		$start = NULL;
		$end = NULL;
		
		$blockIds = array();
		foreach($events as $event)
		{
		
			
			$start = round(abs(strtotime($event['start']) - strtotime($startDate)) /60,2);
			
			$end = round(abs(strtotime($event['end']) - strtotime($startDate)) /60,2);
			
			if (!$end || $end < $start)
			{
				$end = $start + $duration;
			}
			
			for ($i = $start; $i <= $end; $i += $duration)
			{
				$blockIds[] = $i;
			}
			
		}
		
		return $blockIds;
	
	}
	
	function convertBlockIdsToEvents($blockIds, $startDate, $blockLength)
	{
	
	
	}
	
	
	function getVenueScheduleEvents($userID, $venueID, $field, $startDate = null, $endDate = null)
	{
		$db = new Database; 
		$events = array();
		//check user permission
		$Query = "SELECT admin FROM zyjcn_joomleague_playground WHERE id = '".$venueID."'"; 
		
		$db->query($Query);     
		
			
		$db->singleRecord();
		
		if ($db->Record['admin'] == $userID && $field > 0)
		{
			$Query = "SELECT events.event_id, events.title, events.start_date, events.end_date "
					." FROM zyjcn_fields_schedule_events AS events"
					." INNER JOIN zyjcn_venue_fields AS fields ON fields.id = ".$field
					." WHERE fields.venue_id = ".$venueID." AND events.field_id = fields.id "
					." AND events.published = '1'"; 
			
			if ($startDate != '' && $endDate != '') {
				$Query .= " AND start_date  > '".$startDate."' AND end_date < '".$endDate."'";
			}
			
			$Query .= " ORDER BY start_date ASC ";
			
			$db->query($Query);        // query the database 
			
			
			$db->singleRecord();
			
			
			
			if (!empty($db->Record['event_id']))
				$events[] = array('id' => $db->Record['event_id'], 'title' => $db->Record['title'], 'start' => $db->Record['start_date'], 'end' => $db->Record['end_date'], 'allDay' => false, 'color' => '#36C' , 'editable' => true); 
			else
				return $events;
				
			while ($db->nextRecord())  
    		{ 
    			$events[] = array('id' => $db->Record['event_id'], 'title' => $db->Record['title'], 'start' => $db->Record['start_date'], 'end' => $db->Record['end_date'], 'allDay' => false, 'color' => '#36C' , 'editable' => true); 
    		} // end while loop going through whole recordset 
			
			return $events;

		}
		return $events;
	
	}
	
	

?>
