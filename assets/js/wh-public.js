/**
 * Wholesale Powerhouse - Public JavaScript
 * 
 * @package    Wholesale_Powerhouse
 * @subpackage Wholesale_Powerhouse/assets/js
 */

(function($) {
	'use strict';

	$(document).ready(function() {

		/**
		 * Registration form validation
		 */
		$('#wh-registration-form').on('submit', function(e) {
			var isValid = true;
			var errorMessages = [];

			// Username validation
			var username = $('#wh_username').val().trim();
			if (username.length < 3) {
				errorMessages.push('Username must be at least 3 characters long.');
				isValid = false;
			}

			// Email validation
			var email = $('#wh_email').val().trim();
			var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!emailRegex.test(email)) {
				errorMessages.push('Please enter a valid email address.');
				isValid = false;
			}

			// Password validation
			var password = $('#wh_password').val();
			if (password.length < 6) {
				errorMessages.push('Password must be at least 6 characters long.');
				isValid = false;
			}

			// Show errors if validation fails
			if (!isValid) {
				e.preventDefault();
				alert('Please fix the following errors:\n\n' + errorMessages.join('\n'));
				return false;
			}

			// Show loading state
			$(this).find('.wh-submit-btn').prop('disabled', true).text('Processing...');
		});

		/**
		 * Real-time password strength indicator
		 */
		$('#wh_password').on('keyup', function() {
			var password = $(this).val();
			var strength = 0;
			var strengthText = '';
			var strengthColor = '';

			if (password.length >= 6) strength++;
			if (password.length >= 10) strength++;
			if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
			if (/\d/.test(password)) strength++;
			if (/[^a-zA-Z0-9]/.test(password)) strength++;

			// Remove existing indicator
			$('.wh-password-strength').remove();

			if (password.length > 0) {
				if (strength < 2) {
					strengthText = 'Weak';
					strengthColor = '#d63638';
				} else if (strength < 4) {
					strengthText = 'Medium';
					strengthColor = '#dba617';
				} else {
					strengthText = 'Strong';
					strengthColor = '#00a32a';
				}

				$(this).after('<span class="wh-password-strength" style="color: ' + strengthColor + '; font-size: 12px; margin-top: 5px; display: block;">Password Strength: ' + strengthText + '</span>');
			}
		});

		/**
		 * Toggle tiered pricing details on product pages
		 */
		$('.wh-tiered-pricing-info').each(function() {
			$(this).css('animation', 'fadeIn 0.5s');
		});

		/**
		 * Highlight wholesale prices
		 */
		if ($('.wh-wholesale-badge').length) {
			$('.wh-wholesale-badge').css('animation', 'pulse 2s infinite');
		}

		/**
		 * Add private store login prompt
		 */
		$('.wh-login-required').each(function() {
			$(this).on('click', function(e) {
				if (confirm('You need to be logged in to view prices. Would you like to go to the login page?')) {
					window.location.href = $(this).data('login-url') || '/my-account/';
				}
			});
		});

		/**
		 * Enhanced quantity selector for tiered pricing
		 */
		if ($('.wh-tier-table').length) {
			$('.quantity input[type="number"]').on('change', function() {
				var qty = parseInt($(this).val()) || 1;
				var minQty = 0;
				
				// Find minimum quantity for tier pricing
				$('.wh-tier-table tbody tr').each(function() {
					var tierQty = parseInt($(this).find('td:first').text());
					if (!isNaN(tierQty)) {
						minQty = tierQty;
					}
				});

				// Highlight tier if quantity meets threshold
				if (qty >= minQty && minQty > 0) {
					$('.wh-tiered-pricing-info').css({
						'border-color': '#00a32a',
						'background': '#f0f9f0'
					});
				} else {
					$('.wh-tiered-pricing-info').css({
						'border-color': '#e0e0e0',
						'background': '#f7f7f7'
					});
				}
			});
		}

	});

	/**
	 * CSS animations
	 */
	var style = document.createElement('style');
	style.textContent = `
		@keyframes fadeIn {
			from { opacity: 0; transform: translateY(10px); }
			to { opacity: 1; transform: translateY(0); }
		}
		@keyframes pulse {
			0%, 100% { opacity: 1; }
			50% { opacity: 0.7; }
		}
	`;
	document.head.appendChild(style);

})(jQuery);
