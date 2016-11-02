Event.observe(window, 'load', function() {
    if(Review != undefined) {
        Review.prototype.nextStep = function (transport) {
            var parentNextStep = Review.nextStep;
            try {
                parentNextStep(transport);
                try {
                    response = eval('(' + transport.responseText + ')');
                }
                catch (e) {
                    response = {};
                }
                if (response.allowed_payment_methods) {
                    //paymentCssSelector is declared in design/frontend/base/default/template/scoring/onepage.phtml
                    $$(paymentCssSelector).invoke('hide');
                    for (var i = 0; i < response.allowed_payment_methods.length; i++) {
                        paymentObj = $('p_method_' + response.allowed_payment_methods[i]);
                        if (paymentObj) {
                            paymentObj.ancestors()[0].show();
                            paymentObj.ancestors()[0].ancestors()[0].show();
                        }
                    }
                    $$(paymentCssSelector).each(function (payment_container) {
                        if (payment_container.style.display == 'none') {
                            //payment_container.hide();
                        }
                    });
                    //$('p_method_' + response.allowed_payment_methods[0]).checked = true;
                    $('p_method_' + response.allowed_payment_methods[0]).click();
                }
            }
        };
    }
});
