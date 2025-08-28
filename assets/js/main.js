var subheadings = [
  'develops things',
  'designs things',
  'pushes pixels',
  'css guru',
  'javascript ninja',
  'designs experiences',
  'plays ukulele',
  'blogs about design',
  'blogs about code',
  'internetologist',
  'oii msc hci css ftw',
  'make it so'
]

var changeSubheading = function(){
  var $s = $('#subheading')
  var html = random(subheadings)
  var height = $s.height()
  if(html != $s.html()){
    $s.animate({top: -height, opacity: 0}, 500, function(){
      $s.html(html).css({top: 'auto', bottom: -height}).animate({bottom: 0, opacity: 1}, 500)
    })
  }
}

var random = function(array){
  return array[Math.floor(Math.random() * array.length)]
}

// Header background changes according to time of day
var colours = {
  0: '#00161f',
  5: '#00161f',
  11: '#022131',
  14: '#02254a',
  16: '#022b5a', // 04:00
  19: '#324689',
  21: '#5e5b96',
  22: '#74638e',
  23: '#936e7d',
  25: '#e28c54', // 06:00
  27: '#ed943d',
  29: '#c59458', // 07:00
  31: '#1b89cf', // 08:00
  37: '#1b90e1', // 09:00
  41: '#1ba9f6',
  45: '#26b9ff', // 11:00
  55: '#26b9ff',
  64: '#2cacc4',
  67: '#66baa4', // 16:00
  69: '#a0c88a',
  71: '#d9d370', // 17:00
  73: '#e8cc5e',
  74: '#e8aa40',
  75: '#dd862b', // 18:00
  76: '#c7611a',
  77: '#a9410e',
  79: '#93360e', // 19:00
  81: '#752e13',
  83: '#531f0e', // 20:00
  85: '#322625',
  86: '#232529',
  87: '#15242d',
  89: '#022131'  // 22:00
}

var getColourForPercentage = function(percent){
  var match = colours[0]
  $.each(colours, function(startPercent, colour){
    if(percent > startPercent){
      match = colour
    }
  })
  return match
}

var updateHeaderColour = function(){
  var d = new Date()
  var h = d.getHours()
  var m = d.getMinutes()
  var percent = ((h + (m / 60)) / 24) * 100
  var colour = getColourForPercentage(percent)
  $('header').css('background-color', colour)
}

var trackOutboundLink = function(e){
  var gtag = window.gtag || function(){}
  var url = $(this).attr('href')
  var callback = function(){
    window.location.href = url
  }
  // If visitor is cmd/ctrl-clicking (to open link in a
  // new tab) bypass the redirect and let the new tab open.
  // Otherwise, stop the default link action.
  if(e.metaKey || e.ctrlKey){
    callback = function(){}
  } else {
    e.preventDefault()
  }
  // Register Google Analytics event for link url.
  // Then redirect to url on success.
  gtag('event', 'outbound_link_click', {
    outbound_url: url,
    event_callback: callback
  });
  // In case Google Analytics doesn't work,
  // redirect after 2 seconds anyway.
  setTimeout(callback, 2000);
}

$(function(){

  $('#menu-toggle').on('click', function(e){
    e.preventDefault();
    $('header nav').slideToggle()
    $(this).toggleClass('active')
    ga('send', 'event', 'menu', 'toggle')
  })

  $('nav li').each(function(i){
    var $li = $(this);
    if(i > 1 && i % 10 == 1){
      $('<li class="show-more">Show more</li>').insertBefore($li);
    }
    if(i > 10){
      $li.addClass('hidden');
    }
  })

  $('nav').on('click', '.show-more', function(e){
    e.preventDefault();
    $(this).hide().nextUntil('.show-more').removeClass('hidden');
  })

  $('header h1 a:first-child').on('click', function(){
    ga('send', 'event', 'heading', 'click')
  })

  $('#subheading').on('click', function(){
    ga('send', 'event', 'subheading', 'click')
    changeSubheading()
  }).css('cursor', 'pointer')

  $('footer a').on('click', function(){
    ga('send', 'event', 'footer links', 'click', $(this).attr('href'))
  })

  $('a[href^="http"]').on('click', trackOutboundLink)

  window.headingTimer = setInterval(changeSubheading, 15000)
  setTimeout(changeSubheading, 2000)
  window.colourTimer = setInterval(updateHeaderColour, 60000)
  updateHeaderColour()

  $.scrollDepth()

  $('a.footnote').on('mouseenter', function(){
    var $a = $(this);
    clearTimeout( $a.data('timer') );

    if ( $a.siblings('.footnote-preview').length ) {
      return;
    }

    var $sup = $(this).parent();
    var href = $(this).attr('href').replace(':', '\\:');
    var $footnote = $(href);
    var $preview = $('<div>').addClass('footnote-preview');

    $preview.html( $footnote.html() );
    var $reverse = $preview.find('.reversefootnote');
    var $finalp = $reverse.parent();
    $reverse.remove();
    if ( $.trim($finalp.text()) === '' ) {
      $finalp.remove();
    }
    $preview.appendTo($sup);

    $preview.on('mouseenter', function(){
      clearTimeout( $a.data('timer') );
    }).on('mouseleave', function(){
      var timer = setTimeout(function(){
        $preview.fadeOut(200, function(){
          $preview.remove();
        });
      }, 1000);
      $a.data('timer', timer);
    });

  }).on('mouseleave', function(){
    var $a = $(this);
    var $preview = $a.siblings('.footnote-preview');

    var timer = setTimeout(function(){
      $preview.fadeOut(200, function(){
        $preview.remove();
      });
    }, 1000);
    $a.data('timer', timer);

  });

  if ( 'imageLightbox' in $.fn ) {
    $('.image-gallery-item').imageLightbox({
      selector: 'class="lightbox"',
      animationSpeed: 50,
      onStart: function() {
        $('<div class="lightbox-overlay">').appendTo('body');
      },
      onEnd: function() {
        $('.lightbox-overlay').remove();
      },
      onLoadStart: function() {
        $('<div class="lightbox-loader spinner-border" role="status"><span class="visually-hidden">Loading…</span></div>').appendTo('body');
      },
      onLoadEnd: function() {
        $('.lightbox-loader').remove();
      },
    });
  }

})
