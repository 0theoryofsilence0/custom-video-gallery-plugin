jQuery(document).ready(function($) {
    
    $('.filter-category').on('click', function() {
        if ($(this).attr('id') === 'all-videos') {
            $('.filter-category').removeClass('active');
            $(this).addClass('active');
            $('.video-item').show();
        } else {
            $(this).toggleClass('active');
            $('#all-videos').removeClass('active');

            var activeCategories = $('.filter-category.active').map(function() {
                return '.category-' + $(this).data('category');
            }).get();

            if (activeCategories.length > 0) {
                $('.video-item').hide();
                $(activeCategories.join(',')).show();
            } else {
                $('#all-videos').addClass('active');
                $('.video-item').show();
            }
        }
    });

    // Initially display all videos
    $('#all-videos').addClass('active');
    $('.video-item').show();

    $(".video-link").magnificPopup({
        type: "iframe"
    });
    
});
