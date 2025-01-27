
jalaliDatepicker.startWatch({
    minDate: "attr",
    maxDate: "attr",
    time: true,
});  
jQuery(document).ready(function ($) {
    
    $(document).on('click','.sproductFormButtonPrev',function(){
        validateCurrentStep();
    });
    $(document).on('click','.sproductFormButtonNext',function(){
        console.log('changed');
    });
    $(document).on('click', '.sproductFormButtonProceed', function () {
        let formData = new FormData();
        let jsonData = {};
        $('.sproduct-input').each(function () {
            let $input = $(this);
            console.log($input);
            let name = $input.attr('name');
            let type = $input.data('input-type');
    
            if (!name) return;
            if (type === 'radio') {
                if ($input.is(':checked')) {
                    jsonData[name] = $input.val();
                }
            } else if (type === 'checkbox') {
                if (!jsonData[name]) {
                    jsonData[name] = [];
                }
                if ($input.is(':checked')) {
                    jsonData[name].push($input.val());
                }
            } else if (type === 'file') {
                console.log($input);
                if ($input[0].files.length > 0) {
                    formData.append(name, $input[0].files[0]);
                }
            } else {
                jsonData[name] = $input.val();
            }
        });
        let postID = $('.sproductStepContainer').attr('data-post-id');
        let planPrice = $('input[name=selected_plan]:checked').attr('data-plan-price');
        let planDuration = $('input[name=selected_plan]:checked').attr('data-plan-duration');
        let planName = $('input[name=selected_plan]:checked').val();
        let requestType = 'خرید سرویس جدید';
        formData.append('submittedFormData', JSON.stringify(jsonData));
        formData.append('action', 'sproduct_submit_form');
        formData.append('postID', postID);
        formData.append('planName', planName);
        formData.append('planPrice', planPrice);
        formData.append('planDuration', planDuration);
        formData.append('requestType', requestType);
        formData.append('nonce', sproductAjax.nonce);
        $.ajax({
            url: sproductAjax.ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: (res) => {
                if (res.data.added === 1) {
                    window.location.href = '../../cart';
                }
            },
            error: (xhr, status, error) => {
                console.log(xhr.responseText);
                console.log(status, error);
                alert('خطا در ارسال فرم.');
            }
        });
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
