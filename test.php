<?php 


$testagent = array(
    'mbox' => 'mailto:example.learner@adlnet.gov',
  );

$testagent = json_encode($testagent);
//echo $testagent;


// http://localhost:8888/ll/learninglocker/public/data/xAPI/statements?agent={"mbox":"mailto:teacher.1@gmail.com"}

// http://localhost:8888/ll/learninglocker/public/data/xAPI/statements?agent={"mbox":"mailto:teacher.1@gmail.com"}&verb=http://activitystrea.ms/schema/1.0/create


//päeva genereerimine

// teeme 20 enrolli ja loodame parimat



$statements = array();
for($i=0;$i<20;$i++){
	$day = rand(1,31);
	if($day<10){
		$day = '0'.$day;
	}
	$name = 'Test Student '.$i;
	$mbox = $i.'.student@test.ee';
	$statements[] = array(
	'actor' => array(
		'name' => $name,
		'mbox' => 'mailto:'.$mbox,
		'objectType' => 'Agent',
	),
	'verb' => array(
		'id' => 'http://activitystrea.ms/schema/1.0/leave',
		'display' => array(
			'en-US' => 'left',
		),
	),
	'object' => array(
		'id' => 'http://www.kursused.jee/courses.php?cor=2',
		'objectType' => 'Activity',
		'definition' => array(
			'type' => 'http://adlnet.gov/expapi/activities/course',
			'name' => array(
				'en-GB' => 'Example course 2'
			),
		),
	),
	'timestamp' => '2014-10-'.$day.'T08:19:01.850000+00:00'
);	
}
$headers = array(
	'X-Experience-API-Version: 1.0.1',
	'Content-Type: application/json',
);

foreach($statements as $statement){
	$json = json_encode($statement);
	$curl = curl_init();
	  curl_setopt($curl, CURLOPT_URL, 'http://localhost:8888/ll/learninglocker/public/data/xAPI/statements');
	  curl_setopt($curl, CURLOPT_USERPWD, 'a5c960f66ebb0013e1152504801b70770e342580:41100a94622766b876e918d87c316d34ebbf3f7b');
	  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
	  curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
	  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	  
	  $data = curl_exec($curl);	  
	  curl_close($curl);
	  error_log(print_r($data, true));
}
?>