jQuery(document).ready(function ($) {
    const planRepeater = $('#plan-repeater');
    let planCount = planRepeater.find('.plan-item').length;

    // Add Plan
    $('#add-plan').on('click', function () {
        const newPlan = `
            <div class="plan-item">
                <label>نام</label>
                <input type="text" name="sproduct_plans[${planCount}][name]" required />

                <label>مدت</label>
                <input type="number" class="days-field" name="sproduct_plans[${planCount}][days]" required />

                <label>قیمت</label>
                <input type="number" class="price-field" name="sproduct_plans[${planCount}][price]" required />

                <label>توضیحات</label>
                <textarea name="sproduct_plans[${planCount}][description]"></textarea>

                <button type="button" class="remove-plan button">حذف</button>
            </div>
        `;
        planRepeater.append(newPlan);
        planCount++;
    });

    // Remove Plan
    $(document).on('click', '.remove-plan', function () {
        $(this).closest('.plan-item').remove();
        planCount--;
    });

    // Enforce Numeric Validation on Days and Price Fields
    $(document).on('input', '.days-field, .price-field', function () {
        const input = $(this);
        const value = input.val().replace(/[^\d]/g, '');  // Remove non-numeric characters
        input.val(value);  // Update the input value
        showError(input, value);
    });

    // Show Error if Non-Numeric Characters are Entered
    function showError(input, value) {
        const errorMessage = 'Please enter only numbers.';
        input.next('.input-error').remove();  // Remove previous error
        if (value === '') {
            input.after(`<div class="input-error" style="color: red; margin-top: 5px;">${errorMessage}</div>`);
        }
    }
});
