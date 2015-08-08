/* Subheading carousel */

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


/* Header background changes according to time of day */

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

var changeHeader = function(hexColour){
  $('header').animate({backgroundColor: hexColour}, 5000)
}

var checkTime = function(){
  var d = new Date()
  var h = d.getHours()
  var m = d.getMinutes()
  var percent = ((h + (m / 60)) / 24) * 100
  var colour = getColourForPercentage(percent)
  changeHeader(colour)
}

var setUpResponsiveVideos = function(){

  // cache the selector, so it's not repeated inside the resize()
  var $videos = $('iframe[src*="//www.youtube"], iframe[src*="//player.vimeo.com"]')

  // callback function to fire on resize()
  var resizeVideos = function(){
    $videos.each(function(){
      var w = $(this).parent().width()
      $(this).width(w).height( w * $(this).attr('ratio') )
    })
  }

  // set up the beginning aspect ratio
  $videos.each(function(){
    $(this).attr('ratio', this.height / this.width).removeAttr('width height')
  })

  // resize now, and on future window changes
  resizeVideos()
  $(window).on('resize', resizeVideos)

}

var trackOutboundLink = function(e){
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
  ga('send', 'event', 'outbound-link', 'click', url, {
    'hitCallback': callback
  })
  // In case Google Analytics doesn't work,
  // redirect after 2 seconds anyway.
  setTimeout(callback, 2000);
}


/* Dom ready */

$(function(){

  $('#menu-toggle').on('click', function(e){
    e.preventDefault();
    $('header nav').slideToggle()
    $(this).toggleClass('active')
    ga('send', 'event', 'menu', 'toggle')
  })

  $('nav .show-more a').on('click', function(e){
    e.preventDefault();
    $(this).parent().hide().nextUntil('.show-more').removeClass('hidden');
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
  window.colourTimer = setInterval(checkTime, 5000)
  checkTime()

  $.scrollDepth()

  setUpResponsiveVideos()

})
