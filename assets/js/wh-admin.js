/**
 * Wholesale Powerhouse - Admin JavaScript
 * 
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/assets/js
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		
		/**
		 * Product edit page - Wholesale tab enhancements
		 */
		if ($('#wholesale_product_data').length) {
			// Add tooltip info
			$('.wh-price-field').on('focus', function() {
				$(this).closest('.form-field').addClass('active');
			}).on('blur', function() {
				$(this).closest('.form-field').removeClass('active');
			});

			// Validate tier pricing inputs
			$('#_wh_tier_min_qty, #_wh_tier_discount_percent').on('change', function() {
				var minQty = parseInt($('#_wh_tier_min_qty').val()) || 0;
				var discount = parseFloat($('#_wh_tier_discount_percent').val()) || 0;

				if ((minQty > 0 && discount === 0) || (minQty === 0 && discount > 0)) {
					alert('Please fill both Minimum Quantity and Discount Percentage for tiered pricing, or leave both empty.');
				}
			});
		}

		/**
		 * Settings page - Role discount validation
		 */
		$('[id^="wh_role_discount_"]').on('change', function() {
			var value = parseFloat($(this).val());
			
			if (value < 0 || value > 100) {
				alert('Discount percentage must be between 0 and 100.');
				$(this).val(0);
			}
		});

		/**
		 * User profile - Approve wholesale customer confirmation
		 */
		$('#wh_approve_customer').on('change', function() {
			if ($(this).is(':checked')) {
				if (!confirm('Are you sure you want to approve this wholesale customer? They will be assigned the Bronze wholesale role.')) {
					$(this).prop('checked', false);
				}
			}
		});

		/**
		 * Bulk actions confirmation
		 */
		$('#doaction, #doaction2').on('click', function(e) {
			var action = $(this).siblings('select').val();
			
			if (action && action.indexOf('wh_assign_') === 0) {
				var checkedUsers = $('input[name="users[]"]:checked').length;
				
				if (checkedUsers === 0) {
					e.preventDefault();
					alert('Please select at least one user.');
					return false;
				}

				var roleName = action.replace('wh_assign_', '').replace('_', ' ');
				var confirmMsg = 'Are you sure you want to assign the ' + roleName + ' role to ' + checkedUsers + ' user(s)?';
				
				if (!confirm(confirmMsg)) {
					e.preventDefault();
					return false;
				}
			}
		});

		/**
		 * Show success message after bulk action
		 */
		if (window.location.search.indexOf('wh_bulk_assigned') !== -1) {
			var urlParams = new URLSearchParams(window.location.search);
			var count = urlParams.get('wh_bulk_assigned');
			
			if (count) {
				var message = count + ' user(s) have been assigned the wholesale role successfully.';
				$('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>')
					.insertAfter('.wp-header-end');
			}
		}

		/**
		 * Product list - Highlight wholesale-only products
		 */
		$('.column-wholesale').each(function() {
			if ($(this).find('.dashicons-lock').length) {
				$(this).closest('tr').css('background-color', '#fff9e6');
			}
		});

	});

})(jQuery);
