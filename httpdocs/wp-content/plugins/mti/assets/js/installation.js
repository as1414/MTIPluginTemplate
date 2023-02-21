jQuery(function ($) {
	// allow admin notice to be closed and saved to db using AJAX
	$('#mti_notice_container a').click(function () {
		var data = {
			action: 'mti_notice_dismiss',
		};
		$.post(ajaxurl, data, function (response) {
			$('#mti_notice_container').fadeOut('slow');
		});
	});
});
