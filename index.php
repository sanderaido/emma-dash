<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Emma Learning Analytics dashboard</title>

    <!-- Bootstrap -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
   
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
      
      <div class="row">

      <!-- Navigation Buttons -->
      <div class="col-md-3">
        <ul class="nav nav-pills nav-stacked" id="navigation">
          <li class="active"><a href="#la">Learning Analytics</a></li>
          <li><a href="#cs">Course Statistics</a></li>          
        </ul>
      </div>

      <!-- Content -->
      <div class="col-md-9">
        <div class="tab-content">
          <div class="tab-pane active" id="la">
            <h1>Learning Analytics</h1>

            <form class="form-horizontal" role="form">
              <div class="form-group">
                <label for="inputCourse" class="col-sm-2 control-label">Course:</label>
                <div class="col-sm-10">
                  <select class="form-control">
                    <option>Course name</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4</option>
                    <option>5</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="inputType" class="col-sm-2 control-label">Course:</label>
                <div class="col-sm-10">
                  <select class="form-control">
                    <option>Enrollment Activity</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4</option>
                    <option>5</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="inputDate" class="col-sm-2 control-label">Start:</label>
                <div class="col-sm-10">
                <input type="date" class="form-control" /> 
                </div>
              </div>
              <div class="form-group">
                <label for="inputDate" class="col-sm-2 control-label">End:</label>
                <div class="col-sm-10">
                <input type="date" class="form-control" /> 
                </div>
              </div>
            </form>
          </div>
          <div class="tab-pane" id="cs">
            <h1>Course Statistics</h1>
          </div>          
        </div>
      </div>

    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

    <script src="emmaDashJs.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
  </body>
</html>
