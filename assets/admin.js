jQuery(document).ready(function ($) {
    const formContainer = $('.form-steps');
    const conditionContainer = $('.conditionRow');
    function initializeSortable() {
        new Sortable($('.form-steps')[0], {
            animation: 150,
            onEnd: function () {
                updateFormData();
            },
        });
        $('.step-input-container').each(function () {
            new Sortable(this, {
                animation: 150,
                onEnd: function () {
                    updateFormData();
                },
            });
        });
        $('.input-option-container .options').each(function () {
            new Sortable(this, {
                animation: 150,
                onEnd: function () {
                    updateFormData();
                },
            });
        });
    }
    function renderAdminForm(formData){
        formContainer.html('');
        let stepContainer = '';
        $.each(formData , function(stepIndex,step){
            stepContainer += '<div class="step" data-step-index="'+stepIndex+'">';
            stepContainer +='<div class="step-header"><label class="step-label">نام:<input type="text" id="step-heading-input" class="step-input" value="'+ step.name+'" dir="rtl"></label><div id="remove-step" class="remove-step"><svg fill="#d11b1b" viewBox="0 0 1024 1024" width="20" height="20" xmlns="http://www.w3.org/2000/svg"><path d="M352 480h320a32 32 0 1 1 0 64H352a32 32 0 0 1 0-64"/><path d="M512 896a384 384 0 1 0 0-768 384 384 0 0 0 0 768m0 64a448 448 0 1 1 0-896 448 448 0 0 1 0 896"/></svg></div></div>';
            stepContainer += '<div class="step-input-container">';
            $.each(step.inputs , function(inputIndex,input){
                stepContainer += '<div class="input" data-input-index="'+inputIndex+'">';
                stepContainer += '<label><input type="checkbox" class="input-required"'+(input.isRequired?'checked':'')+'>ضروری</label>';
                stepContainer += '<label>نوع<select class="input-type"><option value="text"'+(input.type=='text'?'selected':'')+'>متن</option><option value="email"'+(input.type=='email'?'selected':'')+'>ایمیل</option><option value="number"'+(input.type=='number'?'selected':'')+'>عدد</option></option><option value="datepicker"'+(input.type=='datepicker'?'selected':'')+'>انتخابگر تاریخ</option><option value="nationalcode"'+(input.type=='nationalcode'?'selected':'')+'>کد ملی</option><option value="postcode"'+(input.type=='postcode'?'selected':'')+'>کد پستی</option><option value="phonenumber"'+(input.type=='phonenumber'?'selected':'')+'>شماره همراه</option><option value="phone"'+(input.type=='phone'?'selected':'')+'>شماره ثابت</option><option value="textarea"'+(input.type=='textarea'?'selected':'')+'>ناحیه متنی</option><option value="radio"'+(input.type=='radio'?'selected':'')+'>انتخاب تکی</option><option value="checkbox"'+(input.type=='checkbox'?'selected':'')+'>انتخاب چندگانه</option><option value="file"'+(input.type=='file'?'selected':'')+'>فایل</option></select></label>';
                stepContainer += '<label>نام<input class="input-name" type="text" value="'+input.name+'"></label>';
                stepContainer += '<label>نگهدارنده<input class="input-placeholder" type="text" value="'+input.placeholder+'"></label>';
                stepContainer += '<label>عرض<select class="input-width"><option value="half"'+(input.width=='half'?'selected':'')+'>نیمه</option><option value="full"'+(input.width=='full'?'selected':'')+'>عریض</option></select></label>';
                stepContainer += '<div class="logic-input-container">';
                if(input.type=='text'||input.type=='number' || input.type=='radio' || input.type=='checkbox'){stepContainer += '<div class="logic">';}
                if(input.type == 'text'){
                    stepContainer += '<label>حداقل کاراکتر<input class="logic-min-char" type="number" value="'+input.logics[0].minchar+'"></label>';
                    stepContainer += '<label>حداکثر کاراکتر<input class="logic-max-char" type="number" value="'+input.logics[0].maxchar+'"></label>';
                }
                else if(input.type == 'number'){
                    stepContainer += '<label>حداقل مقدار<input class="logic-min-num" type="number" value="'+input.logics[0].minnum+'"></label>';
                    stepContainer += '<label>حداکثر مقدار<input class="logic-max-num" type="number" value="'+input.logics[0].maxnum+'"></label>';
                }
                else if(input.type=='radio' || input.type=='checkbox'){stepContainer+='<label><input type="checkbox" class="input-vertical"'+(input.isVertical?'checked':'')+'>نمایش عمودی</label>'}
                if(input.type=='text'||input.type=='number' || input.type=='radio' || input.type=='checkbox'){stepContainer += '</div>';}
                stepContainer += '</div>';
                stepContainer += '<div class="input-option-container">';
                if(input.type=='radio'||input.type=='checkbox'){stepContainer += '<div class="options">';}
                $.each(input.options, function(optionIndex,option){
                    stepContainer += '<label class="option" data-option-index="'+optionIndex+'">گزینه<input type="text" value="'+option.name+'"><span class="remove-option" id="remove-option">حدف</span></label>';
                })
                if(input.type=='radio'||input.type=='checkbox'){stepContainer += '<div id="add-option" class="button-primary add-option">+ گزینه</div>';}
                if(input.type=='radio'||input.type=='checkbox'){stepContainer += '</div>';}
                stepContainer += '</div>';
                stepContainer += '<div id="remove-input" class="remove-step"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 11v6m4-6v6M4 7h16M6 7h12v11a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3zm3-2a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2H9z" stroke="#d11b1b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>';
                stepContainer += '</div>';
            });
            stepContainer += '</div>';
            $(formContainer).html(stepContainer);
            stepContainer += '<div id="add-input-field" class="button-primary add-input-field">افزودن فیلد</div>';
            stepContainer += '</div>';
        });
        $(formContainer).html(stepContainer);
        initializeSortable();
    }
    function updateFormData(){
        let formDataUpdate = [];
        $.each($('.step'),function(){
            let step = $(this);
            let stepData = {
                name: step.find('#step-heading-input').val(),
                inputs: [],
            }
            $.each(step.find('.input'),function(){
                let input = $(this);
                let isInputRequired = input.find('.input-required').is(':checked');
                let isInputVertical = input.find('.input-vertical').is(':checked');
                let inputData = {
                    type: input.find('.input-type').val(),
                    name: input.find('.input-name').val(),
                    placeholder: input.find('.input-placeholder').val(),
                    isRequired: isInputRequired,
                    isVertical: isInputVertical,
                    options: [],
                    width: input.find('.input-width').val(),
                    logics:[],
                }
                let logicData = {
                    minchar: (input.find('.logic-min-char').val() ? input.find('.logic-min-char').val() : ''),
                    maxchar: (input.find('.logic-max-char').val() ? input.find('.logic-max-char').val() : ''),
                    minnum: (input.find('.logic-min-num').val() ? input.find('.logic-min-num').val() : ''),
                    maxnum: (input.find('.logic-max-num').val() ? input.find('.logic-max-num').val() : ''),
                }
                $.each(input.find('.options input'),function(){
                    inputData.options.push({name:$(this).val()})
                });
                inputData.logics.push(logicData);
                stepData.inputs.push(inputData);
            });
            formDataUpdate.push(stepData);
        });
        formData = formDataUpdate;
    }
    function operateInputLogicHTML(type){
        switch (type) {
            case 'text':
                cont = '<div class="logic"><label>حداقل کاراکتر<input class="logic-min-char" type="number"></label><label>حداکثر کاراکتر<input class="logic-max-char" type="number"></label></div>';
                break;
            case 'number':
                cont = '<div class="logic"><label>حداقل مقدار<input class="logic-min-num" type="number"></label><label>حداکثر مقدار<input class="logic-max-num" type="number"></label></div>';
                break;
            default:
                cont = '';
        }
        return cont;
    }
    let formData = window.sproductFormData && Array.isArray(window.sproductFormData)
        ? window.sproductFormData
        : [];
    let conditionData = window.sproductConditionData && Array.isArray(window.sproductConditionData)
        ? window.sproductConditionData
        : [];
    renderAdminForm(formData);
    renderConditions(conditionData);
    $('#add-step-btn').on('click', function () {
        updateFormData();
        const newStep = {name: 'مرحله',inputs: []};
        formData.push(newStep);
        renderAdminForm(formData);
    });
    $(document).on('click','#remove-step', function () {
        updateFormData();
        let currentStep = parseInt($(this).closest('.step').attr('data-step-index'),10);
        formData.splice(currentStep, 1);
        renderAdminForm(formData);
    });
    $(document).on('click','#remove-input', function () {
        updateFormData();
        let currentStep = parseInt($(this).closest('.step').attr('data-step-index'),10);
        let currentInput = parseInt($(this).closest('.input').attr('data-input-index'),10);
        formData[currentStep].inputs.splice(currentInput, 1);
        renderAdminForm(formData);
    });
    $(document).on('click','#remove-option', function () {
        updateFormData();
        let currentStep = parseInt($(this).closest('.step').attr('data-step-index'),10);
        let currentInput = parseInt($(this).closest('.input').attr('data-input-index'),10);
        let currentOption = parseInt($(this).closest('.option').attr('data-option-index'),10);
        formData[currentStep].inputs[currentInput].options.splice(currentOption, 1);
        renderAdminForm(formData);
    });
    $(document).on('click','#add-input-field', function () {
        updateFormData();
        let currentStep = parseInt($(this).closest('.step').attr('data-step-index'),10);
        const newInput = {type: 'text',name: 'فیلد',placeholder: '',isRequired: false,options: [],width: 'half' , logics:[{maxchar:'',maxnum:'',minchar: '',minnum: ''}]}
        formData[currentStep].inputs.push(newInput);
        renderAdminForm(formData);
    });
    $(document).on('click','#add-option', function () {
        updateFormData();
        let currentStep = parseInt($(this).closest('.step').attr('data-step-index'),10);
        let currentInput = parseInt($(this).closest('.input').attr('data-input-index'),10);
        const newoption = {name:'گزینه'};
        formData[currentStep].inputs[currentInput].options.push(newoption);
        console.log(formData);
        renderAdminForm(formData);
    });
    $('#publish').on('click', function (e) {
        e.preventDefault();
        updateFormData();
        updateConditionData();
        $('#sproduct_form_data').val(JSON.stringify(formData));
        $('#sproduct_condition_data').val(JSON.stringify(conditionData));
        console.log($('#sproduct_condition_data').val());
        $(this).off('click');
        $(this).click();
    });
    $(document).on('change','.input-type',function(){
        updateFormData();
        let selectedType = $(this).val();
        let selectedStep = $(this).closest('.step');
        let selectedStepIndex = selectedStep.attr('data-step-index');
        let selectedInput = $(this).closest('.input');
        let selectedInputIndex = selectedInput.attr('data-input-index');
        formData[selectedStepIndex].inputs[selectedInputIndex].type = selectedType;
        if(selectedType == 'radio' || selectedType == 'checkbox'){
            selectedInput.find('.logic-input-container').html('<div class="logic"><label><input type="checkbox" class="input-vertical">نمایش عمودی</label></div>');
            let optionContainerInner = '<div class="options"></div><div id="add-option" class="button-primary add-option">+ گزینه</div>';
            selectedInput.find('.input-option-container').html(optionContainerInner);
        }
        else{
            selectedInput.find('.logic-input-container').html('');
            let inputLogicHTML = operateInputLogicHTML(selectedType);
            selectedInput.find('.logic-input-container').html(inputLogicHTML);
        }
    });
    $(document).on('focus' , '.step input[type=text]' , function(){
        let tempVal = $(this).val();
        $(this).val('');
        $(this).val(tempVal);
    });
    //////// Conditions
    function renderConditions(){
        console.log(conditionData);
        let finalConditionHTML = '';
        $.each(conditionData , function(conditionIndex,condition){
            let conditionDataFields = '<span>نمایش</span><select id="inputDropDown" class="select2 inputDropDown"><option value="">انتخاب کنید</option>';
            let conditionDataSelectFields = '<span>اگر</span><select id="inputSelectDropDown" class="select2 inputSelectDropDown"><option value="">انتخاب کنید</option>';
            let conditionDataOptions = '<span>برابر شود با</span><select id="inputOptionDropDown" class="select2 inputOptionDropDown"><option value="">انتخاب کنید</option>';    
            $.each(formData , function(stepIndex,step){
                $.each(step['inputs'] , function(inputIndex,input){
                    if(input['type']=='checkbox' || input['type']=='radio'){
                        conditionDataSelectFields+='<option value="'+input['name']+'"'+(condition['ifItem']==input['name'] ? ' selected':'')+'>'+input['name']+'</option>';
                        $.each(input['options'],function(optionIndex,option){
                            conditionDataOptions+='<option value="'+option['name']+'" data-parent-field="'+input['name']+'"'+(condition['equalItem']==option['name'] ? ' selected':'')+'>'+option['name']+'</option>';
                        });
                    }
                    else{
                        conditionDataFields+='<option value="'+input['name']+'"'+(condition['showItem']==input['name'] ? ' selected':'')+'>'+input['name']+'</option>';
                    }
                });
            });
            conditionDataFields+='</select>';
            conditionDataSelectFields+='</select>';
            conditionDataOptions+='</select>';
            finalConditionHTML += '<div class="conditionSingleRow">'+conditionDataFields+conditionDataSelectFields+conditionDataOptions+'</div>';
        });
        conditionContainer.html(finalConditionHTML);

        $('.select2').select2();
        $('.conditionSingleRow').each(function () {
            var $optionDropdown = $(this).find('.inputOptionDropDown');
            $optionDropdown.data('original-options', $optionDropdown.find('option').clone());
        });
        $('.inputSelectDropDown').on('change', function () {
            var selectedValue = $(this).val();
            var $row = $(this).closest('.conditionSingleRow');
            var $optionDropdown = $row.find('.inputOptionDropDown');
            var originalOptions = $optionDropdown.data('original-options');
            var filteredOptions = originalOptions.filter(function () {
                return $(this).attr('data-parent-field') === selectedValue;
            });
            $optionDropdown.select2('destroy');
            $optionDropdown.empty().append(filteredOptions);
            $optionDropdown.select2();
        });
        $('.inputSelectDropDown').trigger('change');

    }
    function updateConditionData(){
        let conditionDataUpdate = [];
        $.each($('.conditionSingleRow'),function(){
            console.log($(this).find('.inputDropDown').val());
            console.log($(this).find('.inputSelectDropDown').val());
            console.log($(this).find('.inputOptionDropDown').val());
            let conditionRow = {
                showItem: $(this).find('.inputDropDown').val(),
                ifItem: $(this).find('.inputSelectDropDown').val(),
                equalItem: $(this).find('.inputOptionDropDown').val(),
            }
            conditionDataUpdate.push(conditionRow);
        });
        conditionData = conditionDataUpdate;
    }
    $('.add-condition').on('click', function () {
        updateConditionData();
        const newCondition = {showItem: '' , ifItem: '' ,equalItem: ''};
        conditionData.push(newCondition);
        renderConditions(conditionData);
    });
});