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


/* Dom ready */

$(function(){

  var $toggle = $('<span id="menu-toggle"><i class="first"></i><i class="second"></i><i class="third"></i></span>').on('click', function(){
    $('header nav').slideToggle()
    $(this).toggleClass('active')
    ga('send', 'event', 'menu', 'toggle')
  })

  $('header nav').hide()

  $('header h1').prepend($toggle).on('click', function(){
    ga('send', 'event', 'heading', 'click')
  })

  $('#subheading').on('click', function(){
    ga('send', 'event', 'subheading', 'click')
    changeSubheading()
  }).css('cursor', 'pointer')

  $('footer a').on('click', function(){
    ga('send', 'event', 'footer links', 'click', $(this).attr('href'))
  })

  window.headingTimer = setInterval(changeSubheading, 15000)
  setTimeout(changeSubheading, 2000)
  window.colourTimer = setInterval(checkTime, 5000)
  checkTime()

})
