(function($){	

	$('#navigation a').click(function (e) {
    	e.preventDefault()
    	$(this).tab('show')
  	});

})(jQuery);