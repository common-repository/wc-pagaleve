(function($)
{
    'use strict';

    $(document).ready(function(){
        console.log('front');
    });

}(jQuery));;(function($)
{
	'use strict';

	$(document).ready(function() {
		window.addEventListener('message', function(event){

			if (event?.data?.action === 'pagaleve-checkout-finish') {
				try {
					const data = event?.data?.data;
					const hidden = document.querySelector('#pagaleve_order_id');

					if (!hidden) {
						return;
					}

					if (data?.reason === 'confirm' || data?.reason === 'cancel') {
						console.log(data);
						$.ajax({
							type: 'POST',
							url: wc_checkout_params.ajax_url,
							dataType: 'json',
							data: {
								'action': 'ajax_popup_access',
								'order_id': hidden.value
							},
							error: function(error) {
								console.log(error);
							}
						});

						if (data?.value !== this.location.href) {
							this.location.href = data?.value
						}
					}
				}
				
				catch (error) {
					console.log(error);
				}
			} 
		})
	});

	function delay() {
		const checkout = document.querySelector("#pagaleve-checkout");

		if (checkout && checkout.value) {
			setTimeout(function() {
				addModal(checkout);
			}, 200);
		}
	}

	function addModal(checkout) {
		window.postMessage({action: 'pagaleve-checkout-init', url: checkout.value }, '*');
	}
	
	

	document.addEventListener("DOMContentLoaded", () => {

		if (document.readyState == 'complete') {
			delay();
		} else {
			document.onreadystatechange = function () {
				if (document.readyState === "complete") {
					delay();
				}
			}
		}
	});

}(jQuery));
