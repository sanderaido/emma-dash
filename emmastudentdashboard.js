(function($){

	$(document).ready(function(){
		var checkDates = true;
		var options = {format:'dd-mm-yyyy',weekStart:1,viewMode:0, minViewMode:0};
		$('.view-type').on('change', function(){
			if($('.view-type').find(':selected').data('type') == 'progressStudent'){
				options = {format:'mm-yyyy',weekStart:1,viewMode:1, minViewMode:1};
				$('.start-group').css('display', 'none');
				$('.end-group').css('display', 'none');
				if($('.student-overviews-starting-from-group').is(':hidden')){
					$('.student-overviews-starting-from-group').css('display', 'block');
				}
				$('.datepicker-month').datepicker(options).on('changeDate', function(ev){
					$('.datepicker-months').parent().css('display', 'none');
				});
				checkDates = false;
			}else{
				options = {format:'dd-mm-yyyy',weekStart:1,viewMode:0, minViewMode:0};
				checkDates = true;
				if($('.start-group').is(':hidden')){
					$('.start-group').css('display', 'block');
				}
				if($('.end-group').is(':hidden')){
					$('.end-group').css('display', 'block');
				}
			}
		});


		$('.datepicker').datepicker(options).on('changeDate', function(ev){
			$('.datepicker-months').parent().css('display', 'none');
		});

		$('.datepicker').keydown(function(){
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
			if($('.start').val() === ''){
				if(checkDates == true){
					$('.start').parents('.form-group').addClass('has-error');
					hasError = true;
				}
			}else{
				if($('.start').parents('.form-group').hasClass('has-error')){
					$('.start').parents('.form-group').removeClass('has-error');
				}
			}
			if($('.end').val() === ''){
				if(checkDates == true){
					$('.end').parents('.form-group').addClass('has-error');
					hasError = true;
				}
			}else{
				if($('.end').parents('.form-group').hasClass('has-error')){
					$('.end').parents('.form-group').removeClass('has-error');
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
			var type = 	$('.view-type').find(':selected').data('type');
			if(type=='relatedViewsStudent'){
				var request = $.ajax({
					type: 'GET',
					dataType: 'json',
					data: {
						'type':'relatedViewsStudent',
						'start': $('.start').val(),
						'end': $('.end').val(),
						'activity': courseurl,
						'agent': appObject.Agent
					},
					url: 'mongorequests.php'

				});
				request.fail(function( jqXHR, textStatus ){
					alert('Request failed ' + textStatus );

				});
				request.done(function(json){
					//console.log(json);
					if(json.result == 'empty'){
						$('#container').html('<div class="container col-sm-12 jumbo-contain"><div class="jumbotron"><h1>Sorry!</h1><p>There is no data for the <a href="'+courseurl+'">Selected course ('+coursename+')</a> during the selected time period</p></div></div>');
						$('.summary').css('display', 'none');
					}else{

						drawRelatedViewsTables(json);

					}
				});
			}
			if(type=='materialViewsStudent'){
				var request = $.ajax({
					type: 'GET',
					dataType: 'json',
					data:{
						'type': 'materialViewsStudent',
						'start': $('.start').val(),
						'end': $('.end').val(),
						'activity': courseurl,
						'agent': appObject.Agent,
					},
					url: 'mongorequests.php'
				});
				request.fail(function( jqXHR, textStatus ){
					alert('Request failed ' + textStatus );

				});
				request.done(function(json){
					if(json.result == 'empty'){
						$('#container').html('<div class="container col-sm-12 jumbo-contain"><div class="jumbotron"><h1>Sorry!</h1><p>There is no data for the <a href="'+courseurl+'">Selected course ('+coursename+')</a> during the selected time period</p></div></div>');
						$('.summary').css('display', 'none');
					}
					else{
						drawMaterialViewsChart(json);
					}
				});
			}
			if(type=='progressStudent'){
				var request = $.ajax({
					type: 'GET',
					dataType: 'json',
					data:{
						'type': 'progressStudent',
						'activity': courseurl,
						'agent': appObject.Agent,
						'date': $('.datepicker-month').val()
					},
					url: 'mongorequests.php'
				});
				request.fail(function( jqXHR, textStatus){
					alert('Request failed ' + textStatus );
				});
				request.done(function(json){
					if(json.result == 'empty'){
						$('#container').html('<div class="container col-sm-12 jumbo-contain"><div class="jumbotron"><h1>Sorry!</h1><p>There is no data for the <a href="'+courseurl+'">Selected course ('+coursename+')</a> during the selected time period</p></div></div>');
						$('.summary').css('display', 'none');
					}else{
						drawStudentProgress(json);
					}
				});
			}
		});

function drawStudentProgress(json){
	$('#container').html(JSON.stringify(json, null, 2));
}

function drawMaterialViewsChart(json){
	$('#container').html();
	var fortable = json[0];
	var forchart = json[1];
	var categories = [];
	var myinternal = [];
	var mytotalinternal = 0;
	var mytotalexternal = 0;
	var coursetotalinternal = 0;
	var coursetotalexternal = 0;
	var myexternal = [];
	var courseinternal = [];
	var courseexternal = [];
	$.each(forchart, function(index, value){
		categories.push(index);
		myinternal.push(value.myinternal);
		myexternal.push(value.myexternal);
		courseinternal.push(value.courseinternal);
		courseexternal.push(value.courseexternal);
		mytotalinternal+=value.myinternal;
		mytotalexternal+=value.myexternal;
		coursetotalinternal+=value.courseinternal;
		coursetotalexternal += value.courseexternal;
	});

	difference = (coursetotalinternal+coursetotalexternal)-(mytotalexternal+mytotalinternal);
	var moreorless = 'less';
	if(difference<0){
		moreorless = 'more';
		difference = difference*(-1);
	}
	$('#container').highcharts({

        chart: {
            type: 'column'
        },

        title: {
            text: 'Total material views, grouped by weeks'
        },

        xAxis: {
            categories: categories
        },

        yAxis: {
            allowDecimals: false,
            min: 0,
            title: {
                text: 'Number of views'
            }
        },

        tooltip: {
            formatter: function () {
                return '<b>' + this.x + '</b><br/>' +
                    this.series.name + ': ' + this.y + '<br/>' +
                    'Total: ' + this.point.stackTotal;
            }
        },

        plotOptions: {
            column: {
                stacking: 'normal'
            }
        },

        series: [{
            name: 'Internal (you)',
            data: myinternal,
            stack: 'me'
        }, {
            name: 'External (you)',
            data: myexternal,
            stack: 'me'
        }, {
            name: 'Internal (average in course)',
            data: courseinternal,
            stack: 'course'
        }, {
            name: 'External (average in course)',
            data: courseexternal,
            stack: 'course'
        }]
    });
	$('.summary').css('display', 'inline');
	$('.summary .panel-body').html('You have accessed '+(mytotalexternal+mytotalinternal)+' learning materials during the seleceted period. '+mytotalexternal+' of these are external materials and '+mytotalinternal+' internal. That is '+(difference)+' materials '+moreorless+' than the average in current course.');



	var tablehead = '<h3>Most popular resources by course (does not include my views)</h3><table class="table popular-resources"><thead><tr><th>#</th><th>Name</th><th>Views</th></tr><thead>';
	var tablebody = '';
	var tablefooter = '</table>';

	var counter = 1;
	$.each(fortable.slice(0,10), function(index, value){
		tablebody+='<tr style="text-align: left;"><td>'+counter+'</td><td><a href="'+value.url+'">'+value.name+'</a></td><td>'+value.count+'</td></tr>';
		counter++;
	});

	$('.pop-resource-table').html(tablehead+tablebody+tablefooter);


}
function drawRelatedViewsTables(json){
	$('#container').html('');
	$('.pop-resource-table').html('');
	$('.summary').html('');
	var mytablehead= '<h3>Most popular resources by you</h3><table class="table my-visited-resources"><thead><tr><th>#</th><th>Name</th><th>Views</th></tr></thead>';
	var mytablebody = '';
	var mytablefooter = '</table>';

	var myvisiturls = [];
	$.each(json.myvisits, function(index, value){
		myvisiturls.push(value.url);
	});
	var counter = 1;
	$.each(json.myvisits.slice(0,10), function(index, value){
		mytablebody+='<tr style="text-align: left;"><td>'+counter+'</td><td><a href="'+value.url+'">'+value.name+'</a></td><td>'+value.count+'</td></tr>';
		counter++;
	});

	var othertablehead = '<h3>Other students also accessed these materials</h3><table class="table others-visited-resources"><thead><tr><th>#</th><th>Name</th><th>Views</th></tr></thead>';
	var othertablebody = '';
	counter = 1;
	$.each(json.othervisits.slice(0,10), function(index, value){
		othertablebody+='<tr style="text-align: left;"><td>'+counter+'</td><td class="visited-url"><a href="'+value.url+'">'+value.name+'</a></td><td>'+value.count+'</td></tr>';
		counter++;
	});
	$('#container').html(mytablehead+mytablebody+mytablefooter+othertablehead+othertablebody+mytablefooter);

	$('.others-visited-resources .visited-url a').each(function(index, value){
		if(myvisiturls.indexOf($(this).attr('href'))<0){
			$(this).parents('tr').addClass('havent-visited');
		}
	});

}






	});

})(jQuery);

