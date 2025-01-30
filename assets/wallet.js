
jQuery(document).ready(function ($) {
    // Delegate the event to the document for dynamically loaded content
    $(document).on('change', '#use_wallet_credit', function () {
        let useWallet = $(this).is(':checked') ? 1 : 0;

        jQuery.ajax({
            url: wallet_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'toggle_wallet_usage',
                use_wallet_credit: useWallet,
            },
            success: function (response) {
                console.log('AJAX success:', response);
                $(document.body).trigger('update_checkout');
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error, xhr.responseText);
            },
        });
    });

    // Rebind events after WooCommerce updates the checkout via AJAX
    $(document.body).on('updated_checkout', function () {
        console.log('Checkout updated, ensuring event bindings remain active');
    });

    $("#topUpAmount").on("input", function () {
        var value = $(this).val();
        
        // Allow only numbers (remove letters and symbols)
        if (!/^\d*$/.test(value)) {
            $("#error-message").text("لطفا مبلغ را به عدد وارد کنید").show();
            $(this).val(value.replace(/\D/g, "")); // Remove non-numeric characters
        } else {
            $("#error-message").hide();
        }
    });

});
