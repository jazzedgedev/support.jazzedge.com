(function($){
    function injectSelect2(inputName, globalVar) {
        $(`input[name="${inputName}"]`).each(function() {
            var $input = $(this);
            if ($input.data('select2-injected')) return;
            $input.data('select2-injected', true);
            $input.hide();

            var $select = $('<select multiple class="my-avada-select2" style="width:100%"></select>');
            if (window[globalVar]) {
                window[globalVar].forEach(function(opt) {
                    $select.append($('<option>', {value: opt.id, text: opt.text}));
                });
            }

            var selected = $input.val() ? $input.val().split(',') : [];
            $select.val(selected);

            $input.after($select);
            $select.select2();

            $select.on('change', function() {
                $input.val($(this).val() ? $(this).val().join(',') : '');
            });

            $select.val(selected).trigger('change');
        });
    }

    function observeInputs(inputName, globalVar) {
        injectSelect2(inputName, globalVar);

        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    injectSelect2(inputName, globalVar);
                }
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    observeInputs('memberium-tags', 'MEMBERIUM_TAGS');
    observeInputs('memberium-memberships', 'MEMBERIUM_MEMBERSHIPS');
})(jQuery);