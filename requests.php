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
	case('materialViewsStudent'):

		fetchStudentMaterialViewsData($params);

	break;

	case('progressStudent'):
		fetchStudentProgressViewData($params);
	break;

	case('overviewTeacher'):
		fetchTeacherOverallProgressData($params);
	break;
}


// http://php.net/manual/en/function.array-unique.php#97285
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

function divide($a, $b){
	if($a==0 || $b==0){
		return 0;
	}else{
		return $a/$b;
	}
}

function getCreateStatements($activity){
	$params = array(
		'verb' => 'http://activitystrea.ms/schema/1.0/create',
		'activity' => $activity,
		'related_activities' => 'true',
	);
	$urlparams = http_build_query($params);

	return getData($urlparams);
}

function getVisitedStatements($unit){

	$params = array(
		'verb' => 'http://activitystrea.ms/schema/1.0/visited',
		'activity' => $unit,
		'related_activities' => 'true',
	);

	$urlparams = http_build_query($params);

	$response = getData($urlparams);
	if($response == 'empty'){
		return 0;
	}else{
		 //error_log(print_r($unit, true));
		// error_log(print_r($response, true));
		return $response;
	}


}

function getAnsweredAssignmentStatements($unit){
	$params = array(
		'verb' => 'http://adlnet.gov/expapi/verbs/answered',
		'activity' => $unit,
		'related_activities' => 'true',
	);

	$urlparams = http_build_query($params);

	$response = getData($urlparams);
	if($response == 'empty'){
		return 0;
	}else{
		return $response;
	}
}

function fetchTeacherOverallProgressData($params){


	//Get create statements
	$createstatements = getCreateStatements($params['activity']);

	$lessonscreated = array();
	$unitscreated = array();
	foreach($createstatements as $createstatement){
		if($createstatement->object->definition->type == 'http://adlnet.gov/expapi/activities/lesson'){
			$lessonscreated[$createstatement->object->id] = array();
		}elseif ($createstatement->object->definition->type == 'http://adlnet.gov/expapi/activities/unit') {
			$unitscreated[] = $createstatement;
		}
	}
	foreach($unitscreated as $unit){
		$lessonscreated[$unit->context->contextActivities->parent[0]->id][] = $unit->object->id;
	}

// 	Get unit "visited" statements.
	foreach($lessonscreated as &$lesson){
		$lesson['views'] = array();
		$lesson['answers'] = array();
		foreach($lesson as $unit){

			if(!is_array($unit)){
				$tempviews = getVisitedStatements($unit);
				if(!empty($tempviews)){

					foreach ($tempviews as $tempview) {
						if(!array_key_exists($tempview->object->id, $lesson['views'])){
							$lesson['views'][$tempview->object->id] = array(
								'views' => 1,
								'name' => $tempview->object->definition->name->{'en-GB'},
							);
						}else{
							$lesson['views'][$tempview->object->id]['views'] += 1;
						}
					}
				}
				$tempanswers = getAnsweredAssignmentStatements($unit);
				if(!empty($tempanswers)){
					foreach($tempanswers as $tempanswer) {
						if($tempanswer->result->success){
							if(!array_key_exists($tempanswer->object->id, $lesson['answers'])){
								$lesson['answers'][$tempanswer->object->id] = array(
									'answers' => 1,
									'name' => $tempanswer->object->definition->name->{'en-GB'},
								);
							}else{
								$lesson['answers'][$tempanswer->object->id]['answers'] += 1;
							}
						}
					}
				}
			}
		}
	}

	$emptyresponse = false;
	if($emptyresponse){
		$result = array(
			'result' => 'empty',
		);

	}else{
		$result = $lessonscreated;
	}
	echo json_encode($result);
}

function fetchStudentProgressViewData($params){

	// Get and organise all lessons and units
	$urlparams = array('activity' => $params['activity'], 'verb' => 'http://activitystrea.ms/schema/1.0/create');
	$urlparams = http_build_query($urlparams);
	//error_log(print_r($urlparams, true));
	$test = getData($urlparams);
	if(empty($test)){
		$result = array(
			'result' => 'empty',
		);
		echo json_encode($result);
	}else{
		$result = json_encode($test);
		echo $result;
	}
}


function fetchStudentMaterialViewsData($params){
	$start = explode('-', $params['start']);
	$until = explode('-', $params['end']);
	$course = $params['activity'];


	$startdate = date(DATE_ATOM, strtotime('last monday', mktime(0,0,0, $start[1], $start[0], $start[2])));
	$untildate = date(DATE_ATOM, strtotime('next sunday', mktime(23,59,59,$until[1], $until[0], $until[2])));
	// error_log(print_r($startdate, true));
	// error_log(print_r($untildate, true));


	$urlparams = setAllCourseDataParams($startdate, $untildate, $course);

	$coursedata = getData($urlparams);
	$coursedata = super_unique($coursedata);

	/*usort($coursedata, function($a, $b){
		return strtotime($a->timestamp) - strtotime($b->timestamp);
	});*/

	//$urlparams = setAllCourseMembersParams($course);

	$weeksnumber = 1+date('W', strtotime($untildate)) - date('W', strtotime($startdate));
	$weeks = array();
	foreach(range(0,$weeksnumber-1) as $number){
		$first = date(DATE_ATOM, strtotime('+'.$number.' Week', strtotime($startdate)));
		$last = date(DATE_ATOM, strtotime('next monday', strtotime($first)));
		$weeks['Week '.($number+1)]['first-day'] = $first;
		$weeks['Week '.($number+1)]['last-day'] = $last;
		$weeks['Week '.($number+1)]['myinternal'] = 0;
		$weeks['Week '.($number+1)]['myexternal'] = 0;
		$weeks['Week '.($number+1)]['courseinternal'] = 0;
		$weeks['Week '.($number+1)]['courseexternal'] = 0;
	}
	$data = array();


	$emptyresponse = true;

	//$uniqueviewers = array('mailto:'.$params['agent']);
	$uniqueviewers = array();

	foreach($coursedata as $statement){
		if($statement->verb->id == 'http://activitystrea.ms/schema/1.0/visited'){
			foreach($weeks as &$week){
				if($statement->timestamp>($week['first-day']) && $statement->timestamp<($week['last-day'])){
					if(isset($statement->object->definition->type) && $statement->object->definition->type == 'http://adlnet.gov/expapi/activities/link'){
						if($statement->actor->mbox == 'mailto:'.$params['agent']){
							$week['myexternal']+=1;
							$emptyresponse = false;
						}else{
							if(!in_array($statement->actor->mbox, $uniqueviewers)){
								$uniqueviewers[] = $statement->actor->mbox;
							}
							$week['courseexternal']+=1;
							$emptyresponse = false;
						}
					}else{
						if($statement->actor->mbox == 'mailto:'.$params['agent']){
							$week['myinternal']+=1;
							$emptyresponse = false;
						}else{
							if(!in_array($statement->actor->mbox, $uniqueviewers)){
								$uniqueviewers[] = $statement->actor->mbox;
							}
							$week['courseinternal']+=1;
							$emptyresponse = false;
						}
					}
				}
			}
		}
	}


	$pagevisits = fetchStudentRelatedViewsData($params, false);




	if($emptyresponse){
		$result = array(
			'result' => 'empty',
		);
		echo json_encode($result);
	}else{
		foreach($weeks as &$week){
			$week['courseinternal'] = divide($week['courseinternal'],count($uniqueviewers));
			$week['courseexternal'] = divide($week['courseexternal'],count($uniqueviewers));
		}
		$response = array($pagevisits, $weeks);
		echo json_encode($response);
	}
}



function fetchStudentRelatedViewsData($params, $ajaxcall = TRUE){
	//error_log(print_r($params, true));

		$start = explode('-', $params['start']);
		$until = explode('-', $params['end']);
		$course = $params['activity'];


		$startdate = date(DATE_ATOM, strtotime('last monday', mktime(0,0,0, $start[1], $start[0], $start[2])));
		$untildate = date(DATE_ATOM, strtotime('next sunday', mktime(23,59,59,$until[1], $until[0], $until[2])));


		//$weeks = 1+date('W', strtotime($untildate)) - date('W', strtotime($startdate));


		$urlparams = setAllCourseDataParams($startdate, $untildate, $course);

		$coursedata = getData($urlparams);
		//$createcourse = array();
		error_log(print_r($coursedata, true));
		//$externalmaterials = array();
		//$internalmaterials = array();

		$mypagevisits = array();
		$pagevisits = array();
		$helper = array();
		$counter = 0;
		$coursedata = super_unique($coursedata);
		foreach($coursedata as $statement){
			$counter++;
			if($statement->verb->id == 'http://activitystrea.ms/schema/1.0/visited'){
				if(strtotime($statement->timestamp)>strtotime($startdate) && strtotime($statement->timestamp)<strtotime($untildate)){
					$helper[$statement->object->id] = array('name' => $statement->object->definition->name->{'en-GB'});
					if($statement->actor->mbox == 'mailto:'.$params['agent']){
						$mypagevisits[] = $statement->object->id;

					}else{
						$pagevisits[] = $statement->object->id;

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
	if($ajaxcall){
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
	else{
		return $pagevisits;
	}
}

function setAllCourseDataParams($startdate, $untildate, $course){
	$params = array(
		'since' => $startdate,
		'until' => $untildate,
		//'verb' => 'http://activitystrea.ms/schema/1.0/create',
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
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(XAPIVERSIONHEADER));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	$response = curl_exec($curl);

	$response = json_decode($response);
	$data = isset($response->statements) ? $response->statements : 'empty';
	//error_log(print_r($data, true));
	curl_close($curl);
	return $data;
}


?>
