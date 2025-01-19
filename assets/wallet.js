
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
});
