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
            let datePickerCounter = 1; // Counter for datepicker inputs
            stepDiv.find('.add-input-btn').on('click', function () {
                const newInput = {
                    label: 'New Input',
                    type: formData[stepIndex].condition === '' ? 'text' : formData[stepIndex].condition,
                    required: false,
                    options: [],
                    id: `datepicker${Date.now()}` // Generate a unique ID based on timestamp
                };
                // Check if the new input is a date
                const inputType = $(this).siblings('.input-type').val();
                if (inputType === 'date') {
                    newInput.type = 'date';
                    newInput.id = `datepicker${datePickerCounter++}`; // Generate unique ID
                }
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
                            <option value="radio_group" ${input.type === 'radio_group' ? 'selected' : ''}>Radio Group</option>
                            <option value="date" ${input.type === 'date' ? 'selected' : ''}>Date</option>
                        </select>
                        <!-- Placeholder Field -->
                        <input type="text" class="placeholder-input" placeholder="Placeholder text" value="${input.placeholder || ''}" />    
                        <input type="checkbox" class="required-checkbox" ${input.required ? 'checked' : ''} /> ضروری
                        <button type="button" class="delete-input-btn button button-small button-danger">X</button>
                    </div>
                </div>
            </div>
            `);

            // Helper function to render options as checkboxes or radio buttons
            function renderOptions(container, type, options) {
                container.find('.checkbox-item').remove(); // Clear existing options
            
                // Ensure valid options are rendered
                options.forEach((option, index) => {
                    const optionDiv = $(`
                        <div class="checkbox-item">
                            <input type="${type}" disabled>
                            <input type="text" value="${option}" class="checkbox-option" placeholder="Option ${index + 1}">
                            <button type="button" class="delete-option">X</button>
                        </div>
                    `);
            
                    // Handle delete button for this option
                    optionDiv.find('.delete-option').on('click', function () {
                        optionDiv.remove();
            
                        // Update formData immediately when an option is deleted
                        const stepIndex = container.closest('.step').data('step-index');
                        const inputIndex = container.closest('.input-item').data('input-index');
                        const updatedOptions = [];
                        container.find('.checkbox-option').each(function () {
                            const value = $(this).val().trim();
                            if (value !== '') {
                                updatedOptions.push(value); // Avoid empty options
                            }
                        });
                        formData[stepIndex].inputs[inputIndex].options = updatedOptions;
            
                        saveForm();
                    });
            
                    container.append(optionDiv);
                });
            }
            if (input.type === 'checkbox_group') {
                const repeater = $('<div class="checkbox-repeater"></div>');

                // Add Option Button
                const addOptionBtn = $('<button type="button" class="add-option">+ Add Option</button>');
                addOptionBtn.on('click', function () {
                    const newIndex = repeater.find('.checkbox-item').length + 1;
                    const newOption = $(`
                        <div class="checkbox-item">
                            <input type="text" value="" class="checkbox-option" placeholder="Option ${newIndex}">
                            <button type="button" class="delete-option">X</button>
                        </div>
                    `);
                    repeater.append(newOption);
                    saveOptions(repeater, stepIndex, inputIndex);
                });
                // Delete Option Event
                repeater.on('click', '.delete-option', function () {
                    $(this).closest('.checkbox-item').remove();
                    saveOptions(repeater, stepIndex, inputIndex);
                });
                // Restore saved options
                if (input.options && input.options.length > 0) {
                    input.options.forEach((option, index) => {
                        const optionDiv = $(`
                            <div class="checkbox-item">
                                <input type="text" value="${option}" class="checkbox-option" placeholder="Option ${index + 1}">
                                <button type="button" class="delete-option">X</button>
                            </div>
                        `);
                        repeater.append(optionDiv);
                    });
                }
                // Input Event to Save Options
                repeater.on('input', '.checkbox-option', function () {
                    saveOptions(repeater, stepIndex, inputIndex);
                });
                repeater.append(addOptionBtn);
                inputDiv.append(repeater);
            }
            if (input.type === 'radio_group') {
                const repeater = $('<div class="radio-repeater"></div>');
            
                // Add Option Button
                const addOptionBtn = $('<button type="button" class="add-option">+ Add Option</button>');
                addOptionBtn.on('click', function () {
                    const newIndex = repeater.find('.radio-item').length + 1;
                    const newOption = $(`
                        <div class="radio-item">
                            <input type="radio" disabled>
                            <input type="text" value="" class="radio-option" placeholder="Option ${newIndex}">
                            <button type="button" class="delete-option">X</button>
                        </div>
                    `);
                    repeater.append(newOption);
                    saveOptions(repeater, stepIndex, inputIndex);
                });
            
                // Delete Option Event
                repeater.on('click', '.delete-option', function () {
                    $(this).closest('.radio-item').remove();
                    saveOptions(repeater, stepIndex, inputIndex);
                });
            
                // Restore saved options
                if (input.options && input.options.length > 0) {
                    input.options.forEach((option, index) => {
                        const optionDiv = $(`
                            <div class="radio-item">
                                <input type="radio" disabled>
                                <input type="text" value="${option}" class="radio-option" placeholder="Option ${index + 1}">
                                <button type="button" class="delete-option">X</button>
                            </div>
                        `);
                        repeater.append(optionDiv);
                    });
                }
            
                // Save Options on Input
                repeater.on('input', '.radio-option', function () {
                    saveOptions(repeater, stepIndex, inputIndex);
                });
            
                repeater.append(addOptionBtn);
                inputDiv.append(repeater);
            }
            if (input.type === 'date') {
                inputDiv.append(`
                    <input type="text" id="${input.id}" class="datepicker-input" placeholder="Select a date" />
                `);
            }
            
            // Handle required checkbox
            inputDiv.find('.required-checkbox').on('change', function () {
                const isChecked = $(this).is(':checked');
                formData[stepIndex].inputs[inputIndex].required = isChecked;
                if (isChecked) {
                    inputDiv.addClass('is_required');
                } else {
                    inputDiv.removeClass('is_required');
                }
                saveForm();
            });
            inputDiv.find('.input-type').on('change', function () {
                const newType = $(this).val();
                formData[stepIndex].inputs[inputIndex].type = $(this).val();

                // Clear dynamic fields when type changes
                inputDiv.find('.dynamic-fields').remove();

                // Remove any existing repeater to avoid duplicates
                inputDiv.find('.radio-repeater').remove();
                inputDiv.find('.checkbox-repeater').remove();
                if (newType === 'radio_group') {
                    const repeater = $('<div class="radio-repeater"></div>');
            
                    // Add Option Button
                    const addOptionBtn = $('<button type="button" class="add-option">+ Add Option</button>');
                    addOptionBtn.on('click', function () {
                        const newOptionDiv = $(`
                            <div class="radio-item">
                                <input type="radio" disabled>
                                <input type="text" value="" class="radio-option" placeholder="Option ${repeater.find('.radio-item').length + 1}">
                                <button type="button" class="delete-option">X</button>
                            </div>
                        `);
                        repeater.append(newOptionDiv);
            
                        // Save options immediately
                        const options = [];
                        repeater.find('.radio-option').each(function () {
                            const value = $(this).val().trim();
                            if (value !== '') {
                                options.push(value);
                            }
                        });
                        formData[stepIndex].inputs[inputIndex].options = options;
                        saveForm();
                    });
            
                    repeater.append(addOptionBtn);
            
                    // Delete Option Event
                    repeater.on('click', '.delete-option', function () {
                        $(this).closest('.radio-item').remove();
                        const options = [];
                        repeater.find('.radio-option').each(function () {
                            const value = $(this).val().trim();
                            if (value !== '') {
                                options.push(value);
                            }
                        });
                        formData[stepIndex].inputs[inputIndex].options = options;
                        saveForm();
                    });
            
                    // Input Event to Save Options
                    repeater.on('input', '.radio-option', function () {
                        const options = [];
                        repeater.find('.radio-option').each(function () {
                            const value = $(this).val().trim();
                            if (value !== '') {
                                options.push(value);
                            }
                        });
                        formData[stepIndex].inputs[inputIndex].options = options;
                        saveForm();
                    });
            
                    // Append the repeater to the input container
                    inputDiv.append(repeater);
                }
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

    function saveForm() {
        $('.step').each(function (stepIndex) {
            $(this).find('.input-item').each(function (inputIndex) {
                const inputType = $(this).find('.input-type').val();
                formData[stepIndex].inputs[inputIndex].type = inputType;
    
                if (inputType === 'checkbox_group') {    
                    // Save options for checkbox_group
                    const options = [];
                    $(this).find('.checkbox-option').each(function () {
                        const value = $(this).val().trim();
                        if (value !== '') { // Avoid saving empty options
                            options.push(value);
                        }
                    });
                    formData[stepIndex].inputs[inputIndex].options = options; // Save cleaned options
                }
                if (inputType === 'radio_group') {
                    const options = [];
                    $(this).find('.radio-option').each(function () {
                        const value = $(this).val().trim();
                        if (value !== '') { // Avoid saving empty options
                            options.push(value);
                        }
                    });
                    formData[stepIndex].inputs[inputIndex].options = options;
                }
                // Ensure IDs for date inputs are not lost or empty
                if (inputType === 'date' && !formData[stepIndex].inputs[inputIndex].id) {
                    formData[stepIndex].inputs[inputIndex].id = `datepicker${Date.now()}`;
                }
            });
        });
    
        hiddenInput.val(JSON.stringify(formData)); // Save to hidden input for persistence
    }
    $('form').on('submit', function () {
        saveForm();
    });
    
});
