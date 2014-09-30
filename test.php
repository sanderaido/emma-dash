<?php 


$testagent = array(
    'mbox' => 'mailto:example.learner@adlnet.gov',
  );

$testagent = json_encode($testagent);
echo $testagent;


// http://localhost:8888/ll/learninglocker/public/data/xAPI/statements?agent={"mbox":"mailto:teacher.1@gmail.com"}

// http://localhost:8888/ll/learninglocker/public/data/xAPI/statements?agent={"mbox":"mailto:teacher.1@gmail.com"}&verb=http://activitystrea.ms/schema/1.0/create
?>