/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(1);
module.exports = __webpack_require__(2);


/***/ }),
/* 1 */
/***/ (function(module, exports) {

// init Masonry
var $grid = $('.grid').masonry({
    // options...
    itemSelector: '.grid-item',
    columnWidth: '.grid-sizer',
    percentPosition: true,
    horizontalOrder: true
});

// layout Masonry after each image loads
$grid.imagesLoaded().progress(function () {
    $grid.masonry('layout');
});

$('.dropdown-btn').on('click', function () {
    $(this).siblings('.dropdown').toggle();
    $grid.imagesLoaded().progress(function () {
        $grid.masonry('layout');
    });
});

$('.more').on('click', function () {
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

$('.search-input').bind('paste keyup', function () {
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
        }).done(function (data) {
            if (data.response == true) {
                /*si hay resultados*/
                $('.search-results').html('<div class="inner"></div>');
                /*$('.loop').html('');*/
                $.each(data.result, function (key, val) {

                    var html = '<div class="search-item">\n                        \n                        <a href="' + path + '/' + val.slug + '">\n                            <div class="search-title">' + val.title + '</div>\n                            <ul class="card-tags">\n                                <li class="card-tags-details">' + val.year + '</li>    \n                                <li class="card-tags-details break">' + val.country + '</li>                           \n                            </ul>\n                        </a>\n\n                     </div>';
                    $('.search-results .inner').append(html);
                });
            } else {
                $('.search-results').html('');
                console.log('response = false');
            }
        }).fail(function () {
            console.log('no se envia');
        });
    } else {
        //si tiene menos de 3 carácteres
        $('.search-results').html('');
    }
});

$('.search-input').focusout(function () {
    $('.search-results').fadeOut(300);
});

$('.search-input').focusin(function () {
    //para que solo aparezcan si hay resultados
    if ($('.search-item').length && $(this).length) {
        $('.search-results').fadeIn(300);
    }
});

/***/ }),
/* 2 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })
/******/ ]);