jQuery(document).ready(function ($) {
    const steps = $('.sproduct-step');
    const nextBtn = $('#next-btn');
    const prevBtn = $('#prev-btn');
    const submitBtn = $('#submit-btn');
    const form = $('#sproduct-main-form');
    let currentStep = parseInt(sessionStorage.getItem('currentStep')) || 0;
    let formData = JSON.parse(sessionStorage.getItem('sproductFormData')) || {};
    showStep(currentStep);

    // Populate form from sessionStorage
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
        if (validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
        }
        saveStepData();
    });
    // Handle Submit Button Click (Validate Current Step Only)
    submitBtn.on('click', function (e) {
        if (!validateStep(currentStep)) {
            e.preventDefault();  // Block submission if validation fails
        }
    });
    function validateStep(stepIndex) {
        let isValid = true;

        // Validate .is_required inputs in the current step
        steps.eq(stepIndex).find('.is_required input, .is_required textarea').each(function () {
            const inputValue = $(this).val().trim();
            if (inputValue === '') {
                showError($(this), 'پر کردن این فیلد اجباری است');
                isValid = false;
            } else {
                clearError($(this));
            }
        });

        return isValid;
    }
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
        sessionStorage.setItem('sproductFormData', JSON.stringify(formData));
        sessionStorage.setItem('currentStep', currentStep);
    }

    function showStep(stepIndex) {
        steps.each(function (index) {
            $(this).css('display', index === stepIndex ? 'block' : 'none');
        });
        prevBtn.prop('disabled', stepIndex === 0);
        nextBtn.css('display', stepIndex === steps.length - 1 ? 'none' : 'inline-block');
        submitBtn.css('display', stepIndex === steps.length - 1 ? 'inline-block' : 'none');
    }
    $('.sproduct-input input[type="tel"]').on('input', function () {
        const input = $(this);
        const value = input.val().trim();
        
        clearError(input);  // Clear previous errors

        // 1. Validate if it starts with 09
        if (!value.startsWith('09')) {
            showError(input, 'شماره موبایل باید با 09 شروع شود');
            return;
        }

        // 2. Validate if it contains non-digit characters
        if (!/^\d*$/.test(value)) {
            showError(input, 'لطفا فقط عدد وارد کنید');
            return;
        }

        // 3. Validate length (exactly 11 digits)
        if (value.length > 0 && value.length !== 11) {
            showError(input, 'شماره موبایل باید دقیقا 11 رقم باشد');
        }
    });
    form.on('submit', function (e) {
        let isValid = true;
    
        $('.sproduct-input input[type="text"], .sproduct-input input[type="email"], textarea').each(function () {
            const isRequired = $(this).closest('.input-item').find('.required-checkbox').is(':checked');
            const inputValue = $(this).val().trim();
    
            if (isRequired && inputValue === '') {
                showError($(this), 'پر کردن این فیلد اجباری است');
                isValid = false;
            } else {
                clearError($(this));
            }
        });

        $('.sproduct-input input[name*="national_code"]').each(function () {
            const pattern = /^[0-9]{10}$/;
            const inputValue = $(this).val().trim();
    
            if (inputValue !== '' && !pattern.test(inputValue)) {
                showError($(this), 'کد ملی باید دقیقاً 10 رقم باشد.');
                isValid = false;
            } else {
                clearError($(this));
            }
        });

        $('.sproduct-input input[name*="post_code"]').each(function () {
            const pattern = /^[0-9]{10}$/;
            const inputValue = $(this).val().trim();
    
            if (inputValue !== '' && !pattern.test(inputValue)) {
                showError($(this), 'کد پستی باید دقیقاً 10 رقم باشد.');
                isValid = false;
            } else {
                clearError($(this));
            }
        });

        $('.sproduct-input input[type="tel"]').each(function () {
            const pattern = /^09[0-9]{9}$/;
            const inputValue = $(this).val().trim();
    
            if (inputValue !== '' && !pattern.test(inputValue)) {
                showError($(this), 'شماره موبایل باید با 09 شروع شود و 11 رقم باشد.');
                isValid = false;
            } else {
                clearError($(this));
            }
        });

        $('.sproduct-input input[name*="telephone"]').each(function () {
            const pattern = /^[0-9]{8}$/;
            const inputValue = $(this).val().trim();
    
            if (inputValue !== '' && !pattern.test(inputValue)) {
                showError($(this), 'شماره تلفن باید دقیقاً 8 رقم باشد.');
                isValid = false;
            } else {
                clearError($(this));
            }
        });

        $('.sproduct-input input[type="email"]').each(function () {
            const pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            const inputValue = $(this).val().trim();
    
            if (inputValue !== '' && !pattern.test(inputValue)) {
                showError($(this), 'ایمیل وارد شده معتبر نیست.');
                isValid = false;
            } else {
                clearError($(this));
            }
        });

        if (!isValid) {
            e.preventDefault();
            return;
        }
    
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
                alert('فرم با موفقیت ارسال شد!');
                sessionStorage.removeItem('sproductFormData');
                sessionStorage.removeItem('currentStep');
            },
            error: function () {
                alert('خطا در ارسال فرم.');
            }
        });
    });

    function showError(input, message) {
        clearError(input);  // Prevent duplicate messages
        const errorElement = `<div class="input-error" style="color: red; margin-top: 5px;">${message}</div>`;
        input.addClass('input-error-border');
        input.after(errorElement);
    }

    function clearError(input) {
        input.removeClass('input-error-border');
        input.next('.input-error').remove();
    }
    $('.sproduct-input input[type="tel"]').on('keypress', function (e) {
        const charCode = e.which ? e.which : e.keyCode;
        
        // Prevent typing non-numeric characters
        if (charCode < 48 || charCode > 57) {
            e.preventDefault();
            showError($(this), 'لطفا فقط عدد وارد کنید');
        }
    });
    $('.sproduct-input input, .sproduct-input textarea').on('input', function () {
        const inputValue = $(this).val().trim();
        if (inputValue !== '') {
            clearError($(this));
        }
    });
});
