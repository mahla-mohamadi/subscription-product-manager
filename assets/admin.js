jQuery(document).ready(function ($) {
    const formBuilder = $('.form-steps');
    const hiddenInput = $('#sproduct_form_data');
    const addStepBtn = $('#add-step-btn');

    let formData = window.sproductFormData && Array.isArray(window.sproductFormData)
        ? window.sproductFormData
        : [];

    renderForm();

    addStepBtn.on('click', function () {
        const newStep = {
            name: 'New Step',
            inputs: [],
            condition: null
        };
        formData.push(newStep);
        renderForm();
        saveForm();
    });

    function renderForm() {
        formBuilder.html('');
        formData.forEach((step, stepIndex) => {
            const stepDiv = $(`
                <div class="step" data-step-index="${stepIndex}">
                    <div class="step-header">
                        <button type="button" class="delete-step-btn button button-danger">X</button>
                        <h3 contenteditable="true" class="step-title">${step.name}</h3>
                    </div>
                    <select class="step-condition">
                        <option value="">بدون شرط</option>
                        <option value="checkbox">شرط چک باکس</option>
                    </select>
                    <button type="button" class="add-input-btn button">افزودن +</button>
                    <div class="inputs" data-step-index="${stepIndex}"></div>
                </div>
            `);

            stepDiv.find('.step-condition').val(step.condition || '').on('change', function () {
                formData[stepIndex].condition = $(this).val();
                saveForm();
            });

            stepDiv.find('.step-title').on('input', function () {
                formData[stepIndex].name = $(this).text();
                saveForm();
            });

            stepDiv.find('.add-input-btn').on('click', function () {
                const newInput = {
                    label: 'New Input',
                    type: formData[stepIndex].condition === '' ? 'text' : formData[stepIndex].condition,
                    required: false,
                    placeholder: '',
                    options: []
                };
                formData[stepIndex].inputs.push(newInput);
                renderForm();
                saveForm();
            });

            stepDiv.find('.delete-step-btn').on('click', function () {
                if (confirm('Delete this step?')) {
                    formData.splice(stepIndex, 1);
                    renderForm();
                    saveForm();
                }
            });

            formBuilder.append(stepDiv);
            renderInputs(stepDiv.find('.inputs'), step.inputs, stepIndex);
        });
        makeSortable();
    }

    function renderInputs(container, inputs, stepIndex) {
        container.html('');
        inputs.forEach((input, inputIndex) => {
            const inputDiv = $(`
            <div class="input-item" data-input-index="${inputIndex}">
                <div class="input-header">
                <input type="text" class="condition-input" placeholder="#کد شرط" value="${input.condition || ''}" />
                <label contenteditable="true">${input.label}</label>
                <select class="input-type">
                    <option value="text" ${input.type === 'text' ? 'selected' : ''}>Text</option>
                    <option value="email" ${input.type === 'email' ? 'selected' : ''}>Email</option>
                    <option value="textarea" ${input.type === 'textarea' ? 'selected' : ''}>Text Area</option>
                    <option value="national_code" ${input.type === 'national_code' ? 'selected' : ''}>National Code</option>
                    <option value="post_code" ${input.type === 'post_code' ? 'selected' : ''}>Post Code</option>
                    <option value="mobile" ${input.type === 'mobile' ? 'selected' : ''}>Mobile Number</option>
                    <option value="telephone" ${input.type === 'telephone' ? 'selected' : ''}>Telephone</option>
                    <option value="checkbox_group" ${input.type === 'checkbox_group' ? 'selected' : ''}>Checkbox Group</option>
                </select>
                <!-- Placeholder Field -->
                <input type="text" class="placeholder-input" placeholder="Placeholder text" value="${input.placeholder || ''}" />
                <input type="checkbox" class="required-checkbox" ${input.required ? 'checked' : ''} /> ضروری
                <button type="button" class="delete-input-btn button button-small button-danger">X</button>
                <div class="checkbox-repeater"></div>
                </div>
            </div>
            `);

            inputDiv.find('.input-type').on('change', function () {
                const newType = $(this).val();
                input.type = newType;
                if (newType === 'checkbox_group') {
                    renderCheckboxRepeater(inputDiv.find('.checkbox-repeater'), input.options, stepIndex, inputIndex);
                } else {
                    input.options = [];
                    inputDiv.find('.checkbox-repeater').html('');
                }
                saveForm();
            });

            inputDiv.find('.condition-input').on('input', function () {
                input.condition = $(this).val();
                saveForm();
            });

            inputDiv.find('.placeholder-input').on('input', function () {
                input.placeholder = $(this).val();
                saveForm();
            });

            inputDiv.find('.required-checkbox').on('change', function () {
                input.required = $(this).is(':checked');
                saveForm();
            });

            if (input.type === 'checkbox_group') {
                renderCheckboxRepeater(inputDiv.find('.checkbox-repeater'), input.options, stepIndex, inputIndex);
            }

            inputDiv.find('.delete-input-btn').on('click', function () {
                if (confirm('Delete this input?')) {
                    inputs.splice(inputIndex, 1);
                    renderInputs(container, inputs, stepIndex);
                    saveForm();
                }
            });

            container.append(inputDiv);
        });
    }

    function renderCheckboxRepeater(container, options, stepIndex, inputIndex) {
        container.html('');
        options.forEach((option, index) => {
            const optionDiv = $(`
                <div class="checkbox-item">
                    <input type="text" value="${option.value}" class="checkbox-option" placeholder="Option ${index + 1}">
                    <span class="checkbox-id uniqueid-checkbox">${option.id}</span>
                    <button type="button" class="delete-option">X</button>
                </div>
            `);

            optionDiv.find('.checkbox-option').on('input', function () {
                options[index].value = $(this).val().trim();
                saveForm();
            });

            optionDiv.find('.delete-option').on('click', function () {
                options.splice(index, 1);
                renderCheckboxRepeater(container, options, stepIndex, inputIndex);
                saveForm();
            });

            container.append(optionDiv);
        });

        const addOptionBtn = $('<button type="button" class="add-option">+ Add Option</button>');
        addOptionBtn.on('click', function () {
            const uniqueId = `ck-${stepIndex}-${inputIndex}-${options.length + 1}`;
            options.push({ id: uniqueId, value: '' });
            renderCheckboxRepeater(container, options, stepIndex, inputIndex);
            saveForm();
        });

        container.append(addOptionBtn);
    }

    function saveForm() {
        hiddenInput.val(JSON.stringify(formData));
    }

    function makeSortable() {
        if (typeof Sortable !== 'undefined') {
            Sortable.create(formBuilder[0], {
                animation: 150,
                onEnd: function (evt) {
                    const item = formData.splice(evt.oldIndex, 1)[0];
                    formData.splice(evt.newIndex, 0, item);
                    saveForm();
                }
            });
        }
    }
});
