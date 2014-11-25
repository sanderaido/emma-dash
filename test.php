<?php 


// $testagent = array(
//     'mbox' => 'mailto:example.learner@adlnet.gov',
//   );

// $testagent = json_encode($testagent);
//echo $testagent;


// http://localhost:8888/ll/learninglocker/public/data/xAPI/statements?agent={"mbox":"mailto:teacher.1@gmail.com"}

// http://localhost:8888/ll/learninglocker/public/data/xAPI/statements?agent={"mbox":"mailto:teacher.1@gmail.com"}&verb=http://activitystrea.ms/schema/1.0/create


//päeva genereerimine

// teeme 20 enrolli ja loodame parimat



//$statements = array();
// for($i=0;$i<20;$i++){
// 	$day = rand(1,31);
// 	if($day<10){
// 		$day = '0'.$day;
// 	}
// 	$name = 'Test Student '.$i;
// 	$mbox = $i.'.student@test.ee';
// 	$statements[] = array(
// 	'actor' => array(
// 		'name' => $name,
// 		'mbox' => 'mailto:'.$mbox,
// 		'objectType' => 'Agent',
// 	),
// 	'verb' => array(
// 		'id' => 'http://activitystrea.ms/schema/1.0/leave',
// 		'display' => array(
// 			'en-US' => 'left',
// 		),
// 	),
// 	'object' => array(
// 		'id' => 'http://www.kursused.jee/courses.php?cor=2',
// 		'objectType' => 'Activity',
// 		'definition' => array(
// 			'type' => 'http://adlnet.gov/expapi/activities/course',
// 			'name' => array(
// 				'en-GB' => 'Example course 2'
// 			),
// 		),
// 	),
// 	'timestamp' => '2014-10-'.$day.'T08:19:01.850000+00:00'
// );	
// }
// $headers = array(
// 	'X-Experience-API-Version: 1.0.1',
// 	'Content-Type: application/json',
// );

// foreach($statements as $statement){
// 	$json = json_encode($statement);
// 	$curl = curl_init();
// 	  curl_setopt($curl, CURLOPT_URL, 'http://localhost:8888/ll/learninglocker/public/data/xAPI/statements');
// 	  curl_setopt($curl, CURLOPT_USERPWD, 'a5c960f66ebb0013e1152504801b70770e342580:41100a94622766b876e918d87c316d34ebbf3f7b');
// 	  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
// 	  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
// 	  curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
// 	  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	  
// 	  $data = curl_exec($curl);	  
// 	  curl_close($curl);
// 	  error_log(print_r($data, true));
// }


// for($i=0;$i<200;$i++){
// 	$day = rand(1,31);
// 	if($day<10){
// 		$day = '0'.$day;
// 	}
// 	$studentnr = rand(17,19);
// 	$pagenr = rand(1,10);
// 	$name = 'Test Student '.$studentnr;
// 	$mbox = $studentnr.'.student@test.ee';
// 	$statements[] = array(
// 		'actor' => array(
// 			'name' => $name,
// 			'mbox' => 'mailto:'.$mbox,
// 			'objectType' => 'Agent',
// 		),
// 		'verb' => array(
// 			'id' => 'http://activitystrea.ms/schema/1.0/visited',
// 			'display' => array(
// 				'en-US' => 'visited',
// 			),
// 		),
// 		'object' => array(
// 			'objectType' => 'Activity',
// 			'id' => 'http://www.internal.link/page.php?pgnr='.$pagenr,
// 			'definition' => array(
// 				'name' => array(
// 					'en-GB' => 'Resource '.$pagenr,
// 				),
// 			),
// 		),
// 		'context' => array(
// 			'contextActivities' => array(
// 				'parent' => array(
// 					'id' => 'http://www.kursused.jee/courses.php?cor=2',
// 					'objectType' => 'Activity',
// 				),
// 				'grouping' => array(
// 					'id' => 'http://www.kursused.jee/courses.php?cor=2',
// 					'objectType' => 'Activity',
// 				),
// 			),
// 		),
// 		'timestamp' => '2014-10-'.$day.'T08:19:01.850000+00:00',
// 	);
// }
// echo '<pre>';
// print_r($statements);
// echo '</pre>';

// $headers = array(
// 	'X-Experience-API-Version: 1.0.1',
// 	'Content-Type: application/json',
// );

// foreach($statements as $statement){
// 	$json = json_encode($statement);
// 	$curl = curl_init();
// 	  curl_setopt($curl, CURLOPT_URL, 'http://localhost:8888/ll/learninglocker/public/data/xAPI/statements');
// 	  curl_setopt($curl, CURLOPT_USERPWD, 'a5c960f66ebb0013e1152504801b70770e342580:41100a94622766b876e918d87c316d34ebbf3f7b');
// 	  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
// 	  curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
// 	  curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
// 	  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	  
// 	  $data = curl_exec($curl);	  
// 	  curl_close($curl);
// 	  error_log(print_r($data, true));
// }
function super_unique($array)
{
  $result = array_map("unserialize", array_unique(array_map("serialize", $array)));

  foreach ($result as $key => $value)
  {
    if ( is_array($value) )
    {
      $result[$key] = super_unique($value);
    }
  }

  return $result;
}

require_once('config.php');
require_once('krumo/class.krumo.php');
// created course(unique courses).
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, ENDPOINT.'?verb=http://activitystrea.ms/schema/1.0/create');
curl_setopt($curl, CURLOPT_USERPWD, USERNAME.':'.PASSWORD);
curl_setopt($curl, CURLOPT_HEADER, XAPIVERSIONHEADER);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
$data = curl_exec($curl);
$data = json_decode($data);
curl_close($curl);

  $courses = array();
  foreach($data->statements as $statement){
    if($statement->object->definition->type == 'http://adlnet.gov/expapi/activities/course'){
      $courses[] = $statement;
    }
  }

  $uniquecourses = array();

foreach($courses as $course){
  if(!array_key_exists($course->object->id, $uniquecourses)){
    $uniquecourses[$course->object->id] = $course;
  }
}



$courses = array();

foreach($uniquecourses as $uniquecourse){
	$courses[] = array(
		'course_name' => $uniquecourse->object->definition->name->{'en-GB'},
		'course_url' => $uniquecourse->object->id,
		'enrolled' => array(),
		'unenrolled' => array(), // Actually quite tricky
		'observers' => array(),	// Only action is pagevisit
		'actives' => array(),	
		'drop_ins' => array(),	// 2 weeks (think about it).
		'all_students' => array(),
	);
}


$coursedata = array();
$tempdata = array();
foreach($courses as &$course){
	$tempdata = array();
	$students = array();
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, ENDPOINT.'?activity='.$course['course_url'].'&related_activities=true');
	curl_setopt($curl, CURLOPT_USERPWD, USERNAME.':'.PASSWORD);
	curl_setopt($curl, CURLOPT_HEADER, XAPIVERSIONHEADER);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	$data = curl_exec($curl);
	$data = json_decode($data);
	curl_close($curl);
	$statements = $data->statements;
	$statements = super_unique($statements);	
	foreach($statements as $statement){
		$course['all_students'][$statement->actor->mbox][] = $statement;		
	}

	foreach($course['all_students'] as $student){
		$whereTo = whereStudentBelongs($student);
		if($whereTo){
			switch($whereTo[0]){
				case 'enrolled':

				break;
				case 'unenrolled':
					array_push($course['unenrolled'], $whereTo[1]);
				break;
				case 'observers':

				break;
				case 'actives':

				break;
				case 'drop_ins':

				break;
			}
		}
	}
	krumo($course);

}






function whereStudentBelongs($studentstatements){
	// SIIN PEAKS NÜÜD SIIS KLASTERDAMINE TOIMUMA!!	
	$totalstatements = count($studentstatements);
	$student = $studentstatements[0]->actor->mbox;
	$countjoin = 0;
	$countleave = 0;
	foreach($studentstatements as $statement){
		switch($statement->verb->id){
			case 'http://activitystrea.ms/schema/1.0/leave':
				$countleave++;
			break;
			case 'http://activitystrea.ms/schema/1.0/join':
				$countjoin++;
			break;
		}
	}
	if($countjoin>0){
		if($countjoin == $countleave){
			return array('unenrolled', $student);
		}else{
			return false;
		}
	}
}












?>