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
    $('.sproduct-input input[type="telephone"]').on('input', function () {
        const input = $(this);
        const value = input.val().trim();
        clearError(input);
    
        // 1. Allow only numbers
        if (!/^\d*$/.test(value)) {
            showError(input, 'You must enter only numbers.');
            input.val(value.replace(/\D/g, ''));  // Remove non-digit characters
        }
    
        // 2. Enforce exactly 8 digits
        if (value.length > 8) {
            showError(input, 'The phone number is wrong.');
            input.val(value.substring(0, 8));  // Limit to 8 digits
        }
    });
    $('.sproduct-input input[type="telephone"]').on('keypress', function (e) {
        const charCode = e.which ? e.which : e.keyCode;
        if (charCode < 48 || charCode > 57) {
            e.preventDefault();
            showError($(this), 'You must enter only numbers.');
        }
        // Prevent typing if already 8 digits
        if ($(this).val().length >= 8) {
            e.preventDefault();
            showError($(this), 'The phone number cannot exceed 8 digits.');
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
        // Phone Number Validation (inside validateStep)
        steps.eq(stepIndex).find('input[type="tel"]').each(function () {
            const value = $(this).val().trim();
            const pattern = /^09[0-9]{9}$/;
            const isRequired = $(this).closest('.is_required').length > 0;

            // Required validation
            if (isRequired && value === '') {
                showError($(this), 'پر کردن این فیلد اجباری است');
                isValid = false;
            } 
            // Format validation only if field is not empty
            else if (value !== '' && !pattern.test(value)) {
                showError($(this), 'شماره موبایل باید با 09 شروع شود و 11 رقم باشد.');
                isValid = false;
            } else {
                clearError($(this));
            }
        });
        // Email Validation (Latin and required @ and .)
        steps.eq(stepIndex).find('input[type="email"]').each(function () {
            const value = $(this).val().trim();
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            const isRequired = $(this).closest('.is_required').length > 0;
            const farsiPattern = /[آ-ی]/;  // Detects Farsi characters

            // Check for required email
            if (isRequired && value === '') {
                showError($(this), 'پر کردن این فیلد اجباری است');
                isValid = false;
            } 
            // Farsi character check
            else if (farsiPattern.test(value)) {
                showError($(this), 'Enter your email in Latin');
                isValid = false;
            } 
            // Check for @ and . in email
            else if (isRequired && !value.includes('@') || isRequired && !value.includes('.')) {
                showError($(this), 'The email must contain "@" and "."');
                isValid = false;
            } 
            // Final format check
            else if (value !== '' && !emailPattern.test(value)) {
                showError($(this), 'ایمیل وارد شده معتبر نیست.');
                isValid = false;
            } else {
                clearError($(this));
            }
        });
        // Landline (Telephone) Validation
        steps.eq(stepIndex).find('input[type="telephone"]').each(function () {
            const value = $(this).val().trim();
            const pattern = /^[0-9]{8}$/;
            const isRequired = $(this).closest('.is_required').length > 0;


            if (isRequired && value === '') {
                showError($(this), 'پر کردن این فیلد اجباری است');
                isValid = false;
            }
            // Check if required and empty
            else if ($(this).closest('.is_required').length && value === '') {
                showError($(this), 'پر کردن این فیلد اجباری است');
                isValid = false;
            } 
            // Check format (exactly 8 digits)
            else if (value !== '' && !pattern.test(value)) {
                showError($(this), 'The phone number is wrong.');
                isValid = false;
            } else {
                clearError($(this));
            }
        });
        // Postal Code (National Code) Validation
        steps.eq(stepIndex).find('input[type="nationalcode"]').each(function () {
            const value = $(this).val().trim();
            const isRequired = $(this).closest('.is_required').length > 0;

            // 1. Check if required and empty
            if (isRequired && value === '') {
                showError($(this), 'پر کردن این فیلد اجباری است');
                isValid = false;
            } 
            // 2. Validate length (exactly 10 digits)
            else if (value !== '' && value.length !== 10) {
                showError($(this), 'The entered postal code is wrong.');
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
        } else if (!/^\d*$/.test(value)) {
            showError(input, 'لطفا فقط عدد وارد کنید');
        } else if (value.length > 0 && value.length !== 11) {
            showError(input, 'شماره موبایل باید دقیقا 11 رقم باشد');
        }
    });
    // Email Real-time Validation (Prevent Farsi and Enforce Latin)
    $('.sproduct-input input[type="email"]').on('input', function () {
        const input = $(this);
        const value = input.val().trim();
        
        clearError(input);
        
        // 1. Prevent Farsi characters
        const farsiPattern = /[آ-ی]/;  // Matches any Farsi character
        if (farsiPattern.test(value)) {
            showError(input, 'Enter your email in Latin');
            input.val(value.replace(farsiPattern, ''));  // Remove Farsi characters
        }
    });
    // Real-time Validation for Postal Code (Prevent Letters)
    $('.sproduct-input input[type="nationalcode"]').on('input', function () {
        const input = $(this);
        const value = input.val().trim();
        
        clearError(input);
        
        // 1. Allow only numbers (remove letters)
        if (!/^\d*$/.test(value)) {
            showError(input, 'Zip code must be entered as a number');
            input.val(value.replace(/\D/g, ''));  // Remove non-digits
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

        $('.sproduct-input input[type="nationalcode"]').each(function () {
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

        $('.sproduct-input input[type="telephone"]').each(function () {
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
    // Prevent Non-numeric Characters from Being Typed
    $('.sproduct-input input[type="nationalcode"]').on('keypress', function (e) {
        const charCode = e.which ? e.which : e.keyCode;
        if (charCode < 48 || charCode > 57) {
            e.preventDefault();
            showError($(this), 'Zip code must be entered as a number');
        }
    });
    $('.sproduct-input input, .sproduct-input textarea').on('input', function () {
        const inputValue = $(this).val().trim();
        if (inputValue !== '') {
            clearError($(this));
        }
    });
});
