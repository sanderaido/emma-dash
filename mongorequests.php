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

function divide($a, $b){
  if($a==0 || $b==0){
    return 0;
  }else{
    return $a/$b;
  }
}

function fetchDataFromDB($query){

  $query['lrs._id'] = LRSID;

  $connection = new MongoClient('mongodb://'.MDBHOST.':'.MDBPORT,[
    'username' => MDBUSERNAME,
    'password' => MDBPASSWORD,
    'db' => MDBDB,
  ]);
  $db = $connection->selectDB(MDBDB);



  $collection = $db->statements;

  $cursor = $collection->find($query);
  $connection->close();

  return $cursor;
}

function fetchStudentProgressViewData($params){

  $agent = $params['agent'];
  $course = $params['activity'];
  $monthandyear = explode('-', $params['date']);
  $date = date(DATE_ATOM, mktime(0,0,0,$monthandyear[0], 1,$monthandyear[1]));

  $query = array(
    'statement.verb.id' => 'http://activitystrea.ms/schema/1.0/create',
    'statement.context.contextActivities.grouping.0.id' => $course,
    'statement.object.definition.type' => array('$in' => array('http://adlnet.gov/expapi/activities/unit', 'http://adlnet.gov/expapi/activities/lesson')),
    'statement.timestamp' => array('$gte' => $date),
  );

  $cursor = fetchDataFromDB($query);

  $lessonscreated = array();
  $unitscreated = array();



  foreach($cursor as $document){
    if($document['statement']['object']['definition']['type'] == 'http://adlnet.gov/expapi/activities/lesson'){
      $lessonscreated[$document['statement']['object']['id']] = array(
        'lsName' => array($document['statement']['object']['definition']['name']['en-GB']),
      );
    }elseif ($document['statement']['object']['definition']['type'] == 'http://adlnet.gov/expapi/activities/unit'){
      $unitscreated[] = $document['statement'];
    }
  }
  foreach($unitscreated as $unit){
    $lessonscreated[$unit['context']['contextActivities']['parent'][0]['id']][] = $unit['object']['id'];
  }

  $participants = getCourseParticipants($course, $monthandyear);
  $data = array();
  foreach($lessonscreated as &$lesson){
    foreach($lesson as $unit){
      if(!is_array($unit)){
        $unitassignments = getUnitAssignments($unit, $date);
        if(!empty($unitassignments)){

        }
      }
    }

  }


}

function getUnitAssignments($unit, $date){
  $query = array(
    'statement.verb.id' => 'http://activitystrea.ms/schema/1.0/create',
    'statement.context.contextActivities.parent.0.id' => $unit,
    'statement.object.definition.type' => 'http://adlnet.gov/expapi/activities/assignment',
    'statement.timestamp' => array('$gte' => $date),
  );


  $createAssignmentStatements = array();
  $cursor = fetchDataFromDB($query);
  foreach($cursor as $document){
    $createAssignmentStatements[] = $document['statement'];
  }



}

function checkIfAssignmentPassed($agent, $assignment){

}

function getCourseParticipants($course, $monthandyear){
  $since = date(DATE_ATOM, mktime(0,0,0,$monthandyear[0], 1,$monthandyear[1]));
  $query = array(
    'statement.verb.id' => 'http://activitystrea.ms/schema/1.0/join',
    'statement.object.id' => $course,
    'statement.timestamp' => array('$gte' => $since),
  );

  $cursor = fetchDataFromDB($query);

  $participants = array();

  foreach($cursor as $document){
    if(!(array_key_exists($document['statement']['actor']['mbox'], $participants))){
      $participants[$document['statement']['actor']['mbox']] = 1;
    }else{
      $participants[$document['statement']['actor']['mbox']] += 1;
    }
  }
  $query['statement.verb.id'] = 'http://activitystrea.ms/schema/1.0/leave';
  $cursor = fetchDataFromDB($query);
  foreach($cursor as $document){
    if(array_key_exists($document['statement']['actor']['mbox'], $participants)){
      $participants[$document['statement']['actor']['mbox']] -=1;
    }
  }

  foreach($participants as $key => $participant){
    if($participant<1){
      unset($participants[$key]);
    }
  }
  // $participantsflipped = array();
  // foreach($participants as $key => $value){
  //   $participantsflipped[] = $key;
  // }
  // error_log(print_r($participants, true ));
  // error_log(print_r($participantsflipped, true));
  return $participants;

}


function fetchTeacherOverallProgressData($params){

  $monthandyear = explode('-', $params['date']);
  $course = $params['activity'];
  $date = date(DATE_ATOM, mktime(0,0,0,$monthandyear[0], 1,$monthandyear[1]));



  $query = array(
    'statement.verb.id' => 'http://activitystrea.ms/schema/1.0/create',
    'statement.context.contextActivities.grouping.0.id' => $course,
    'statement.object.definition.type' => array('$in' => array('http://adlnet.gov/expapi/activities/unit', 'http://adlnet.gov/expapi/activities/lesson')),
    'statement.timestamp' => array('$gte' => $date),
  );

  $cursor = fetchDataFromDB($query);

  $lessonscreated = array();
  $unitscreated = array();



  foreach($cursor as $document){
    if($document['statement']['object']['definition']['type'] == 'http://adlnet.gov/expapi/activities/lesson'){
      $lessonscreated[$document['statement']['object']['id']] = array(
        'lsName' => array($document['statement']['object']['definition']['name']['en-GB']),
      );
    }elseif ($document['statement']['object']['definition']['type'] == 'http://adlnet.gov/expapi/activities/unit'){
      $unitscreated[] = $document['statement'];
    }
  }
  foreach($unitscreated as $unit){
    $lessonscreated[$unit['context']['contextActivities']['parent'][0]['id']][] = $unit['object']['id'];
  }

  $participants = getCourseParticipants($course, $monthandyear);

  foreach($lessonscreated as &$lesson){
    $lesson['views'] = array();
    $lesson['answers'] = array();
    $lesson['viewers'] = array();
    $lesson['answerers'] = array();

    foreach($lesson as $unit){
      if(!is_array($unit)){
        $tempviews = getVisitedStatements($unit);
        if(!(empty($tempviews))){
          foreach($tempviews as $tempview){
            if(array_key_exists($tempview['actor']['mbox'], $participants)){
              if(!array_key_exists($tempview['object']['id'], $lesson['views'])){
                $lesson['views'][$tempview['object']['id']] = array(
                  'views' => 1,
                  'name' => $tempview['object']['definition']['name']['en-GB'],
                );
              }else{
                $lesson['views'][$tempview['object']['id']]['views'] += 1;
              }
              if(!array_key_exists($tempview['actor']['mbox'], $lesson['viewers'])){
                $lesson['viewers'][$tempview['actor']['mbox']] = true;
              }
            }
          }
        }
        $tempanswers = getAnsweredAssignments($unit);
        if(!empty($tempanswers)){
          foreach($tempanswers as $tempanswer){
            if($tempanswer['result']['success']){
              if(array_key_exists($tempanswer['actor']['mbox'], $participants)){
                if(!array_key_exists($tempanswer['object']['id'], $lesson['answers'])){
                  $lesson['answers'][$tempanswer['object']['id']] = array(
                    'answers' => 1,
                    'name' => $tempanswer['object']['definition']['name']['en-GB'],
                  );
                }else{
                  $lesson['answers'][$tempanswer['object']['id']]['answers'] += 1;
                }
                if(!array_key_exists($tempanswer['actor']['mbox'], $lesson['answerers'])){
                  $lesson['answerers'][$tempanswer['actor']['mbox']] = true;
                }
              }
            }
          }
        }
      }
    }
  }

  foreach($lessonscreated as &$lesson){
    uasort($lesson['views'], 'compareViews');
    uasort($lesson['answers'], 'compareAnswers');
  }

  foreach($lessonscreated as &$lesson){
    $lesson['viewers'] = count($lesson['viewers']);
    $lesson['answerers'] = count($lesson['answerers']);
  }
  $emptyresponse = false;
  if($emptyresponse){
    $result = array(
      'result' => 'empty',
    );

  }else{
    $result = $lessonscreated;
    $result['participants'] = count($participants);
  }
  echo json_encode($result);

}

function compareViews($a, $b){
  if($a['views']==$b['views']){
    return 0;
  }
  return ($a['views'] < $b['views']) ? 1 : -1;
}


function compareAnswers($a, $b){
  if($a['answers']==$b['answers']){
    return 0;
  }
  return ($a['answers'] < $b['answers']) ? 1 : -1;
}

function getAnsweredAssignments($unit){
  $query = array(
    'statement.verb.id' => 'http://adlnet.gov/expapi/verbs/answered',
    'statement.context.contextActivities.parent.id' => $unit,
  );
  $cursor = fetchDataFromDB($query);

  $response = array();
  foreach($cursor as $document){
    $response[] = $document['statement'];
  }

  return $response;
}

function getVisitedStatements($unit){

  $query = array(
    'statement.verb.id' => 'http://activitystrea.ms/schema/1.0/visited',
    'statement.context.contextActivities.parent.id' => $unit,
  );

  $cursor = fetchDataFromDB($query);


  $response = array();
  foreach($cursor as $document){
    $response[] = $document['statement'];
  }

  return $response;
}


function fetchStudentMaterialViewsData($params){
  $start = explode('-', $params['start']);
  $until = explode('-', $params['end']);
  $course = $params['activity'];


  $startdate = date(DATE_ATOM, strtotime('last monday', mktime(0,0,0, $start[1], $start[0], $start[2])));
  $untildate = date(DATE_ATOM, strtotime('next sunday', mktime(23,59,59,$until[1], $until[0], $until[2])));

  $query = array(
    'statement.verb.id' => 'http://activitystrea.ms/schema/1.0/visited',
    'statement.context.contextActivities.grouping.0.id' => $course,
    'statement.timestamp' => array('$gte'=>$startdate, '$lte'=>$untildate),
  );


  $cursor = fetchDataFromDB($query);

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

  $uniqueviewers = array();

  foreach($cursor as $document) {
    foreach($weeks as &$week){
      if($document['statement']['timestamp']>($week['first-day']) && $document['statement']['timestamp']<($week['last-day'])){
        if(isset($document['statement']['object']['definition']['type']) && $document['statement']['object']['definition']['type'] == 'http://adlnet.gov/expapi/activities/link'){
          if($document['statement']['actor']['mbox']=='mailto:'.$params['agent']){
            $week['myexternal']+=1;
            $emptyresponse = false;
          }else{
            if(!in_array($document['statement']['actor']['mbox'], $uniqueviewers)){
              $uniqueviewers[] = $document['statement']['actor']['mbox'];
            }
            $week['courseexternal']+=1;
            $emptyresponse = false;
          }
        }else{
          if($document['statement']['actor']['mbox']=='mailto:'.$params['agent']){
            $week['myinternal']+=1;
            $emptyresponse = false;
          }else{
            if(!in_array($document['statement']['actor']['mbox'], $uniqueviewers)){
              $uniqueviewers[] = $document['statement']['actor']['mbox'];
            }
            $week['courseinternal']+=1;
            $emptyresponse = false;
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


  $start = explode('-', $params['start']);
  $until = explode('-', $params['end']);
  $course = $params['activity'];


  $startdate = date(DATE_ATOM, strtotime('last monday', mktime(0,0,0, $start[1], $start[0], $start[2])));
  $untildate = date(DATE_ATOM, strtotime('next sunday', mktime(23,59,59,$until[1], $until[0], $until[2])));


  $query = array(
    'statement.verb.id' => 'http://activitystrea.ms/schema/1.0/visited',
    'statement.context.contextActivities.grouping.0.id' => $course,
    'statement.timestamp' => array('$gte'=>$startdate, '$lte'=>$untildate),
  );


  $cursor = fetchDataFromDB($query);
  $myviews = array();
  $otherviews = array();
  $helper = array();
  foreach($cursor as $document){
    $helper[$document['statement']['object']['id']] = array('name' => $document['statement']['object']['definition']['name']['en-GB']);
    if($document['statement']['actor']['mbox'] == 'mailto:'.$params['agent']){
      $myviews[] = $document['statement']['object']['id'];
    }else{
      $otherviews[] = $document['statement']['object']['id'];
    }
  }

  $mycount = array_count_values($myviews);
  asort($mycount);
  $mycount = array_reverse($mycount);
  $othercount = array_count_values($otherviews);
  asort($othercount);
  $othercount = array_reverse($othercount);

  $myvisits = array();
    foreach($mycount as $key => $value){
      $myvisits[] = array(
        'name' => $helper[$key]['name'],
        'url' => $key,
        'count' => $value,
      );
    }

    $pagevisits = array();
    foreach($othercount as $key => $value){
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


function fetchEnrollmentActivityData($params){


  $verb = 'http://activitystrea.ms/schema/1.0/join';
  $monthandyear = explode('-', $params['date']);
  $activity = $params['activity'];
  $daysinmonth = cal_days_in_month(CAL_GREGORIAN, $monthandyear[0], $monthandyear[1]);

  $since = date(DATE_ATOM, mktime(0,0,0,$monthandyear[0], 1,$monthandyear[1]));
  $until = date(DATE_ATOM, mktime(23,59,59,$monthandyear[0], $daysinmonth,$monthandyear[1]));


  $query = array(
    'statement.verb.id' => 'http://activitystrea.ms/schema/1.0/join',
    'statement.object.id' => $params['activity'],
    'statement.timestamp' => array('$gte'=>$since, '$lte'=>$until),
  );

  $cursor = fetchDataFromDB($query);
  $enrollmentdates = array();
  foreach($cursor as $document){
    array_push($enrollmentdates, substr($document['statement']['timestamp'], 0,10));
  }

  $query['statement.verb.id'] = 'http://activitystrea.ms/schema/1.0/leave';
  $cursor = fetchDataFromDB($query);
  $unenrollmentdates = array();
  foreach($cursor as $document){
    array_push($unenrollmentdates, substr($document['statement']['timestamp'], 0,10));
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
  $unenrollmentcounts = array_count_values($unenrollmentdates);

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
