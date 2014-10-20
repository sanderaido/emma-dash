(function($){	

	$('#navigation a').click(function (e) {
    	e.preventDefault()
    	$(this).tab('show')
  	});


	$(document).ready(function(){

			var enrolldata = [49, 0,106,129,144,176,135,148,216,194,95,54];
			var unenrolldata = [0, 25, 98, 93, 106, 84, 105, 104, 91, 83, 106, 92];
			var totalenroll = 0;
			var totalunenroll = 0;
			$.each(enrolldata, function(index, value){
				totalenroll+=value;
			});
			$.each(unenrolldata, function(index, value){
				totalunenroll+=value;
			});
			$('.total-enrollments').prepend(totalenroll);
			$('.total-unenrollments').prepend(totalunenroll);
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
		            categories: [
		                'Jan',
		                'Feb',
		                'Mar',
		                'Apr',
		                'May',
		                'Jun',
		                'Jul',
		                'Aug',
		                'Sep',
		                'Oct',
		                'Nov',
		                'Dec'
		            ]
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
		            data: enrolldata

		        }, {
		            name: 'Unenroll',
		            data: unenrolldata

		        }]
		    });
		
	});

})(jQuery);

