(function($){	

	$(document).ready(function(){
		var options = {format:'mm-yyyy',weekStart:1,viewMode:1, minViewMode:1};
		$('.datepicker').datepicker(options).on('changeDate', function(ev){
			$('.datepicker-months').parent().css('display', 'none');
		});



		$('.fetch').on('click', function(){
			var courseurl = $('.course-name').find(':selected').data('url');

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
				$.each(json, function(index, value){					
					cat.push(value['date']);
					enrolls.push(value['enrollments']);
					unenrolls.push(value['unenrollments']);
				});
				drawChart(cat, enrolls, unenrolls);
				
			});

			
		});



function drawChart(cat, enrolls, unenrolls){
	$('#container').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'Enrollment history'
        },
        subtitle: {
            text: 'Course name'
        },
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

