<?php

require_once('config.php');


$params = $_GET;

$requestType = $params['type'];
switch($requestType){
	case('enrollmentactivity'):

		fetchEnrollmentActivityData($params);

	break;
	case('relatedViewsStudent'):

		fetchStudentRelatedViewsData($params);

	break;
}
function fetchStudentRelatedViewsData($params){
	//error_log(print_r($params, true));

	$start = explode('-', $params['start']);
	$until = explode('-', $params['end']);
	$course = $params['activity'];


	$startdate = date(DATE_ATOM, strtotime('last monday', mktime(0,0,0, $start[1], $start[0], $start[2])));
	$untildate = date(DATE_ATOM, strtotime('next sunday', mktime(23,59,59,$until[1], $until[0], $until[2])));
	

	//$weeks = 1+date('W', strtotime($untildate)) - date('W', strtotime($startdate));


	$urlparams = setAllCourseDataParams($startdate, $untildate, $course);

	$coursedata = getData($urlparams);
	$createcourse = array();
	//$externalmaterials = array();
	//$internalmaterials = array();
	$mypagevisits = array();
	$pagevisits = array();
	$helper = array();
	foreach($coursedata as $statement){
		if($statement->verb->id == 'http://activitystrea.ms/schema/1.0/visited'){
			if(strtotime($statement->timestamp)>strtotime($startdate) && strtotime($statement->timestamp)<strtotime($untildate)){
				$pagevisits[] = $statement->object->id;
				$helper[$statement->object->id] = array('name' => $statement->object->definition->name->{'en-GB'});
				if($statement->actor->mbox == 'mailto:'.$params['agent']){
					$mypagevisits[] = $statement->object->id;
				}
			}
		}
	}

	unset($coursedata);

	
	$myvisitcounts = array_count_values($mypagevisits);
	asort($myvisitcounts);
	$myvisitcounts = array_reverse($myvisitcounts);
	$pagevisitcounts = array_count_values($pagevisits);
	asort($pagevisitcounts);
	$pagevisitcounts = array_reverse($pagevisitcounts);

	$myvisits = array();
	foreach($myvisitcounts as $key => $value){
		$myvisits[] = array(
			'name' => $helper[$key]['name'],
			'url' => $key,
			'count' => $value,
		);
	}
	
	$pagevisits = array();
	foreach($pagevisitcounts as $key => $value){
		$pagevisits[] = array(
			'name' => $helper[$key]['name'],
			'url' => $key,
			'count' => $value,
		);
	}

	if(empty($myvisits) && empty($pagevisits)){
		$result = array(
			'result' => 'empty',
		);
		echo json_encode($result);
	}else{

		$data = array('myvisits' => $myvisits, 'othervisits' => $pagevisits);


		echo json_encode($data);
	}
}

function setAllCourseDataParams($startdate, $untildate, $course){
	$params = array(
		'since' => $startdate,
		'until' => $untildate,
		'verb' => 'http://activitystrea.ms/schema/1.0/create',
		'activity' => $course,
		'related_activities' => 'true',		
	);
	$urlparams = http_build_query($params);
	return $urlparams;
}


function fetchEnrollmentActivityData($params){
	$verb = 'http://activitystrea.ms/schema/1.0/join';
	$monthandyear = explode('-', $params['date']);
	$activity = $params['activity'];
	$daysinmonth = cal_days_in_month(CAL_GREGORIAN, $monthandyear[0], $monthandyear[1]);
	
	$urlparams = setEnrollmentCurlParams($verb, $monthandyear, $activity, $daysinmonth);

	$enrollments = getData($urlparams);
	$enrollmentdates = array();
	foreach($enrollments as $enrollment){
		array_push($enrollmentdates, substr($enrollment->timestamp, 0, 10));
	}

	$verb = 'http://activitystrea.ms/schema/1.0/leave';
	$urlparams = setEnrollmentCurlParams($verb, $monthandyear, $activity, $daysinmonth);
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
	
	if(empty($enrollmentcounts) && empty($unenrollmentcounts)){
		$result = array(
			'result' => 'empty',
		);
		echo json_encode($result);
	}else{
		foreach($categories as &$category){
			if(array_key_exists($category['date'], $enrollmentcounts)){
				$category['enrollments'] = $enrollmentcounts[$category['date']];
				
			}
			if(array_key_exists($category['date'], $unenrollmentcounts)){
				$category['unenrollments'] = $unenrollmentcounts[$category['date']];				
			}
		}
		echo json_encode($categories);
	}
}


function setEnrollmentCurlParams($verb, $monthandyear, $activity, $daysinmonth){
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
