/**
 * Front-end Script
 */

window.GFterawallet = null;


(function ($) {

	GFterawallet = function (args) {

		for (var prop in args) {
			if (args.hasOwnProperty(prop))
				this[prop] = args[prop];
		}

		this.form = null;

		this.activeFeed = null;

		this.GFCCField = null;

		this.terawalletResponse = null;

		this.hasPaymentIntent = false;

		this.init = function () {

			if (!this.isCreditCardOnPage()) {
				if (this.terawallet_payment === 'terawallet.js' || (this.terawallet_payment === 'elements' && ! $('#gf_terawallet_response').length)) {
					return;
				}
			}

			var GFterawalletObj = this, activeFeed = null, feedActivated = false, hidePostalCode = false, apiKey = this.apiKey;

			this.form = $('#gform_' + this.formId);
			this.GFCCField = $('#input_' + this.formId + '_' + this.ccFieldId + '_1');
			
			gform.addAction('gform_frontend_feeds_evaluated', function (feeds, formId) {
				if ( formId !== GFterawalletObj.formId ) {
					return;
				}

				activeFeed = null;
				feedActivated = false;
				hidePostalCode = false;

				for (var i = 0; i < Object.keys(feeds).length; i++) {
					if (feeds[i].addonSlug === 'gravityformsterawallet' && feeds[i].isActivated) {
						feedActivated = true;

						for (var j = 0; j < Object.keys(GFterawalletObj.feeds).length; j++) {
							if (GFterawalletObj.feeds[j].feedId === feeds[i].feedId) {
								activeFeed = GFterawalletObj.feeds[j];

								break;
							}
						}

						apiKey = activeFeed.hasOwnProperty('apiKey') ? activeFeed.apiKey : GFterawalletObj.apiKey;
						GFterawalletObj.activeFeed = activeFeed;

						switch (GFterawalletObj.terawallet_payment) {
							case 'elements':
								terawallet = terawallet(apiKey);
								elements = terawallet.elements();

								hidePostalCode = activeFeed.address_zip !== '';

								// If terawallet Card is already on the page (AJAX failed validation, or switch frontend feeds),
								// Destroy the card field so we can re-initiate it.
								if ( card != null && card.hasOwnProperty( '_destroyed' ) && card._destroyed === false ) {
									card.destroy();
								}

								// Clear card field errors before initiate it.
								if (GFterawalletObj.GFCCField.next('.validation_message').length) {
									GFterawalletObj.GFCCField.next('.validation_message').html('');
								}

								card = elements.create(
									'card',
									{
										classes: GFterawalletObj.cardClasses,
										style: GFterawalletObj.cardStyle,
										hidePostalCode: hidePostalCode
									}
								);

								if ( $('.gform_terawallet_requires_action').length ) {
									if ( $('.ginput_container_creditcard > div').length === 2 ) {
										// Cardholder name enabled.
										$('.ginput_container_creditcard > div:last').hide();
										$('.ginput_container_creditcard > div:first').html('<p><strong>' + gforms_terawallet_frontend_strings.requires_action + '</strong></p>');
									} else {
										$('.ginput_container_creditcard').html('<p><strong>' + gforms_terawallet_frontend_strings.requires_action + '</strong></p>');
									}
									GFterawalletObj.scaActionHandler(terawallet, formId);
								} else {
									card.mount('#' + GFterawalletObj.GFCCField.attr('id'));

									card.on('change', function (event) {
										GFterawalletObj.displayterawalletCardError(event);
									});
								}
								break;
							case 'terawallet.js':
								terawallet.setPublishableKey(apiKey);
								break;
						}

						break; // allow only one active feed.
					}
				}

				if (!feedActivated) {
					if (GFterawalletObj.terawallet_payment === 'elements') {
						if ( elements != null && card === elements.getElement( 'card' ) ) {
							card.destroy();
						}

						if (!GFterawalletObj.GFCCField.next('.validation_message').length) {
							GFterawalletObj.GFCCField.after('<div class="gfield_description validation_message"></div>');
						}

						var cardErrors = GFterawalletObj.GFCCField.next('.validation_message');
						cardErrors.html( gforms_terawallet_frontend_strings.no_active_frontend_feed );

						wp.a11y.speak( gforms_terawallet_frontend_strings.no_active_frontend_feed );
					}

					// remove terawallet fields and form status when terawallet feed deactivated
					GFterawalletObj.resetterawalletStatus(GFterawalletObj.form, formId, GFterawalletObj.isLastPage());
					apiKey = GFterawalletObj.apiKey;
					GFterawalletObj.activeFeed = null;
				}
			});

			switch (this.terawallet_payment) {
				case 'elements':
					var terawallet = null,
						elements = null,
						card = null,
						skipElementsHandler = false;

					if ( $('#gf_terawallet_response').length ) {
						this.terawalletResponse = JSON.parse($('#gf_terawallet_response').val());

						if ( this.terawalletResponse.hasOwnProperty('client_secret') ) {
							this.hasPaymentIntent = true;
						}
					}
					break;
			}

			// bind terawallet functionality to submit event
			$('#gform_' + this.formId).on('submit', function (event) {
				// by checking if GFCCField is hidden, we can continue to the next page in a multi-page form
				if (!feedActivated || $(this).data('gfterawalletsubmitting') || $('#gform_save_' + GFterawalletObj.formId).val() == 1 || (!GFterawalletObj.isLastPage() && 'elements' !== GFterawalletObj.terawallet_payment) || gformIsHidden(GFterawalletObj.GFCCField) || GFterawalletObj.maybeHitRateLimits() || GFterawalletObj.invisibleCaptchaPending()) {
					return;
				} else {
					event.preventDefault();
					$(this).data('gfterawalletsubmitting', true);
					GFterawalletObj.maybeAddSpinner();
				}

				switch (GFterawalletObj.terawallet_payment) {
					case 'elements':
						GFterawalletObj.form = $(this);

						if ( activeFeed.paymentAmount === 'form_total' ) {
							// Set priority to 51 so it will be triggered after the coupons add-on
							gform.addFilter('gform_product_total', function (total, formId) {
								window['gform_terawallet_amount_' + formId] = total;
								return total;
							}, 51);

							gformCalculateTotalPrice(GFterawalletObj.formId);
						}

						GFterawalletObj.updatePaymentAmount();

						// don't create card token if clicking on the Previous button.
						var sourcePage = parseInt($('#gform_source_page_number_' + GFterawalletObj.formId).val(), 10),
						    targetPage = parseInt($('#gform_target_page_number_' + GFterawalletObj.formId).val(), 10);
						if ((sourcePage > targetPage && targetPage !== 0) || window['gform_terawallet_amount_' + GFterawalletObj.formId] === 0) {
							skipElementsHandler = true;
						}

						if ((GFterawalletObj.isLastPage() && !GFterawalletObj.isCreditCardOnPage()) || gformIsHidden(GFterawalletObj.GFCCField) || skipElementsHandler) {
							$(this).submit();
							return;
						}

						if ( activeFeed.type === 'product' ) {
							// Create a new payment method when every time the terawallet Elements is resubmitted.
							GFterawalletObj.createPaymentMethod(terawallet, card);
						} else {
							GFterawalletObj.createToken(terawallet, card);
						}
						break;
					case 'terawallet.js':
						var form = $(this),
							ccInputPrefix = 'input_' + GFterawalletObj.formId + '_' + GFterawalletObj.ccFieldId + '_',
							cc = {
								number: form.find('#' + ccInputPrefix + '1').val(),
								exp_month: form.find('#' + ccInputPrefix + '2_month').val(),
								exp_year: form.find('#' + ccInputPrefix + '2_year').val(),
								cvc: form.find('#' + ccInputPrefix + '3').val(),
								name: form.find('#' + ccInputPrefix + '5').val()
							};


						GFterawalletObj.form = form;

						terawallet.card.createToken(cc, function (status, response) {
							GFterawalletObj.responseHandler(status, response);
						});
						break;
				}

			});

		};

		this.getBillingAddressMergeTag = function (field) {
			if (field === '') {
				return '';
			} else {
				return '{:' + field + ':value}';
			}
		};

		this.responseHandler = function (status, response) {

			var form = this.form,
				ccInputPrefix = 'input_' + this.formId + '_' + this.ccFieldId + '_',
				ccInputSuffixes = ['1', '2_month', '2_year', '3', '5'];

			// remove "name" attribute from credit card inputs
			for (var i = 0; i < ccInputSuffixes.length; i++) {

				var input = form.find('#' + ccInputPrefix + ccInputSuffixes[i]);

				if (ccInputSuffixes[i] == '1') {

					var ccNumber = $.trim(input.val()),
						cardType = gformFindCardType(ccNumber);

					if (typeof this.cardLabels[cardType] != 'undefined')
						cardType = this.cardLabels[cardType];

					form.append($('<input type="hidden" name="terawallet_credit_card_last_four" />').val(ccNumber.slice(-4)));
					form.append($('<input type="hidden" name="terawallet_credit_card_type" />').val(cardType));

				}

				// name attribute is now removed from markup in GFterawallet::add_terawallet_inputs()
				//input.attr( 'name', null );

			}

			// append terawallet.js response
			form.append($('<input type="hidden" name="terawallet_response" />').val($.toJSON(response)));

			// submit the form
			form.submit();

		};

		this.elementsResponseHandler = function (response) {

			var form = this.form,
				GFterawalletObj = this,
				activeFeed = this.activeFeed,
			    currency = gform.applyFilters( 'gform_terawallet_currency', this.currency, this.formId ),
				amount = (0 === gf_global.gf_currency_config.decimals) ? window['gform_terawallet_amount_' + this.formId] : gformRoundPrice( window['gform_terawallet_amount_' + this.formId] * 100 );

			if (response.error) {
				// display error below the card field.
				this.displayterawalletCardError(response);
				// when terawallet response contains errors, stay on page
				// but remove some elements so the form can be submitted again
				// also remove last_4 and card type if that already exists (this happens when people navigate back to previous page and submit an empty CC field)
				this.resetterawalletStatus(form, this.formId, this.isLastPage());

				return;
			}

			if (!this.hasPaymentIntent) {
				// append terawallet.js response
				if (!$('#gf_terawallet_response').length) {
					form.append($('<input type="hidden" name="terawallet_response" id="gf_terawallet_response" />').val($.toJSON(response)));
				} else {
					$('#gf_terawallet_response').val($.toJSON(response));
				}

				if (activeFeed.type === 'product') {
					//set last 4
					form.append($('<input type="hidden" name="terawallet_credit_card_last_four" id="gf_terawallet_credit_card_last_four" />').val(response.paymentMethod.card.last4));

					// set card type
					form.append($('<input type="hidden" name="terawallet_credit_card_type" id="terawallet_credit_card_type" />').val(response.paymentMethod.card.brand));
					// Create server side payment intent.
					$.ajax({
						async: false,
						url: gforms_terawallet_frontend_strings.ajaxurl,
						dataType: 'json',
						method: 'POST',
						data: {
							action: "gfterawallet_create_payment_intent",
							nonce: gforms_terawallet_frontend_strings.create_payment_intent_nonce,
							payment_method: response.paymentMethod,
							currency: currency,
							amount: amount,
							feed_id: activeFeed.feedId
						},
						success: function (response) {
							if (response.success) {
								// populate the terawallet_response field again.
								if (!$('#gf_terawallet_response').length) {
									form.append($('<input type="hidden" name="terawallet_response" id="gf_terawallet_response" />').val($.toJSON(response.data)));
								} else {
									$('#gf_terawallet_response').val($.toJSON(response.data));
								}
								// submit the form
								form.submit();
							} else {
								response.error = response.data;
								delete response.data;
								GFterawalletObj.displayterawalletCardError(response);
								GFterawalletObj.resetterawalletStatus(form, GFterawalletObj.formId, GFterawalletObj.isLastPage());
							}
						}
					});
				} else {
					form.append($('<input type="hidden" name="terawallet_credit_card_last_four" id="gf_terawallet_credit_card_last_four" />').val(response.token.card.last4));
					form.append($('<input type="hidden" name="terawallet_credit_card_type" id="terawallet_credit_card_type" />').val(response.token.card.brand));
					form.submit();
				}
			} else {
				if (activeFeed.type === 'product') {
					if (response.hasOwnProperty('paymentMethod')) {
						$('#gf_terawallet_credit_card_last_four').val(response.paymentMethod.card.last4);
						$('#terawallet_credit_card_type').val(response.paymentMethod.card.brand);

						$.ajax({
							async: false,
							url: gforms_terawallet_frontend_strings.ajaxurl,
							dataType: 'json',
							method: 'POST',
							data: {
								action: "gfterawallet_update_payment_intent",
								nonce: gforms_terawallet_frontend_strings.create_payment_intent_nonce,
								payment_intent: response.id,
								payment_method: response.paymentMethod,
								currency: currency,
								amount: amount,
								feed_id: activeFeed.feedId
							},
							success: function (response) {
								if (response.success) {
									$('#gf_terawallet_response').val($.toJSON(response.data));
									form.submit();
								} else {
									response.error = response.data;
									delete response.data;
									GFterawalletObj.displayterawalletCardError(response);
									GFterawalletObj.resetterawalletStatus(form, GFterawalletObj.formId, GFterawalletObj.isLastPage());
								}
							}
						});
					} else if (response.hasOwnProperty('amount')) {
						form.submit();
					}
				} else {
					var currentResponse = JSON.parse($('#gf_terawallet_response').val());
					currentResponse.updatedToken = response.token.id;

					$('#gf_terawallet_response').val($.toJSON(currentResponse));

					form.append($('<input type="hidden" name="terawallet_credit_card_last_four" id="gf_terawallet_credit_card_last_four" />').val(response.token.card.last4));
					form.append($('<input type="hidden" name="terawallet_credit_card_type" id="terawallet_credit_card_type" />').val(response.token.card.brand));
					form.submit();
				}
			}
		};

		this.scaActionHandler = function (terawallet, formId) {
			if ( ! $('#gform_' + formId).data('gfterawalletscaauth') ) {
				$('#gform_' + formId).data('gfterawalletscaauth', true);

				var GFterawalletObj = this, response = JSON.parse($('#gf_terawallet_response').val());
				if (this.activeFeed.type === 'product') {
					// Prevent the 3D secure auth from appearing twice, so we need to check if the intent status first.
					terawallet.retrievePaymentIntent(
						response.client_secret
					).then(function(result) {
						if ( result.paymentIntent.status === 'requires_action' ) {
							terawallet.handleCardAction(
								response.client_secret
							).then(function(result) {
								var currentResponse = JSON.parse($('#gf_terawallet_response').val());
								currentResponse.scaSuccess = true;

								$('#gf_terawallet_response').val($.toJSON(currentResponse));

								GFterawalletObj.maybeAddSpinner();
								$('#gform_' + formId).data('gfterawalletscaauth', false);
								$('#gform_' + formId).data('gfterawalletsubmitting', true).submit();
							});
						}
					});
				} else {
					terawallet.retrievePaymentIntent(
						response.client_secret
					).then(function(result) {
						if ( result.paymentIntent.status === 'requires_action' ) {
							terawallet.handleCardPayment(
								response.client_secret
							).then(function(result) {
								GFterawalletObj.maybeAddSpinner();
								$('#gform_' + formId).data('gfterawalletscaauth', false);
								$('#gform_' + formId).data('gfterawalletsubmitting', true).submit();
							});
						}
					});
				}
			}
		};

		this.isLastPage = function () {

			var targetPageInput = $('#gform_target_page_number_' + this.formId);
			if (targetPageInput.length > 0)
				return targetPageInput.val() == 0;

			return true;
		};

		this.isCreditCardOnPage = function () {

			var currentPage = this.getCurrentPageNumber();

			// if current page is false or no credit card page number, assume this is not a multi-page form
			if (!this.ccPage || !currentPage)
				return true;

			return this.ccPage == currentPage;
		};

		this.getCurrentPageNumber = function () {
			var currentPageInput = $('#gform_source_page_number_' + this.formId);
			return currentPageInput.length > 0 ? currentPageInput.val() : false;
		};

		this.maybeAddSpinner = function () {
			if (this.isAjax)
				return;

			if (typeof gformAddSpinner === 'function') {
				gformAddSpinner(this.formId);
			} else {
				// Can be removed after min Gravity Forms version passes 2.1.3.2.
				var formId = this.formId;

				if (jQuery('#gform_ajax_spinner_' + formId).length == 0) {
					var spinnerUrl = gform.applyFilters('gform_spinner_url', gf_global.spinnerUrl, formId),
						$spinnerTarget = gform.applyFilters('gform_spinner_target_elem', jQuery('#gform_submit_button_' + formId + ', #gform_wrapper_' + formId + ' .gform_next_button, #gform_send_resume_link_button_' + formId), formId);
					$spinnerTarget.after('<img id="gform_ajax_spinner_' + formId + '"  class="gform_ajax_spinner" src="' + spinnerUrl + '" alt="" />');
				}
			}

		};

		this.resetterawalletStatus = function(form, formId, isLastPage) {
			$('#gf_terawallet_response, #gf_terawallet_credit_card_last_four, #terawallet_credit_card_type').remove();
			form.data('gfterawalletsubmitting', false);
            $('#gform_ajax_spinner_' + formId).remove();

			// must do this or the form cannot be submitted again
			if (isLastPage) {
				window["gf_submitting_" + formId] = false;
			}
		};

		this.displayterawalletCardError = function (event) {
			if (!this.GFCCField.next('.validation_message').length) {
				this.GFCCField.after('<div class="gfield_description validation_message"></div>');
			}

			var cardErrors = this.GFCCField.next('.validation_message');

			if (event.error) {
				cardErrors.html(event.error.message);

				wp.a11y.speak( event.error.message, 'assertive' );
				// Hide spinner.
				if ( $('#gform_ajax_spinner_' + this.formId).length > 0 ) {
					$('#gform_ajax_spinner_' + this.formId).remove();
				}
			} else {
				cardErrors.html('');
			}
		};

		this.updatePaymentAmount = function () {
			var formId = this.formId, activeFeed = this.activeFeed;

			if (activeFeed.paymentAmount !== 'form_total') {
				var price = GFMergeTag.getMergeTagValue(formId, activeFeed.paymentAmount, ':price'),
					qty = GFMergeTag.getMergeTagValue(formId, activeFeed.paymentAmount, ':qty');

				if (typeof price === 'string') {
					price = GFMergeTag.getMergeTagValue(formId, activeFeed.paymentAmount + '.2', ':price');
					qty = GFMergeTag.getMergeTagValue(formId, activeFeed.paymentAmount + '.3', ':qty');
				}

				window['gform_terawallet_amount_' + formId] = price * qty;
			}

			if (activeFeed.hasOwnProperty('setupFee')) {
				price = GFMergeTag.getMergeTagValue(formId, activeFeed.setupFee, ':price');
				qty = GFMergeTag.getMergeTagValue(formId, activeFeed.setupFee, ':qty');

				if (typeof price === 'string') {
					price = GFMergeTag.getMergeTagValue(formId, activeFeed.setupFee + '.2', ':price');
					qty = GFMergeTag.getMergeTagValue(formId, activeFeed.setupFee + '.3', ':qty');
				}

				window['gform_terawallet_amount_' + formId] += price * qty;
			}
		};

		this.createToken = function (terawallet, card) {
			var GFterawalletObj = this, activeFeed = this.activeFeed;
				cardholderName = $( '#input_' + this.formId + '_' + this.ccFieldId + '_5' ).val(),
				tokenData = {
					name: cardholderName,
					address_line1: GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_line1)),
					address_line2: GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_line2)),
					address_city: GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_city)),
					address_state: GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_state)),
					address_zip: GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_zip)),
					address_country: GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_country)),
					currency: gform.applyFilters( 'gform_terawallet_currency', this.currency, this.formId )
				};
			terawallet.createToken(card, tokenData).then(function (response) {
				GFterawalletObj.elementsResponseHandler(response);
			});
		};

		this.createPaymentMethod = function (terawallet, card, country) {
			var GFterawalletObj = this, activeFeed = this.activeFeed, countryFieldValue = '';

			if ( activeFeed.address_country !== '' ) {
				countryFieldValue = GFMergeTag.replaceMergeTags(GFterawalletObj.formId, GFterawalletObj.getBillingAddressMergeTag(activeFeed.address_country));
			}

			if (countryFieldValue !== '' && ( typeof country === 'undefined' || country === '' )) {
                $.ajax({
                    async: false,
                    url: gforms_terawallet_frontend_strings.ajaxurl,
                    dataType: 'json',
                    method: 'POST',
                    data: {
                        action: "gfterawallet_get_country_code",
                        nonce: gforms_terawallet_frontend_strings.create_payment_intent_nonce,
                        country: countryFieldValue,
                        feed_id: activeFeed.feedId
                    },
                    success: function (response) {
                        if (response.success) {
                            GFterawalletObj.createPaymentMethod(terawallet, card, response.data.code);
                        }
                    }
                });
            } else {
                var cardholderName = $('#input_' + this.formId + '_' + this.ccFieldId + '_5').val(),
					line1 = GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_line1)),
					line2 = GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_line2)),
					city = GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_city)),
					state = GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_state)),
					postal_code = GFMergeTag.replaceMergeTags(this.formId, this.getBillingAddressMergeTag(activeFeed.address_zip)),
                    data = { billing_details: { name: null, address: {} } };

                if (cardholderName !== '') {
                	data.billing_details.name = cardholderName;
				}
				if (line1 !== '') {
					data.billing_details.address.line1 = line1;
				}
				if (line2 !== '') {
					data.billing_details.address.line2 = line2;
				}
				if (city !== '') {
					data.billing_details.address.city = city;
				}
				if (state !== '') {
					data.billing_details.address.state = state;
				}
				if (postal_code !== '') {
					data.billing_details.address.postal_code = postal_code;
				}
				if (country !== '') {
					data.billing_details.address.country = country;
				}

				if (data.billing_details.name === null) {
					delete data.billing_details.name;
				}
				if (data.billing_details.address === {}) {
					delete data.billing_details.address;
				}

				terawallet.createPaymentMethod('card', card, data).then(function (response) {
					if (GFterawalletObj.terawalletResponse !== null) {
						response.id = GFterawalletObj.terawalletResponse.id;
						response.client_secret = GFterawalletObj.terawalletResponse.client_secret;
					}

					GFterawalletObj.elementsResponseHandler(response);
				});
            }
		};

		this.maybeHitRateLimits = function() {
			if (this.hasOwnProperty('cardErrorCount')) {
				if (this.cardErrorCount >= 5) {
					return true;
				}
			}

			return false;
		};

		this.invisibleCaptchaPending = function () {
			var form = this.form,
				reCaptcha = form.find('.ginput_recaptcha');

			if (!reCaptcha.length || reCaptcha.data('size') !== 'invisible') {
				return false;
			}

			var reCaptchaResponse = reCaptcha.find('.g-recaptcha-response');

			return !(reCaptchaResponse.length && reCaptchaResponse.val());
		}

		this.init();

	}

})(jQuery);