<?php

require_once('config.php');

$agent = 'student.5@test.jee';

  // $connection = new MongoClient('mongodb://'.MDBHOST.':'.MDBPORT,[
  //   'username' => MDBUSERNAME,
  //   'password' => MDBPASSWORD,
  //   'db' => MDBDB,
  // ]);
  $connection = new MongoClient();
  $db = $connection->selectDB(MDBDB);


  $collection = $db->statements;

  $query = array(
    'statement.actor.mbox' => 'mailto:'.$agent,
    'statement.verb.id' => 'http://activitystrea.ms/schema/1.0/join',
    'statement.object.definition.type' => 'http://adlnet.gov/expapi/activities/course',
  );
  $lrsid = LRSID;
  $query['lrs._id'] = $lrsid;
  $cursor = $collection->find($query);
  $connection->close();

  $courses = array();
  foreach($cursor as $document){
    $courses[] = $document['statement'];
  }
  $uniquecourses = array();
  foreach($courses as $course){
    if(!array_key_exists($course['object']['id'], $uniquecourses)){
      $uniquecourses[$course['object']['id']] = $course;
    }
  }



?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Emma Learning Analytics dashboard</title>

    <!-- Bootstrap -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
   <link rel="stylesheet" href="emmadash.css">
   <link href="datepicker/css/datepicker.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script type="text/javascript">
      var appObject = {};
      appObject.Coursesjs = <?php echo json_encode($courses);?>;
      appObject.Agent = <?php echo json_encode($agent);?>;
    </script>
    <script src="emmastudentdashboard.js"></script>
    <script src="Highcharts/js/highcharts.js"></script>
    <script src="Highcharts/js/modules/exporting.js"></script>
    <script src="datepicker/js/bootstrap-datepicker.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
  </head>

<div class="container-fluid">
      <div class="row">
      <!-- Content -->
      <div class="col-md-9">
            <h1>Students Activity</h1>
            <form class="form-horizontal" role="form">
              <div class="form-group">
                <label for="inputCourse" class="col-sm-2 control-label">Course:</label>
                <div class="col-sm-10">
                  <select class="form-control course-name">
                    <option>Course name</option>
                    <?php
                      foreach($uniquecourses as $course){
                        echo '<option data-url="'.$course['object']['id'].'">'.$course['object']['definition']['name']['en-GB'].'</option>';
                      }
                    ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="inputType" class="col-sm-2 control-label">Type:</label>
                <div class="col-sm-10">
                  <select class="form-control view-type">
                    <!-- <option data-type="relatedViewsStudent">Related Learning Materials</option>
                    <option data-type="materialViewsStudent">Learning Material views</option> -->
                    <option data-type="learningMaterialViewsStudent">Learning Material views</option>
                    <!-- <option data-type="progressStudent">Overall Progress</option> -->
                  </select>
                </div>
              </div>
              <div class="form-group start-group">
                <label for="inputDate" class="col-sm-2 control-label">Start:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control datepicker start" />
                </div>
              </div>
              <div class="form-group end-group">
                <label for="inputDate" class="col-sm-2 control-label">End:</label>
                <div class="col-sm-10">
                <input type="text" class="form-control datepicker end" />
                </div>
              </div>
              <div class="form-group student-overviews-starting-from-group" style="display: none;">
                <label for="overview-starting-from" class="col-sm-2 control-label">Starting From:</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control datepicker-month overview-starting-from" />
                </div>
              </div>
            </form>
          <button type="button" class="btn btn-primary fetch">Fetch data</button>

          <div class="chart-container"></div>
            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto">
              <img class="container-loader" src="ajax-loader.gif">
            </div>
            <br>
    <div class="summary">

    </div>

      </div>
    </div>
    <div id="log"></div>
    </div>
  </body>
</html>
