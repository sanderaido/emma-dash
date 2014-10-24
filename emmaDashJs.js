(function($){	

	$('#navigation a').click(function (e) {
    	e.preventDefault()
    	$(this).tab('show')
  	});
function daysInMonth(month,year) {
    return new Date(year, month, 0).getDate();
}
	

	$(document).ready(function(){
		var options = {format:'mm-yyyy',weekStart:1,viewMode:1, minViewMode:1};
		$('.datepicker').datepicker(options).on('changeDate', function(ev){
			$('.datepicker-months').parent().css('display', 'none');
		});
		$('.fetch').on('click', function(){
			var date = $('.month').val().split('-');
			var days = daysInMonth(date[0], date[1]);
			var year = date[1];
			var month = date[0];
			cat = new Array();
			for(var n = 0;n<days;n++){
				cat.push(n+1+'-'+month+'-'+year);
			}

			var since = year+'-'+month+'-01T00:00:00';
			var until = year+'-'+month+'-'+(days)+'T23:59:59';
			
			var courseurl = $('.course-name').find(':selected').data('url');

			var request = $.ajax({
				type: 'GET',
				dataType: 'json',
				url: 'http://localhost:8888/ll/learninglocker/public/data/xAPI/statements?verb=http://activitystrea.ms/schema/1.0/join&activity='+courseurl+'&since='+since+'until='+until,
				headers: {
					'X-Experience-API-Version': '1.0.1',
					'Authorization': 'Basic '+btoa('a5c960f66ebb0013e1152504801b70770e342580:41100a94622766b876e918d87c316d34ebbf3f7b')
				}			
			});
			request.fail(function( jqXHR, textStatus ){
				alert('Request failed ' + textStatus);
			});			
			request.done(function(json){
				
				statements = json.statements;
				timestamps = [];

				$.each(statements, function(index, value){
					date = value.timestamp.substring(0,10);
					daterev = date.split('-');
					daterev.reverse();
					date = daterev.join('-');
					timestamps.push(date);
					
				});
				
				enrolls = [];
				timestampsobj = {};
				$.each(timestamps, function(index, value){
					if(timestampsobj.hasOwnProperty(value)){
						timestampsobj[value]+=1;
					}else{
						timestampsobj[value]=1;
					}
				});
				$.each(cat, function(index, value){
					if(timestampsobj.hasOwnProperty(value)){
						enrolls.push(timestampsobj[value]);
					}else{
						enrolls.push(0);
					}
				});

				getUnenrolls(courseurl, since, until);
			});

			
		});

function getUnenrolls(courseurl, since, until){

				var request = $.ajax({
					type: 'GET',
					dataType: 'json',
					url: 'http://localhost:8888/ll/learninglocker/public/data/xAPI/statements?verb=http://activitystrea.ms/schema/1.0/leave&activity='+courseurl+'&since='+since+'until='+until,
					headers: {
						'X-Experience-API-Version': '1.0.1',
						'Authorization': 'Basic '+btoa('a5c960f66ebb0013e1152504801b70770e342580:41100a94622766b876e918d87c316d34ebbf3f7b')
					}			
				});
				request.fail(function( jqXHR, textStatus ){
					alert('Request failed ' + textStatus);
				});
				request.done(function(json){
					statementsun = json.statements;
					timestampsun = []
				
					$.each(statementsun, function(index, value){
						date = value.timestamp.substring(0,10);
						daterev = date.split('-');
						daterev.reverse();
						date = daterev.join('-');
						timestampsun.push(date);	
					});
					
					unenrolls = [];
					timestampsunobj = {};
					$.each(timestampsun, function(index, value){
						if(timestampsunobj.hasOwnProperty(value)){
							timestampsunobj[value]+=1;
						}else{
							timestampsunobj[value]=1;
						}
					});
					
					$.each(cat, function(index, value){
						if(timestampsunobj.hasOwnProperty(value)){
							unenrolls.push(timestampsunobj[value]);
						}else{
							unenrolls.push(0);
						}
					});

					drawChart();
				});	
	
}

function drawChart(){
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
var totalenroll = 0;
			// var totalunenroll = 0;
			// $.each(enrolls, function(index, value){
			// 	totalenroll+=value;
			// });
			// $.each(unenrolls, function(index, value){
			// 	totalunenroll+=value;
			// });
			// $('.total-enrollments').prepend(totalenroll);
			// $('.total-unenrollments').prepend(totalunenroll);
}
		
			
			

		    
		
	});

})(jQuery);

