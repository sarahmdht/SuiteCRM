(function checkQuickCreateLoaded() {

    var interval = setInterval(function () {
        const form = document.querySelector('form[name="form_SubpanelQuickCreate_Custom_Module"]');
    
        if (form) {
    
            // Get values from DetailView
            const field1 = document.querySelector('.detail-view-row-item[data-field="field_1_c"] #field_1_c')?.textContent?.trim();
            const field2 = document.querySelector('.detail-view-row-item[data-field="lfield_2"] #field_2')?.value?.trim();  //In case of Dropdown
            const field3 = document.querySelector('.detail-view-row-item[data-field="field_3"] #field_3')?.textContent?.trim();
    
            // Now autofill the form fields
            if (field1) {
                const opp1Field = form.querySelector('input[name="field1"]');
                if (opp1Field) opp1Field.value = field1;
            }
    
            if (field2) {
                const opp2Field = form.querySelector('select[name="opp2"]');
                if (opp2Field) opp2Field.value = field2;
            }
    
            if (field3) {
                const opp3Field = form.querySelector('input[name="opp3"]');
                if (opp3Field) opp3Field.value = filed3;
            }
    
            clearInterval(interval);
        }
    }, 500);
    })();