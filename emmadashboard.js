(function($){	

	$(document).ready(function(){
		var options = {format:'mm-yyyy',weekStart:1,viewMode:1, minViewMode:1};
		$('.datepicker').datepicker(options).on('changeDate', function(ev){
			$('.datepicker-months').parent().css('display', 'none');
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
			if($('.month').val() === ''){
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
			var request = $.ajax({
				type: 'GET',
				dataType: 'json',
				data: {
					'type':'enrollmentactivity',					
					'date': $('.month').val(),
					'activity': courseurl
				},
				url: 'requests.php'
				
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
					$.each(json, function(index, value){					
						cat.push(value['date']);
						enrolls.push(value['enrollments']);
						unenrolls.push(value['unenrollments']);
					});
						
					drawEnrollmentChart(cat, enrolls, unenrolls, coursename);
					if($('.summary').is(":hidden")){
						$('.summary').css('display', 'inline');
					}
				}
			});

			
		});



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
        
        // colors: [
        // 	'#00ff00',
        // 	'#ff0000'
        // ],
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
	$('.summary .panel-body').html('');
	var totalenrollments = 0;
	var totalunenrollments = 0;
	$.each(enrolls, function(index, value){
		totalenrollments+=value;
	});
	$.each(unenrolls, function(index, value){
		totalunenrollments+=value;
	});
	
	$('.summary .panel-body').append('<div class="total-enrollments"></div>');
	$('.summary .panel-body').append('<div class="total-unenrollments"></div>');
	$('.total-enrollments').html(totalenrollments+' Enrollments during selected period');
	$('.total-unenrollments').html(totalunenrollments+' Unenrollments during selected period');


}
		
			
			

		    
		
	});

})(jQuery);

