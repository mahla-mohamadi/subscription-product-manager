jQuery(document).ready(function ($) {
    const steps = $('.sproduct-step');
    const nextBtn = $('#next-btn');
    const prevBtn = $('#prev-btn');
    const submitBtn = $('#submit-btn');
    const form = $('#sproduct-main-form');

    let currentStep = parseInt(localStorage.getItem('currentStep')) || 0;
    let formData = JSON.parse(localStorage.getItem('sproductFormData')) || {};

    showStep(currentStep);

    // Populate form from localStorage
    $.each(formData, function (name, value) {
        const input = $(`[name="${name}"]`);
        if (input.length) {
            input.val(value);
            if (input.attr('type') === 'checkbox' && value === "1") {
                input.prop('checked', true);
            }
        }
    });

    nextBtn.on('click', function () {
        saveStepData();
        if (currentStep < steps.length - 1) {
            currentStep++;
            showStep(currentStep);
        }
    });

    prevBtn.on('click', function () {
        saveStepData();
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
    });

    function saveStepData() {
        const inputs = steps.eq(currentStep).find('input');
        inputs.each(function () {
            const input = $(this);
            formData[input.attr('name')] = input.attr('type') === 'checkbox' 
                ? input.is(':checked') ? "1" : "0" 
                : input.val();
        });
        localStorage.setItem('sproductFormData', JSON.stringify(formData));
        localStorage.setItem('currentStep', currentStep);
    }

    function showStep(stepIndex) {
        steps.each(function (index) {
            $(this).css('display', index === stepIndex ? 'block' : 'none');
        });
        prevBtn.prop('disabled', stepIndex === 0);
        nextBtn.css('display', stepIndex === steps.length - 1 ? 'none' : 'inline-block');
        submitBtn.css('display', stepIndex === steps.length - 1 ? 'inline-block' : 'none');
    }

    form.on('submit', function (e) {
        e.preventDefault();
        saveStepData();

        $.ajax({
            url: sproductAjax.ajaxurl,
            method: 'POST',
            data: {
                action: 'sproduct_submit_form',
                form_data: JSON.stringify(formData),
                post_id: form.closest('#sproduct-form-frontend').data('post-id'),
                nonce: sproductAjax.nonce
            },
            success: function (response) {
                alert('Form submitted successfully!');
                localStorage.removeItem('sproductFormData');
                localStorage.removeItem('currentStep');
            },
            error: function () {
                alert('Error submitting form.');
            }
        });
    });
});
