(function($){

	$(document).ready(function(){
    var enrcheckDates = true;
    var checkovDates = false;
    $('.view-type').on('change', function(){
      if($('.view-type').find(':selected').data('type') == 'overview'){
        enrcheckDates = false;
        checkovDates = true;
        $('.month-group').css('display', 'none');
        if($('.overviews-starting-from-group').is(':hidden')){
          $('.overviews-starting-from-group').css('display', 'block');
        }
      }else{
        if($('.overviews-starting-from-group').is(':visible')){
          $('.overviews-starting-from-group').css('display', 'none');
          checkovDates = false;
        }


        if($('.month-group').is(':hidden')){
          enrcheckDates = true;
          $('.month-group').css('display', 'block');
        }
      }
    });
		var options = {format:'mm-yyyy',weekStart:1,viewMode:1, minViewMode:1};
		$('.datepicker').datepicker(options).on('changeDate', function(ev){
			$('.datepicker-months').parent().css('display', 'none');
		});
    $('.overview-starting-from').keydown(function(){
      return false;
    });
		$('.month').keydown(function(){
			return false;
		});

		$('.fetch').on('click', function(e){
			var hasError = false;
			var courseurl = $('.course-name').find(':selected').data('url');
			if(typeof courseurl === 'undefined'){
				$('.course-name').parents('.form-group').addClass('has-error');
				hasError = true;
			}else{
				if($('.course-name').parents('.form-group').hasClass('has-error')){
					$('.course-name').parents('.form-group').removeClass('has-error');
				}
			}
      if($('.overview-starting-from').val() === '' && checkovDates==true){
        $('.overview-starting-from').parents('.form-group').addClass('has-error');
        hasError = true;
      }else{
          if($('.overview-starting-from').parents('.form-group').hasClass('has-error')){
            $('.overview-starting-from').parents('.form-group').removeClass('has-error');
          }
        }

			if($('.month').val() === '' && enrcheckDates==true){
				$('.month').parents('.form-group').addClass('has-error');
				hasError = true;
			}else{
				if($('.month').parents('.form-group').hasClass('has-error')){
					$('.month').parents('.form-group').removeClass('has-error');
				}
			}

			if (hasError) {
				return;
			}

			if($('.container-loader').length!=0){
				$('.container-loader').css('display', 'inline');
		  }else{
				$('#container').html('<img class="container-loader" src="ajax-loader.gif">');
				$('.container-loader').css('display', 'inline');
			}
			var coursename = $('.course-name').find(':selected').val();
      var type =  $('.view-type').find(':selected').data('type');
      if(type == 'enrollmentactivity'){
  			var request = $.ajax({
  				type: 'GET',
  				dataType: 'json',
  				data: {
  					'type':'enrollmentactivity',
  					'date': $('.month').val(),
  					'activity': courseurl
  				},
  				url: 'mongorequests.php'

  			});
  			request.fail(function( jqXHR, textStatus ){
  				alert('Request failed ' + textStatus );

  			});
  			request.done(function(json){

  				var cat = [];
  				var enrolls = [];
  				var unenrolls = [];
  				if(json.result == 'empty'){
  					$('#container').html('<div class="container col-sm-12 jumbo-contain"><div class="jumbotron"><h1>Sorry!</h1><p>There is no data for the <a href="'+courseurl+'">Selected course ('+coursename+')</a> during the selected time period</p></div></div>');
  					$('.summary').css('display', 'none');
  				}else{
            if($('.summary').is(':hidden')){
              $('.summary').css('display', 'inline');
            }
  					$.each(json, function(index, value){
  						cat.push(value['date']);
  						enrolls.push(value['enrollments']);
  						unenrolls.push(value['unenrollments']);
  					});

  					drawEnrollmentChart(cat, enrolls, unenrolls, coursename);

  				}
  			});
      }
      if(type == 'overview'){

        if($('.summary').is(':visible')){
              $('.summary').css('display', 'none');
            }
        var request = $.ajax({
          type: 'GET',
          dataType: 'json',
          data: {
            'type' : 'overviewTeacher',
            'date' : $('.overview-starting-from').val(),
            'activity' : courseurl,
            'teacher' : appObject.Agent
          },
          url : 'mongorequests.php'
        });
        request.fail(function( jqXHR, textStatus){
          alert('Request failed '+textStatus);
        });
        request.done(function(json){
          if(json.result == 'empty'){
            $('#container').html('<div class="container col-sm-12 jumbo-contain"><div class="jumbotron"><h1>Sorry!</h1><p>There is no data for the <a href="'+courseurl+'">Selected course ('+coursename+')</a></p></div></div>');
          }else{
            var participants = json.participants;
            delete json.participants;
            var cat = [];
            var views = [];
            var answers = [];
            tabs = '';
            $.each(json, function(key, value){
              lsviews = 0;
              lsanswers = 0;
              cat.push(value.lsName);
              viewActivity = value.viewers / participants * 100;
              views.push(viewActivity);
              answerActivity = value.answerers / participants * 100;
              answers.push(answerActivity);
            });
            if($('.summary').is(':hidden')){
              $('.summary').css('display', 'block');
            }

            drawOverAllProgressforTeacher(cat, views, answers, coursename);
            $('.summary').html('');
            $('.summary').append('<div class="overview-summary"><div class="overview-tabs"></div></div>');
            $('.overview-tabs').append('<ul class="nav nav-tabs overview-tabs-ul" data-tabs="tabs" role="tablist"></ul>');
            var isfirst = true;
            var counter = 0;
            var plotbandFrom = -0.5
            var plotbandTo = 0.5;

            $.each(json, function(key, value){
              counter++;
              if(isfirst){
                $('.overview-tabs-ul').append('<li role="presentation" class="active"><a class="overview-lesson-tab" data-url="'+key+'" data-plotfrom="'+plotbandFrom+'" data-plotTo="'+plotbandTo+'" href="#tab-'+counter+'" aria-controls="'+value.lsName+'" role="tab" data-toggle="tab">'+value.lsName+'</a></li>');
              }else{
                $('.overview-tabs-ul').append('<li role="presentation"><a class="overview-lesson-tab" data-plotFrom="'+plotbandFrom+'" data-plotto="'+plotbandTo+'" data-url="'+key+'" href="#tab-'+counter+'" aria-controls="'+value.lsName+'" role="tab" data-toggle="tab">'+value.lsName+'</a></li>');
              }
              plotbandFrom++;
              plotbandTo++;
              isfirst = false
            });
            isfirst = true;
            $('.overview-tabs').append('<div class="tab-content overview-tabs-content"></div>');
            counter = 0;
            $.each(json, function(key, value){
              counter++;
              if(isfirst){
                $('.overview-tabs-content').append('<div role="tabpanel" class="tab-pane active" id="tab-'+counter+'">'+getLessonViewsSorted(value.views, value.lsName)+getLessonAnswersSorted(value.answers, value.lsName)+'</div>');
              }else{
                $('.overview-tabs-content').append('<div role="tabpanel" class="tab-pane" id="tab-'+counter+'">'+getLessonViewsSorted(value.views, value.lsName)+getLessonAnswersSorted(value.answers, value.lsName)+'</div>');
              }
              isfirst = false;
            });



            $('.overview-lesson-tab').on('click', function(){


              var chart = $('#container').highcharts();
              chart.xAxis[0].removePlotBand('plotband-1');
              chart.xAxis[0].addPlotBand({
                  from: $(this).data('plotfrom'),
                  to: $(this).data('plotto'),
                  color: '#FCFFC5',
                  id: 'plotband-1'
              });
            });
          }
        });
      }

		});
function getLessonAnswersSorted(answers, lessonName){
  table = '<div class="row"><h3>'+lessonName+' - performed assignments</h3></div>';
  if($.isEmptyObject(answers)){
    return '';
  }else{
    table+=('<table class="table table-condensed">');
    table+=('<thead><tr><th>#</th><th>Assignment Title</th><th>Submissions</th></tr></thead>');
    table+=('<tbody>');
    counter = 1;
    $.each(answers, function(key, value){
      table+=('<tr><td>'+counter+'</td><td><a href="'+key+'">'+value.name+'</a></td><td>'+value.answers+'</td></tr>');
      counter++;
    });
    table+=('</tbody></table>');


    return table;
  }
}
function getLessonViewsSorted(views, lessonName){
  table = '<div class="row"><h3>'+lessonName+' - popular materials</h3></div>';
  if($.isEmptyObject(views)){
    return '';
  }else{
    table+=('<table class="table table-condensed">');
    table+=('<thead><tr><th>#</th><th>Name</th><th>Views</th></tr></thead>');
    table+=('<tbody>');
    counter = 1;
    $.each(views, function(key, value){
      table+=('<tr><td>'+counter+'</td><td><a href="'+key+'">'+value.name+'</a></td><td>'+value.views+'</td></tr>');
      counter++;
    });
    table+=('</tbody></table>');


    return table;
  }
}


function drawOverAllProgressforTeacher(cat, views, answers, coursename){
  $('#container').highcharts({
    chart: {
      type: 'column'
    },
    title: {
      text: 'Overall progress of students on selected course'
    },
    subtitle: {
      text: coursename
    },
    // If needed, bar colours can be defined here

        colors: [
          '#00AA9D',
          '#C26FAC'
        ],
    xAxis: {
            categories: cat,
            plotBands: [{
              color: '#FCFFC5',
              from: -0.5,
              to: 0.5,
              id: 'plotband-1'
            }]
    },
    yAxis: {
            allowDecimals: false,
            min: 0,
            max: 100,
            title: {
                text: ''
            },
            labels: {
              formatter: function(){
                return this.value + '%';
              }
            }
        },

        tooltip: {
            formatter: function () {
                return this.series.name + ': ' + this.y + '%<br/>';
            }
        },

        plotOptions: {
            column: {
                stacking: 'normal'
            }
        },

        series: [{
            name: 'Material views',
            data: views,
            stack: 'material views'
        },{
            name: 'Finished assignments',
            data: answers,
            stack: 'finished assignments'
        }]
    });
}

function drawEnrollmentChart(cat, enrolls, unenrolls, coursename){
	$('#container').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Enrollment history'
        },
        subtitle: {
            text: coursename
        },
        // If needed, bar colours can be defined here

        colors: [
        	'#00AA9D',
        	'#C26FAC'
        ],
        xAxis: {
            categories: cat,
            labels: {
            	rotation: -45
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Enroll/Unenroll'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                '<td style="padding:0"><b>{point.y}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.1,
                borderWidth: 1
            }
        },
        series: [{
            name: 'Enroll',
            data: enrolls

        }, {
            name: 'Unenroll',
            data: unenrolls

        }]
    });
  $('.summary').html('');

	var totalenrollments = 0;
	var totalunenrollments = 0;
	$.each(enrolls, function(index, value){
		totalenrollments+=value;
	});
	$.each(unenrolls, function(index, value){
		totalunenrollments+=value;
	});
  $('.summary').append('<div class="enrollment-summary"><div class="panel panel-default"><div class="panel-heading">Enrollment summary</div><div class="panel-body"></div></div></div>');

	$('.summary .panel-body').append('<div class="total-enrollments"></div>');
	$('.summary .panel-body').append('<div class="total-unenrollments"></div>');
	$('.total-enrollments').html(totalenrollments+' Enrollments during selected period');
	$('.total-unenrollments').html(totalunenrollments+' Unenrollments during selected period');



}

	});

})(jQuery);

