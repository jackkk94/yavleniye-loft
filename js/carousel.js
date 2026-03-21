const CAROUSEL_CONFIG = {
  infinite: false,
  arrows: false,
  initialSlide: 0,
  slidesToScroll: 1,
  slidesToShow: 1,
  variableWidth: true,
  centerMode: false,
  focusOnChange: false,
  focusOnSelect: false,
  draggable: true,
  swipe: true,
  swipeToSlide: true,
  responsive: [
    {
      breakpoint: 450,
      settings: {
        slidesToShow: 1,
      },
    },
  ],
};


$(document).on('ready', function () {
  $('.carousel').slick(CAROUSEL_CONFIG);

  $('.carousel-prev, .carousel-gradient--left').on('click', function () {
    $('.carousel').slick('slickPrev');
  });

  $('.carousel-next, .carousel-gradient--right').on('click', function () {
    $('.carousel').slick('slickNext');
  });
});