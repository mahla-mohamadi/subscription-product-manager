
jQuery(document).ready(function ($) {
    // Initialize Persian Date Picker for all date inputs
    $('.datepicker-input').each(function () {
        const uniqueId = $(this).attr("id"); // Get the ID of the input
        if (uniqueId) {
            $("#" + uniqueId).persianDatepicker({
                "inline": false,
                "format": "YYYY/MM/DD",
                "viewMode": "year",
                "initialValue": false,
                "minDate": null,
                "maxDate": null,
                "autoClose": false,
                "position": "auto",
                "altFormat": "YYYY/MM/DD",
                // "altField": `#alt-${inputId}`, // Create a corresponding alt field if needed
                "onlyTimePicker": false,
                "onlySelectOnDate": false,
                "calendarType": "persian",
                "inputDelay": 800,
                "observer": false,
                "calendar": {
                    "persian": {
                        "locale": "fa",
                        "showHint": true,
                        "leapYearMode": "algorithmic"
                    },
                    "gregorian": {
                        "locale": "en",
                        "showHint": false
                    }
                },
                "navigator": {
                    "enabled": true,
                    "scroll": {
                        "enabled": true
                    },
                    "text": {
                        "btnNextText": ">",
                        "btnPrevText": "<"
                    }
                },
                "toolbox": {
                    "enabled": true,
                    "calendarSwitch": {
                        "enabled": false,
                        "format": "MMMM"
                    },
                    "todayButton": {
                        "enabled": true,
                        "text": {
                            "fa": "امروز",
                            "en": "Today"
                        }
                    },
                    "submitButton": {
                        "enabled": true,
                        "text": {
                            "fa": "تایید",
                            "en": "Submit"
                        }
                    },
                    "text": {
                        "btnToday": "امروز"
                    }
                },
                "responsive": true
            });
        }
    });
    const steps = $('.sproduct-step'),
        nextBtn = $('#next-btn'),
        prevBtn = $('#prev-btn'),
        submitBtn = $('#submit-btn'),
        form = $('#sproduct-main-form');
    let currentStep = parseInt(sessionStorage.getItem('currentStep')) || 0,
        formData = JSON.parse(sessionStorage.getItem('sproductFormData')) || {};
    showStep(currentStep);
    populateForm();
    function populateForm() {
        $.each(formData, (name, value) => {
            const input = $(`[name="${name}"]`);
            if (input.length) {
                if (input.attr('type') === 'checkbox') {
                    input.prop('checked', value === "1");
                } else {
                    input.val(value);
                }
            }
        });
    }
    function handleNavigation(change) {
        if (validateStep(currentStep)) {
            currentStep += change;
            showStep(currentStep);
            saveStepData();
        }
    }
    nextBtn.on('click', () => handleNavigation(1));
    prevBtn.on('click', () => handleNavigation(-1));

    submitBtn.on('click', (e) => {
        if (!validateStep(currentStep)) e.preventDefault();
    });

    form.on('submit', function (e) {
        e.preventDefault();
        let submittedFormData = JSON.stringify($(this).serialize());
        let postID = $('#sproduct-form-frontend').attr('data-post-id');
        let planPrice = $('input[name=selected_plan]:checked').attr('data-plan-price');
        let planDuration = $('input[name=selected_plan]:checked').attr('data-plan-duration');
        let planName = $('input[name=selected_plan]:checked').val();
        let requestType = 'خرید سرویس جدید';
        // if (!$('input[name="selected_plan"]:checked').val()) {
        //     e.preventDefault();
        //     alert('Please select a subscription plan before submitting.');
        // } else if (!validateAll()) {
        //     e.preventDefault();
        // } else {
        //     saveStepData();
        
            $.ajax({
                url: sproductAjax.ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'sproduct_submit_form',
                    submittedFormData: submittedFormData,
                    postID: postID,
                    planName: planName,
                    planPrice: planPrice,
                    planDuration: planDuration,
                    requestType: requestType,
                    submittedFormData: submittedFormData,
                    nonce: sproductAjax.nonce
                },
                success: (res) => {
                    if(res.data.added===1){
                        window.location.href = '../../cart';
                    }
                },
                error: (xhr, status, error) => {
                    console.log(xhr.responseText);
                    console.log(status, error);
                    alert('خطا در ارسال فرم.');
                }
            });
        // }
    });
    function validateStep(stepIndex) {
        let isValid = true;
        const step = steps.eq(stepIndex);
        const validations = [
            { selector: 'input[type="tel"], input[type="telephone"]', message: 'شماره موبایل باید با 09 شروع شود و 11 رقم باشد.', condition: (v) => !/^09\d{9}$/.test(v) },
            { selector: 'input[type="email"]', message: 'ایمیل وارد شده معتبر نیست.', condition: (v) => !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) },
            { selector: 'input[type="nationalcode"]', message: 'کد ملی باید دقیقاً 10 رقم باشد.', condition: (v) => v.length !== 10 }
        ];
        step.find('input, textarea').each(function () {
            const value = $(this).val().trim();
            const isRequired = $(this).closest('.is_required').length > 0;
            let fieldValid = true;
            if (isRequired && value === '') {
                showError($(this), 'پر کردن این فیلد اجباری است');
                fieldValid = false;
            } else {
                clearError($(this));
                if (value !== '') {
                    validations.forEach(({ selector, message, condition }) => {
                        if ($(this).is(selector) && condition(value)) {
                            showError($(this), message);
                            fieldValid = false;
                        }
                    });
                }
            }
            if (!fieldValid) {
                isValid = false;
            }
        });
        return isValid;
    }
    function validateAll() {
        let isValid = true;
        form.find('input, textarea').each(function () {
            if ($(this).closest('.input-item').find('.required-checkbox').is(':checked') && !$(this).val().trim()) {
                showError($(this), 'پر کردن این فیلد اجباری است');
                isValid = false;
            } else {
                clearError($(this));
            }
        });
        return isValid;
    }
    function showStep(stepIndex) {
        steps.hide().eq(stepIndex).show();
        prevBtn.prop('disabled', stepIndex === 0);
        nextBtn.toggle(stepIndex !== steps.length - 1);
        submitBtn.toggle(stepIndex === steps.length - 1);
    }
    // form.on('input change', 'input, textarea, select', function () {
    //     const input = $(this);
    //     formData[input.attr('name')] = input.attr('type') === 'checkbox'
    //         ? input.is(':checked') ? "1" : "0"
    //         : input.val();
    //     sessionStorage.setItem('sproductFormData', JSON.stringify(formData));
    // });
    // function saveStepData() {
    //     steps.eq(currentStep).find('input, textarea, select').each(function () {
    //         const input = $(this);
    //         formData[input.attr('name')] = input.attr('type') === 'checkbox'
    //             ? input.is(':checked') ? "1" : "0"
    //             : input.val();
    //     });
    //     sessionStorage.setItem('sproductFormData', JSON.stringify(formData));
    //     sessionStorage.setItem('currentStep', currentStep);
    // }
    function showError(input, message) {
        clearError(input);
        input.addClass('input-error-border').after(`<div class="input-error" style="color: red; margin-top: 5px;">${message}</div>`);
    }

    function clearError(input) {
        input.removeClass('input-error-border').next('.input-error').remove();
    }

    const realTimeValidations = [
        { selector: 'input[type="telephone"]', pattern: /^\d{0,8}$/, message: 'The phone number cannot exceed 8 digits.' },
        { selector: 'input[type="tel"]', pattern: /^09\d{0,10}$/, message: 'شماره موبایل باید با 09 شروع شود' },
        { selector: 'input[type="email"]', pattern: /^[^\u0600-\u06FF]+$/, message: 'Enter your email in Latin' },
        { selector: 'input[type="nationalcode"]', pattern: /^\d*$/, message: 'Zip code must be entered as a number' }
    ];

    realTimeValidations.forEach(({ selector, pattern, message }) => {
        form.on('input', selector, function () {
            const input = $(this);
            const value = input.val().trim();
            if (!pattern.test(value)) {
                showError(input, message);
                input.val(value.replace(/\D/g, ''));
            } else {
                clearError(input);
            }
        });
    });
    form.on('keypress', 'input[type="tel"], input[type="nationalcode"]', function (e) {
        const charCode = e.which ? e.which : e.keyCode;
        if (charCode < 48 || charCode > 57) {
            e.preventDefault();
            showError($(this), 'لطفا فقط عدد وارد کنید');
        }
    });
});



// ذخیره مقادیر فرم در sessionStorage
document.querySelectorAll("#sproduct-main-form input, #sproduct-main-form textarea").forEach(function (input) {
    input.addEventListener("input", function () {
        // برای چک‌باکس‌ها و رادیو باتن‌ها
        if (input.type === "checkbox" || input.type === "radio") {
            let selectedValues = [];
            const checkboxes = document.querySelectorAll(`input[name="${input.name}"]:checked`);
            checkboxes.forEach(function (checkbox) {
                selectedValues.push(checkbox.value);
            });
            sessionStorage.setItem(input.name, selectedValues.join(","));
        } else {
            sessionStorage.setItem(input.name, input.value);
        }
    });
});

// بارگذاری مقادیر ذخیره‌شده هنگام بارگذاری صفحه
window.addEventListener("load", function () {
    document.querySelectorAll("#sproduct-main-form input, #sproduct-main-form textarea").forEach(function (input) {
        const storedValue = sessionStorage.getItem(input.name);

        if (storedValue) {
            // اگر فیلد از نوع رادیو باتن است، وضعیت انتخاب شده را بازیابی کنیم
            if (input.type === "radio") {
                const selectedRadio = document.querySelector(`input[name="${input.name}"][value="${storedValue}"]`);
                if (selectedRadio) {
                    selectedRadio.checked = true;
                }
            }
            // اگر فیلد از نوع چک‌باکس است، وضعیت انتخاب شده را بازیابی کنیم
            else if (input.type === "checkbox") {
                const selectedValues = storedValue.split(',');
                const checkboxes = document.querySelectorAll(`input[name="${input.name}"]`);
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = selectedValues.includes(checkbox.value);
                });
            }
            // برای دیگر فیلدها (مثل input text, textarea)، مقدار را به فیلد اختصاص می‌دهیم
            else {
                input.value = storedValue;
            }
        }
    });

    const steps = document.querySelectorAll(".sproduct-step");
    steps.forEach(function (step) {
        const stepIndex = parseInt(step.getAttribute("data-step"));
        const storedStep = sessionStorage.getItem('currentStep');

        if (storedStep && parseInt(storedStep) === stepIndex) {
            step.style.display = "block";
        } else if (!storedStep && stepIndex === 0) {
            step.style.display = "block";
        } else {
            step.style.display = "none";
        }
    });
});

// مدیریت تغییرات مراحل (steps)
let currentStep = 0;
document.getElementById("next-btn").addEventListener("click", function () {
    const steps = document.querySelectorAll(".sproduct-step");
    console.log(steps)
    if (currentStep < steps.length - 1) {
        currentStep++;
        sessionStorage.setItem('currentStep', currentStep);
    }

});

document.getElementById("prev-btn").addEventListener("click", function () {
    const steps = document.querySelectorAll(".sproduct-step");
    if (currentStep > 0) {
        currentStep--;
        sessionStorage.setItem('currentStep', currentStep);
    }

    // در صورت برگشت به مرحله اول، دکمه ارسال مخفی می‌شود
    if (currentStep < steps.length - 1) {
        document.getElementById("submit-btn").style.display = "none";
        document.getElementById("next-btn").style.display = "block";
    }
});

