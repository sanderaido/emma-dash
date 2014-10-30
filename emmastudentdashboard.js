(function($){	

	$(document).ready(function(){		
		var options = {format:'dd-mm-yyyy',weekStart:1,viewMode:0, minViewMode:0};
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
				$('.start').parents('.form-group').addClass('has-error');
				hasError = true;
			}else{
				if($('.start').parents('.form-group').hasClass('has-error')){
					$('.start').parents('.form-group').removeClass('has-error');
				}
			}
			if($('.end').val() === ''){
				$('.end').parents('.form-group').addClass('has-error');
				hasError = true;
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
				url: 'requests.php'
				
			});
			request.fail(function( jqXHR, textStatus ){
				alert('Request failed ' + textStatus );
				
			});			
			request.done(function(json){
				console.log(json);
				if(json.result == 'empty'){	
					$('#container').html('<div class="container col-sm-12 jumbo-contain"><div class="jumbotron"><h1>Sorry!</h1><p>There is no data for the <a href="'+courseurl+'">Selected course ('+coursename+')</a> during the selected time period</p></div></div>');
					$('.summary').css('display', 'none');
				}else{
											
					drawRelatedViewsTables(json);
					
				}
			});

			
		});



function drawRelatedViewsTables(json){
	
	var mytablehead= '<h3>Most popular resources by you</h3><table class="table"><thead><tr><th>#</th><th>Name</th><th>Page URL</th><th>Views</th></tr></thead>';
	var mytablebody = '';
	var mytablefooter = '</table>';
	var counter = 1;
	$.each(json.myvisits, function(index, value){
		mytablebody+='<tr style="text-align: left;"><td>'+counter+'</td><td>'+value.name+'</td><td>'+value.url+'</td><td>'+value.count+'</td></tr>';
		counter++;
	});

	var othertablehead = '<h3>Other students also accessed these materials</h3><table class="table"><thead><tr><th>#</th><th>Name</th><th>Page URL</th><th>Views</th></tr></thead>';
	var othertablebody = '';
	
	counter = 1;
	$.each(json.othervisits, function(index, value){
		othertablebody+='<tr style="text-align: left;"><td>'+counter+'</td><td>'+value.name+'</td><td>'+value.url+'</td><td>'+value.count+'</td></tr>';
		counter++;
	});
	$('#container').html(mytablehead+mytablebody+mytablefooter+othertablehead+othertablebody+mytablefooter);

}
		
			
			

		    
		
	});

})(jQuery);

