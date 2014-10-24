<?php

require_once('config.php');


$params = $_GET;

$requestType = $params['type'];
switch($requestType){
	case('enrollmentactivity'):
		$verb = 'http://activitystrea.ms/schema/1.0/join';
		$monthandyear = explode('-', $params['date']);
		$activity = $params['activity'];
		$daysinmonth = cal_days_in_month(CAL_GREGORIAN, $monthandyear[0], $monthandyear[1]);
		
		$urlparams = setCurlParams($verb, $monthandyear, $activity, $daysinmonth);

		$enrollments = getData($urlparams);
		$enrollmentdates = array();
		foreach($enrollments as $enrollment){
			array_push($enrollmentdates, substr($enrollment->timestamp, 0, 10));
		}

		$verb = 'http://activitystrea.ms/schema/1.0/leave';
		$urlparams = setCurlParams($verb, $monthandyear, $activity, $daysinmonth);
		$unenrollments = getData($urlparams);

		$unenrolllmentdates = array();
		foreach($unenrollments as $unenrollment){
			array_push($unenrolllmentdates, substr($unenrollment->timestamp, 0, 10));
		}

		$categories = array();
		foreach(range(1, $daysinmonth) as $daynumber){
			if($daynumber<10){
				array_push($categories, array('date' => $monthandyear[1].'-'.$monthandyear[0].'-0'.($daynumber)));	
			}else{
				array_push($categories, array('date' => $monthandyear[1].'-'.$monthandyear[0].'-'.($daynumber)));
			}
			$categories[$daynumber-1]['enrollments'] = 0;
			$categories[$daynumber-1]['unenrollments'] = 0;
		}

		$enrollmentcounts = array_count_values($enrollmentdates);
		$unenrollmentcounts = array_count_values($unenrolllmentdates);

		foreach($categories as &$category){
			if(array_key_exists($category['date'], $enrollmentcounts)){
				$category['enrollments'] = $enrollmentcounts[$category['date']];
				
			}
			if(array_key_exists($category['date'], $unenrollmentcounts)){
				$category['unenrollments'] = $unenrollmentcounts[$category['date']];				
			}
		}
		echo json_encode($categories);

	break;
}

function setCurlParams($verb, $monthandyear, $activity, $daysinmonth){
	$since = date(DATE_ATOM, mktime(0,0,0,$monthandyear[0], 1,$monthandyear[1]));
	$until = date(DATE_ATOM, mktime(23,59,59,$monthandyear[0], $daysinmonth,$monthandyear[1]));		
	$params = array(
		'verb' => $verb,
		'activity' => $activity,
		'since' => $since,
		'until' => $until,
	);
	$urlparams = http_build_query($params);
	return $urlparams;
}

function getData($urlparams){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, ENDPOINT.'?'.$urlparams);
	curl_setopt($curl, CURLOPT_USERPWD, USERNAME.':'.PASSWORD);
	curl_setopt($curl, CURLOPT_HEADER, XAPIVERSIONHEADER);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	$response = curl_exec($curl);

	$response = json_decode($response);
	$data = $response->statements;
	curl_close($curl);
	return $data;
}


?>
