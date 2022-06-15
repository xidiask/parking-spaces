

/* ------------------------ SCREEN RESOLUTION - RESIZE ------------------------ */


var doit;
$(window).resize(function(){
  clearTimeout(doit);
  doit = setTimeout(afterResize, 200);
});

//AFTER RESIZE CALL FUNCTIONS

function afterResize(){
	screenResolution();
}


$(document).ready(screenResolution);

function screenResolution() {
  var classes,
  breakpoint = getBreakpoint();
  classes = ['xs', 'sm', 'md', 'lg', 'xl', 'xxl'];
  $.each(classes, function(index, value){
	  if($('body').hasClass(value))
		  $('body').removeClass(value);
  });
  $('body').addClass(breakpoint).trigger('changeResolution');
}


function getBreakpoint() {
	var breakpoint,
	width = $(window).width();
	if (width < 576)
		breakpoint = 'xs';
	else if (width < 768)
		breakpoint = 'sm';
	else if (width < 992)
		breakpoint = 'md';
	else if (width < 1200)
		breakpoint = 'lg';
	else if (width < 1400)
		breakpoint = 'xl';
	else
		breakpoint = 'xxl';
	return breakpoint;
}

function touchDevice() {
	if(('ontouchstart' in window) || (navigator.MaxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0)) {
		$('body').removeClass('no-touch');
		$('body').addClass('touch'); //this is a touch device
	}else {
		$('body').removeClass('touch');
		$('body').addClass('no-touch'); //it's not touch device
	}
}


function fixMenuWidth() {
	$('nav.navbar').css('width', vw(100)-getScrollBarWidth());
	$('.cookies.fixed-bottom').css('width', vw(100)-getScrollBarWidth());
}

function fixMenuHeight() {
	if($.inArray(getBreakpoint(), ['xs', 'sm', 'md']) !== -1)
		$('.fix-menu-height').css('height', $('nav.navbar.fixed-top').innerHeight());
	else
		$('.fix-menu-height').css('height', 0);
}

// SCROLLBAR WIDTH
function getScrollBarWidth() {
	var inner = document.createElement('p');
	inner.style.width = "100%";
	inner.style.height = "200px";
  
	var outer = document.createElement('div');
	outer.style.position = "absolute";
	outer.style.top = "0px";
	outer.style.left = "0px";
	outer.style.visibility = "hidden";
	outer.style.width = "200px";
	outer.style.height = "150px";
	outer.style.overflow = "hidden";
	outer.appendChild (inner);
  
	document.body.appendChild (outer);
	var w1 = inner.offsetWidth;
	outer.style.overflow = 'scroll';
	var w2 = inner.offsetWidth;
	if (w1 == w2) w2 = outer.clientWidth;
  
	document.body.removeChild (outer);
  
	return (w1 - w2);
  };

$(document).on('changeResolution', function() {
	fixMenuWidth();
	fixMenuHeight();
	touchDevice();
});

/* ------------------------ CALCULATE VH & VW (PX) ------------------------ */

function vh(v) {
    var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
    return (v * h) / 100;
  }
  
  function vw(v) {
    var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    return (v * w) / 100;
  }


	// FORM CONTROL
	$('body').on('focus', '.form-control', function (e) {
		e.preventDefault();
		if($(this).val() == '' && !$(this).hasClass('bootstrap-select'))
			$(this).addClass('has-value');
	});
	$('body').on('focusout', '.form-control', function (e) {
		e.preventDefault();
		if ($(this).val() == '' && !$(this).hasClass('bootstrap-select'))
			$(this).removeClass('has-value');
	});
	/*
	$('.form-control').each(function(){
		if($(this).val() != '')
			$(this).addClass('has-value');
	});
	*/
	// selectpicker
	$('body').on('change', '.bootstrap-select', function (e) {
		e.preventDefault();
		var object = $(this);
		object.closest('form').find('button[type=submit]').prop('disabled', false);
		if(typeof object.find("option:selected").val() !== 'undefined')
			object.addClass('has-value');
		else
			object.removeClass('has-value');
		//console.log(object.find('select').val());
	});

	// uncheck radio
	var previous_value = [];
	$('body').on('focusin', '.form-check-input.uncheck', function() {
		var object = $(this),
		name = object.attr('name');
		previous_value[name] = object.filter('input:checked').val();
    });
    $('body').on('click', '.form-check-input.uncheck', function() {
		var object = $(this),
		name = object.attr('name');
		//console.warn('@@@@@');
		//console.error('####');
 		if(previous_value[name] == object.val())
			object.prop('checked', false);
		else
			object.prop('checked', true);
    });



/* 
 $(document).ready(function(){
    setInterval(function(){
        $('.item').each(function(index) {
            var object = $(this);
            setTimeout(function(){
                object.toggleClass('red');
                object.toggleClass('green');
            },Math.floor((Math.random() * 8000) + 1000));
        });
    },10000);
 });
  */

 $(document).ready(function(){

 
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (event) {
        //console.log(event.target);
        //console.log(event.relatedTarget);
        var selected = $(event.target).data('floor-id');
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: $.param({
              cookie: 'floor',
              selected: selected
            }),
            dataType: 'html',
            async: true
        });
      });

	/* ------------------------ POPOVER - TOOLTIP - TITLES ------------------------ */

	$('a[title]').hover(function(e){
		e.preventDefault();
	    $(this).attr('title', '');
	});

	$(function () {
		$('.popover-top').popover({
			html: true,
			placement: 'top',
			content: function() {
				var object = $(".input-move-to").clone();
				object.removeClass('d-none');
				return object;
			}
		})
	})
	$('.popover-top').on('click', function() {
		var object = $(this);
		if(!object.hasClass('clicked')) {
			$('.popover-top').each(function(){
				$(this).popover('hide');
				$(this).removeClass('clicked');
			});	
			object.popover('show');
			object.addClass('clicked');
		}
		else {
			object.popover('hide');
			object.removeClass('clicked');
		}
	});

	$(document).click(function(event) { 
		var $target = $(event.target);
		if(!$target.closest('.popover-top').length && !$target.closest('.popover').length) {
			$('.popover-top').each(function(){
				$(this).popover('hide');
				$(this).removeClass('clicked');
			});	
		}        
	});
	/* 
		$('.popover-top').on('show.bs.popover hidden.bs.popover', function () {
			// do something...
			console.log('toggle');
		}) */

});


 