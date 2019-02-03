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

$('.more').on('click', function() {
    $('.actors .hide').addClass('show-inline');
    $(this).addClass('hide');
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

/************************************************************************************************
    SEARCH BAR
************************************************************************************************/

$('.search-input').bind('paste keyup', function() {
    var t = $(this);
    var string = t.val();
    var ilength = string.length;
    var url = $('.search-box form').data('url'); 
    var path = $('.search-box form').data('path');
    if (ilength > 2) {  
        $.ajax({
            url: url,
            type: 'POST',
            data: { 'string': string }
        }).done(function(data) {
            if (data.response == true) { /*si hay resultados*/
                $('.search-results').html('<div class="inner"></div>');
                /*$('.loop').html('');*/
                $.each(data.result, function(key,val) {
                    
                    var html = 
                    `<div class="search-item">
                        
                        <a href="` + path + `/` + val.slug + `">
                            <div class="search-title">` + val.title + `</div>
                            <ul class="card-tags">
                                <li class="card-tags-details">` + val.year + `</li>    
                                <li class="card-tags-details break">` + val.country + `</li>                           
                            </ul>
                        </a>

                     </div>`;
                    $('.search-results .inner').append(html);
                });
            } else {
                $('.search-results').html('');
                console.log('response = false')
            }
        }).fail(function() {
            console.log('no se envia');
        });
    } else { //si tiene menos de 3 carácteres
        $('.search-results').html('');
    }
});

$('.search-input').focusout(function() {
    $('.search-results').fadeOut(300);
});     

$('.search-input').focusin(function() {
    //para que solo aparezcan si hay resultados
    if ($('.search-item').length && $(this).length) {
        $('.search-results').fadeIn(300);
    }
})