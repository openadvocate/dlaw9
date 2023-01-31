/**
 * Hero Slider function using the ID "carouselHero" and class "show-neighbors" on the same div
 * Use this with the _hero-slider.scss
 * This function will make the hero images full height of the viewport on page load that uses background-image
 * and prepend and append previous and next slides on each sides of the currently "active" slide
 * 
 * @author Jay Lee
 */
const heroSlider = () => {
  if ($(window).width() >= 992) {
    $('#carouselHero .carousel-item__img').css('height', $(window).height() - 120);
  } else if ($(window).width() >= 768) {
    $('#carouselHero .carousel-item__img').css('height', 402);
  } else {
    $('#carouselHero .carousel-item__img').css('height', 226);
  }

  if ($(window).width() >= 768) {
    $('.show-neighbors .carousel-item').each(function() {
      let next = $(this).next();
      if (!next.length) {
        next = $(this).siblings(':first');
      }
      next.children(':first-child').clone().appendTo($(this));
    }).each(function() {
      let prev = $(this).prev();
      if (!prev.length) {
        prev = $(this).siblings(':last');
      }
      prev.children(':nth-last-child(2)').clone().prependTo($(this));
    });
  }
}
