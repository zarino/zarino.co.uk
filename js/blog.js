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

$(function(){
  var $toggle = $('<span id="menu-toggle"><i></i><i></i><i></i></span>').on('click', function(){
    $('header nav').slideToggle()
    $(this).toggleClass('active')
  })
  $('header nav').hide()
  $('header h1').prepend($toggle)
  
  var timer = setInterval(changeSubheading, 20000)
})