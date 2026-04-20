jQuery(document).ready(function() {

    if (typeof jQuery.fn.wpalSelect2 === "function") {
        // Simple Dropdown
        jQuery('.basic-single').wpalSelect2();
        jQuery('.roles-selector').wpalSelect2();

        if (typeof actionsetlist !== 'undefined') {
            jQuery('.actionsetdropdown').wpalSelect2({
                data: actionsetlist
            });
        }

        if (typeof pagelist !== 'undefined') {
            jQuery('.pagelistdropdown').wpalSelect2({
                data: pagelist
            });
        }

        if (typeof taglist !== 'undefined') {
            jQuery('.taglistdropdown').wpalSelect2({
                data: taglist
            });
            jQuery('.multitaglist,.tag-selector').wpalSelect2({
                data: taglist,
                multiple: true
            });
        }

        if (typeof taglist2 !== 'undefined') {
            jQuery('.taglistdropdown2').wpalSelect2({
                data: taglist2
            });
            jQuery('.multitaglist2,.tag-selector2').wpalSelect2({
                data: taglist2,
                multiple: true
            });
        }

        jQuery('.disabledmultitaglist').wpalSelect2({});

        if (typeof requiredtaglist !== 'undefined') {
            jQuery('.requiredtaglistdropdown').wpalSelect2({
                data: requiredtaglist
            });
        }

        if (typeof membershiptaglist !== 'undefined') {
            jQuery('.membershiptaglistdropdown').wpalSelect2({
                data: membershiptaglist
            });
        }

        if (typeof themelist !== 'undefined') {
            jQuery('.themelistdropdown').wpalSelect2({
                data: themelist
            });
        }

        if (typeof productlist !== 'undefined') {
            jQuery('.multiproductlistdropdown').wpalSelect2({
                data: productlist,
                multiple: true
            });
            jQuery('.productlistdropdown').wpalSelect2({
                data: productlist
            });
        }

        if (typeof capabilitylist !== 'undefined') {
            jQuery('.capabilitylistdropdown').wpalSelect2({
                data: capabilitylist,
            });
        }
    }



    jQuery(".memberium-copy-button").on("click", function() {
        // Find the input field and get its value
        var inputValue = jQuery(this).prev("input").val();
        // Create a temporary input element to copy the value to clipboard
        var tempInput = jQuery("<input>");
        jQuery("body").append(tempInput);
        tempInput.val(inputValue).select();
        document.execCommand("copy");
        tempInput.remove();
        // Display a success message
        alert("Copy to clipboard completed.");
    });

});