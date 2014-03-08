<?php

class CLeagueScheduling {
	
	var $_adminId;
	
	var $_stadiumId;
	
	var $_fieldIds;
	
	var $_startDate;
	
	var $_endDate;
	
	var $_matchLength;
	
	var $_finalScheduleBlocks;
	
	var $_teams;
	
	public function __construct() {
	

		$this->_finalScheduleBlocks = array();

		$this->_adminId = 820;

		$this->_stadiumId = 34;

		$this->_fieldIds = array(6,7);

		$this->_startDate = '2014-01-12';

		$this->_endDate = '2014-01-21';

		$this->_matchLength = 60;
		
		$this->_teams = array('100', '200', '300', '400', '500', '600', '700', '800' );
		
	}
	
	public function fetchFieldScheduleData() {
	

		foreach($this->_fieldIds as $fieldId) {

			$ch = curl_init(); 

			// set url 
			curl_setopt($ch, CURLOPT_URL, "http://www.development.squadcloud.com/schedule/json.getvenueevents.php?userid=".$this->_adminId."&stadium=".$this->_stadiumId."&field=".$fieldId."&startdate=".$this->_startDate."&enddate=".$this->_endDate); 

			//return the transfer as a string 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

			// $output contains the output string 
			$output = curl_exec($ch); 

			//contains array of all free blockids        
			$scheduleBlocks = json_decode($output);
			
		
	
			$this->_finalScheduleBlocks[$fieldId] = $scheduleBlocks;
			
			
	
		}
		
	}
	
	public function calculateFieldSchedules() {
	
		$finalSchedules = array();
		
		foreach($this->_finalScheduleBlocks as $fieldId => $scheduleBlocks)
		{
		
			$finalSchedules[$fieldId] = $this->_getAvailableSlots($scheduleBlocks, $this->_matchLength);
		}
		
		return $finalSchedules;
	
	
	}

	/** 
	 * Will give a round robin pairing of matches
	 * if odd number of teams, each round of matches will have one team which gets a 
	 * bye
	 * Eg: If there are n teams where n is even:
	 * It will give you a list of matches with round numbers as an index of schedule
	 * The values will contain a pairings of matches for that particular round 
	 */

	public function roundRobin()
	{
		$number_of_teams = count($this->_teams);
		if ($number_of_teams % 2)
		{
			$this->_teams[] = null;
		}
		$number_of_teams = count($this->_teams);
		$sets = $number_of_teams - 1;
		$half = $number_of_teams / 2;
		$schedule = array();

		foreach (range(0, $sets - 1) as $i)
		{
			$pairings = array();
			foreach (range(0, $half - 1) as $index)
			{
				$arr = array($this->_teams[$index], $this->_teams[$number_of_teams - $index - 1]);   
				$pairings[] = $arr;
			} 
			$schedule[] = $pairings;
			$val = array_pop($this->_teams);
			array_splice($this->_teams, 1 , 0, $val);
			// push value at index 1
		
		
		}
		return $schedule;
	}
	
	
	private function _getAvailableSlots($scheduleBlocks, $matchLength)
	{
		if($matchLength % 5 != 0)
		{
			echo "Error: Match length should be a multiple of 5";
			return;
		}

		$streak = 0;
		$prev_element = $scheduleBlocks[0];
		$result_arr = array();
		$expected_streak_length = $matchLength / 5;

		foreach (range(1, count($scheduleBlocks) - 1) as $i)
		{
			$difference = $scheduleBlocks[$i] - $prev_element;
			if ($difference == 5)
			{
				$streak = $streak + 1;
				$prev_element = $scheduleBlocks[$i];
				if ($streak == $expected_streak_length)
				{
				   $result_arr[] = $scheduleBlocks[$i - $expected_streak_length];
				   $streak = 0;
				}

			}
			else
			{
				$streak = 0;
			}

		}
		return $result_arr;

	}
	
}

//initialize here

$leagueScheduling = new CLeagueScheduling;


print_r($leagueScheduling->roundRobin());


echo "<br>****************************<br>";


$leagueScheduling->fetchFieldScheduleData();
	
$scheduleData = $leagueScheduling->calculateFieldSchedules();


print_r($scheduleData);
	


?>
