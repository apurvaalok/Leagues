<?php

$ch = curl_init(); 

// set url 
curl_setopt($ch, CURLOPT_URL, "http://www.development.squadcloud.com/schedule/json.getvenueevents.php?duration=5&userid=820&stadium=34&field=6&startdate=2014-01-13&enddate=2014-01-16"); 

//return the transfer as a string 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

// $output contains the output string 
$output = curl_exec($ch); 

//contains array of all free blockids        
$scheduleBlocks = json_decode($output);


        
$teams = array('100', '200', '300', '400', '500', '600', '700', '800' );

// Will give a round robin pairing of matches
// if odd number of teams, each round of matches will have one team which gets a 
// bye
// Eg: If there are n teams where n is even:
// It will give you a list of matches with round numbers as an index of schedule
// The values will contain a pairings of matches for that particular round 


function round_robin($teams)
{
    $number_of_teams = count($teams);
    if ($number_of_teams % 2)
    {
        $teams[] = null;
    }
    $number_of_teams = count($teams);
    $sets = $number_of_teams - 1;
    $half = $number_of_teams / 2;
    $schedule = array();

    foreach (range(0, $sets - 1) as $i)
    {
        $pairings = array();
        foreach (range(0, $half - 1) as $index)
        {
            $arr = array($teams[$index], $teams[$number_of_teams - $index - 1]);   
            $pairings[] = $arr;
        } 
        $schedule[] = $pairings;
        $val = array_pop($teams);
        array_splice($teams, 1 , 0, $val);
        // push value at index 1
        
        
    }
    return $schedule;
}

print_r(round_robin($teams));


echo "<br>****************************<br>";

//print schedule
print_r($scheduleBlocks);



?>