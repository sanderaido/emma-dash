Learning analytics dashboard for EMMA. 
=========

Uses:
- Twitter Bootstrap 3.2.0
- Highcharts 4.0.4 (Needs to be in root folder and called "Highcharts") http://www.highcharts.com
- http://www.eyecon.ro/bootstrap-datepicker/ (in root folder and called datepicker)
- uses PHP function "cal_days_in_month" so http://php.net/manual/en/book.calendar.php needs to be enabled


Notes:
- The fact that if a teacher decides to reopen a course and is able to see, for example enrollments from last year, is not a bug. It is a feature ;)
- See [statement_structures.json](statement_structures.json) for example statement strucure


Instructions:
- in [config.php](config.php) define needed constants. These values can be retrieved from your LRS inside Learning Locker(Log in, choose LRS, go to "xAPI statements"). Please note that the endpoint has to have "statements" at the end(e.g. http://your.learninglocker.install/public/data/xAPI/statements). [see this image](screenshots/llscreenshot.png)
- Right now the users account is hardcoded in [index.php](index.php) as $agent. This should be the logged in users e-mail address.
- Make sure everything is in place from the "Uses" part in this readme(except twitter bootstrap, that comes from cdn).
- Make sure your xAPI statements actor, verb and object correspond to [these](statement_structures.json).
- For student view, please use studentdashboard.php

Statements Implemented:
- Teacher creates course
- Student joins course
- Student leaves course
- Actor visits material(internal/external)
 
Dashboard views implemented:
- [Enrollment history](screenshots/dashboard-view.png)
- [Teacher Overview](screenshots/teacher-overview.png)
- Student Related Views
- Student Material Views
