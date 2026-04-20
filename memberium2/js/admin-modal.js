jQuery(document).ready(function($) {
    // Check if the modal div exists
    var $modal = $('#memberium-custom-modal');
    if ($modal.length) {
        // Show the modal
        $modal.show();

        // Close the modal when the close button is clicked
        $('.custom-close').on('click', function() {
            $modal.hide();
        });

        // Close modal when clicking outside of the modal content
        $(window).on('click', function(event) {
            if ($(event.target).is('#custom-modal')) {
                $modal.hide();
            }
        });
    }
});