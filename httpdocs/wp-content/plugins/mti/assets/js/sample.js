var $j = jQuery.noConflict();

/*************** CART PAGE *********************/
// https://wordpress.stackexchange.com/questions/342148/woocommerce-documentation-list-of-js-events

var timeout;

$j(document).ready(function () {
	set_cart_form();

	$j('div.woocommerce').on('change keyup mouseup', 'input.qty, select.qty', function () {
		// keyup and mouseup for Firefox support
		if (timeout != undefined) clearTimeout(timeout); //cancel previously scheduled event
		if ($j(this).val() == '') return; //qty empty, instead of removing item from cart, do nothing
		timeout = setTimeout(function () {
			$j('span.update-cart-button i').show();
			$j('[name="update_cart"]').trigger('click');
		}, settings.cart_ajax_update_delay); // schedule update cart event with delay in miliseconds specified in plugin settings
	});
});

$j(document.body).on('updated_cart_totals', set_cart_form);

function set_cart_form() {
	$j('input[name="update_cart"]').hide();
	$j('span.update-cart-button i').hide();
	$j('span.update-cart-button i').addClass('rotate-spin');
	$j('span.update-cart-button i').css('color', settings.spinning_wheel_color);
}
