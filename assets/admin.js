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

    // Render Form Steps and Inputs
    function renderForm() {
        formBuilder.html('');

        formData.forEach((step, stepIndex) => {
            const stepDiv = $(`
                <div class="step" data-step-index="${stepIndex}">
                    <div class="step-header">
                        <h3 contenteditable="true" class="step-title">${step.name}</h3>
                        <button type="button" class="delete-step-btn button button-danger">Delete Step</button>
                    </div>
                    <select class="step-condition">
                        <option value="">No Condition</option>
                        <option value="checkbox">Show if Checkbox</option>
                        <option value="select">Show if Select</option>
                    </select>
                    <button type="button" class="add-input-btn button">+ Add Input</button>
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
                    type: 'text',
                    required: false
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

        saveForm();
        makeSortable();
    }

    // Render Individual Inputs
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
                            <option value="checkbox" ${input.type === 'checkbox' ? 'selected' : ''}>Checkbox</option>
                        </select>
                        <input type="checkbox" ${input.required ? 'checked' : ''} /> Required
                        <button type="button" class="delete-input-btn button button-small button-danger">X</button>
                    </div>
                </div>
            `);

            inputDiv.find('label').on('input', function () {
                formData[stepIndex].inputs[inputIndex].label = $(this).text();
                saveForm();
            });

            inputDiv.find('.input-type').on('change', function () {
                formData[stepIndex].inputs[inputIndex].type = $(this).val();
                saveForm();
            });

            inputDiv.find('.delete-input-btn').on('click', function () {
                if (confirm('Delete this input?')) {
                    formData[stepIndex].inputs.splice(inputIndex, 1);
                    renderForm();
                    saveForm();
                }
            });

            container.append(inputDiv);
        });
    }

    // Make Steps and Inputs Sortable
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

            $('.inputs').each(function () {
                Sortable.create(this, {
                    animation: 150,
                    group: 'inputs',
                    onEnd: function (evt) {
                        const stepIndex = $(evt.from).data('step-index');
                        const item = formData[stepIndex].inputs.splice(evt.oldIndex, 1)[0];
                        formData[stepIndex].inputs.splice(evt.newIndex, 0, item);
                        saveForm();
                    }
                });
            });
        }
    }

    function saveForm() {
        hiddenInput.val(JSON.stringify(formData));
    }

    $('form').on('submit', function () {
        saveForm();
    });
});
