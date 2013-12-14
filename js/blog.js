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
  var $s = $('h1 small')
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
  16: '#022b5a',
  21: '#5e5b96',
  27: '#ed943d',
  34: '#1b89cf',
  45: '#26b9ff',
  55: '#26b9ff',
  64: '#2cacc4',
  73: '#e8cc5e',
  77: '#a9410e',
  89: '#022131',
  100: '#00161f'
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
  var $toggle = $('<span id="menu-toggle"><i></i><i></i><i></i></span>').on('click', function(){
    $('header nav').slideToggle()
    $(this).toggleClass('active')
  })
  $('header nav').hide()
  $('header h1').prepend($toggle)

  window.headingTimer = setInterval(changeSubheading, 15000)
  setTimeout(changeSubheading, 2000)
  window.colourTimer = setInterval(checkTime, 5000)
  checkTime()
})
