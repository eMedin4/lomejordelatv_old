// init Masonry
var $grid = $('.grid').masonry({
    // options...
    itemSelector: '.grid-item',
    columnWidth: '.grid-sizer',
    percentPosition: true,
    horizontalOrder: true
});

// layout Masonry after each image loads
$grid.imagesLoaded().progress( function() {
    $grid.masonry('layout');
});

$('.dropdown-btn').on('click', function() {
    $(this).siblings('.dropdown').toggle();
    $grid.imagesLoaded().progress( function() {
        $grid.masonry('layout');
    });
});