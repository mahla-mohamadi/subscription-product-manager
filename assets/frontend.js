
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
    $(document).on('click','.sproductFormButtonProceed',function(){
        console.log('changed');
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
