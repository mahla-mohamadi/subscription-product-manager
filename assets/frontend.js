
jalaliDatepicker.startWatch({
    minDate: "attr",
    maxDate: "attr",
    time: true,
});  




jQuery(document).ready(function ($) {
    function isJustNumbers(value) {
        return /^\d*$/.test(value);
    }
    $('.sproduct-input[data-input-type="phone"], \
       .sproduct-input[data-input-type="phonenumber"], \
       .sproduct-input[data-input-type="postcode"], \
       .sproduct-input[data-input-type="nationalcode"], \
       .sproduct-input[data-input-type="number"]').on('input', function () {
        // $(this).siblings('.validateMessage').remove();
        let currentVal = $(this).val();
        if (!isJustNumbers(currentVal)) {
            $(this).val(currentVal.replace(/\D/g, ""));
            if ($(this).siblings('.validateNumberMessage').length === 0) {
                $(this).after('<span class="validateNumberMessage">فقط عدد وارد شود</span>');
            }
        } else {
            $(this).siblings('.validateNumberMessage').remove();
        }
    });

    let dynamicRules = JSON.parse($('#condition_data_input').val());
    $.each(dynamicRules, function(index, rule) {
        $('input[name="' + rule.showItem + '"]').closest('.sproductSingleInput').hide();
        $('input[name="' + rule.ifItem + '"]').on('change', function() {
          var inputType = $(this).attr('type');
          if (inputType === 'checkbox') {
            if ($(this).is(':checked') && $(this).val() === rule.equalItem) {
              $('input[name="' + rule.showItem + '"]').closest('.sproductSingleInput').show();
            } else {
              $('input[name="' + rule.showItem + '"]').closest('.sproductSingleInput').hide();
            }
          } else if (inputType === 'radio') {
            var selectedVal = $('input[name="' + rule.ifItem + '"]:checked').val();
            if (selectedVal === rule.equalItem) {
              $('input[name="' + rule.showItem + '"]').closest('.sproductSingleInput').show();
            } else {
              $('input[name="' + rule.showItem + '"]').closest('.sproductSingleInput').hide();
            }
          }
        });
    });
    function isEmptyOrSpaces(value) {
        return value === null || value.match(/^ *$/) !== null;
    }
    function isStringBetween(string,min,max){
        if(min && max && (string.length>max || string.length<min) ){
            return false;
        }
        return true;
    }
    function isNumberBetween(number,min,max){
        let parsedInt = parseInt(number , 10);
        if(min && max && (parsedInt>max || parsedInt<min)){
            return false;
        }
        return true;
    }
    function isTenDigits(value) {
        if (value.length === 10) {
            return true;
        }
        return false;
    }
    function isPhoneNumber(value) {
        if (value.length === 11 && value.startsWith("09")) {
            return true;
        }
        return false;
    }
    function isValidEmail(value) {
        var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailPattern.test(value);
    }
    function isIranianLandline(phoneNumber) {
        let regex = /^0[1|3|4|5|6|7|8|9][0-9]{9}$|^02[0-9]{9}$/;
        return regex.test(phoneNumber);
    }
    function isValidDate(date) {
        let dateRegex = /^\d{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])$/;
        return dateRegex.test(date);
    }
    function validateStep(step){
        let allInputs = $('.sproductSingleStep').eq(step).find('.sproduct-input');
        $.each(allInputs,function(){
            let validateMessage = '';
            let isValid = true;
            let currentInput = $(this);
            let currentType = currentInput.attr('data-input-type');
            let currentVal = currentInput.val();
            let currentIsRequired = (currentInput.attr('data-input-required')==1 ? true : false);
            let currentMinChar = currentInput.attr('data-input-minchar');
            let currentMaxChar = currentInput.attr('data-input-maxchar');
            let currentMinNum = currentInput.attr('data-input-minnum');
            let currentMaxNum = currentInput.attr('data-input-maxnum');
            if(currentType == 'checkbox' || currentType == 'radio'){
                if(currentInput.parent('label').siblings('label').find('input').is(':checked') || currentInput.is(':checked')){
                    currentVal = 'ne'
                }
                else{
                    currentVal = ''
                }
            }
            if(currentIsRequired && isEmptyOrSpaces(currentVal)){
                validateMessage = 'تکمیل این فیلد ضروری‌است';
                isValid = false;
            }
            else if(!isEmptyOrSpaces(currentVal)){
                if(currentType == 'text' && !isStringBetween($(this).val(),currentMinChar,currentMaxChar)){
                    validateMessage = 'طول کاراکترها مجاز نیست';
                    isValid = false;
                }
                else if(currentType == 'number' && !isNumberBetween($(this).val(),currentMinNum,currentMaxNum)){
                    validateMessage = 'عدد وارد شده باید بین '+currentMinNum+' و '+currentMaxNum+' باشد';
                    isValid = false;
                }
                else if (
                    (currentType == 'postcode' || currentType == 'nationalcode') && !isTenDigits(currentVal)){ 
                    validateMessage = 'باید ده رقم باشد';
                    isValid = false;
                }
                else if 
                    (currentType == 'phonenumber' && !isPhoneNumber(currentVal)){ 
                    validateMessage = 'شماره موبایل صحیح نیست';
                    isValid = false;
                }
                else if 
                    (currentType == 'email' && !isValidEmail(currentVal)){ 
                    validateMessage = 'ایمیل وارد شده صحیح نیست';
                    isValid = false;
                }
                else if (currentType == 'phone' && !isIranianLandline(currentVal)) { 
                    validateMessage = 'شماره تلفن ثابت معتبر نیست';
                    isValid = false;
                }
                else if (currentType == 'datepicker' && !isValidDate(currentVal)) { 
                    validateMessage = 'فرمت تاریخ صحیح نیست';
                    isValid = false;
                }
            }
            else{
                validateMessage = '';
                isValid = true;
            }
            // else if(currentType == 'email'){
                
            // }
            // else if(currentType == 'number'){

            // }
            if(!isValid){
                $(this).siblings('.validateMessage').remove();
                $(this).css({'border-color':'#d00'});
                $(this).after('<span class="validateMessage">'+validateMessage+'</span>');
            }
            else{
                $(this).css({'border-color':'#ccc'});
                $(this).siblings('.validateMessage').remove();
            }
        });
    }
    $(document).on('click','.sproductFormButtonPrev',function(){
        validateCurrentStep();
    });
    $(document).on('click','.sproductFormButtonNext',function(){
        console.log('changed');
    });
    $('.sproduct-input').on('input', function(){
        if($(this).siblings('.validateMessage').length){
            $(this).siblings('.validateMessage').remove();
            $(this).css({'border-color':'rgb(204,204,204)'})
        }
    });
    $(document).on('click', '.sproductFormButtonProceed', function () {
        validateStep(0);
        // let formData = new FormData();
        // let jsonData = {};
        // $('.sproduct-input').each(function () {
        //     let $input = $(this);
        //     console.log($input);
        //     let name = $input.attr('name');
        //     let type = $input.data('input-type');
    
        //     if (!name) return;
        //     if (type === 'radio') {
        //         if ($input.is(':checked')) {
        //             jsonData[name] = $input.val();
        //         }
        //     } else if (type === 'checkbox') {
        //         if (!jsonData[name]) {
        //             jsonData[name] = [];
        //         }
        //         if ($input.is(':checked')) {
        //             jsonData[name].push($input.val());
        //         }
        //     } else if (type === 'file') {
        //         console.log($input);
        //         if ($input[0].files.length > 0) {
        //             formData.append(name, $input[0].files[0]);
        //         }
        //     } else {
        //         jsonData[name] = $input.val();
        //     }
        // });
        // let postID = $('.sproductStepContainer').attr('data-post-id');
        // let planPrice = $('input[name=selected_plan]:checked').attr('data-plan-price');
        // let planDuration = $('input[name=selected_plan]:checked').attr('data-plan-duration');
        // let planName = $('input[name=selected_plan]:checked').val();
        // let requestType = 'خرید سرویس جدید';
        // formData.append('submittedFormData', JSON.stringify(jsonData));
        // formData.append('action', 'sproduct_submit_form');
        // formData.append('postID', postID);
        // formData.append('planName', planName);
        // formData.append('planPrice', planPrice);
        // formData.append('planDuration', planDuration);
        // formData.append('requestType', requestType);
        // formData.append('nonce', sproductAjax.nonce);
        // $.ajax({
        //     url: sproductAjax.ajaxurl,
        //     method: 'POST',
        //     dataType: 'json',
        //     data: formData,
        //     processData: false,
        //     contentType: false,
        //     success: (res) => {
        //         if (res.data.added === 1) {
        //             window.location.href = '../../cart';
        //         }
        //     },
        //     error: (xhr, status, error) => {
        //         console.log(xhr.responseText);
        //         console.log(status, error);
        //         alert('خطا در ارسال فرم.');
        //     }
        // });
    });
    
    






























    $(document).on('change','.sproductLinkedProductCheckbox',function(){
        console.log('changed');
    });
    $('.sproduct-input-file').on('change', function () {
        const file = this.files[0];
        const maxSize = 1 * 1024 * 1024;
        const allowedFormats = ['image/png', 'image/jpeg', 'application/pdf'];
        if (file) {
            if (file.size > maxSize) {
                alert('حداکثر حجم بارگذاری 1 مگابایت است')
                $(this).val('');
                return;
            }
            else if (!allowedFormats.includes(file.type)) {
                alert('فرمت‌های پشتیبانی شده: JPG | PNG | PDF')
                $(this).val('');
                return;
            }
            else{
                alert('فایل اضافه شد')
            }
        }
    });
    $(document).on('click','.sproduct-input-file-button',function(){
        $(this).siblings('.sproduct-input-file').trigger('click');
    });
    // $('#numericInput').on('keypress', function (e) {
    //     if (!/\d/.test(String.fromCharCode(e.which))) {
    //         e.preventDefault(); // Prevent the character from being entered
    //     }
    // });

    // $('#numericInput').on('input', function () {
    //     this.value = this.value.replace(/\D/g, '');
    // }); 
});

