import $ from 'jquery';
import 'slick-carousel';
import 'slick-carousel/slick/slick.css';

import 'slick-carousel/slick/slick-theme.css';

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

export function initCarousel() {
  $('.carousel').slick(CAROUSEL_CONFIG);

  $('.carousel-prev, .carousel-gradient--left').on('click', function () {
    $('.carousel').slick('slickPrev');
  });

  $('.carousel-next, .carousel-gradient--right').on('click', function () {
    $('.carousel').slick('slickNext');
  });
}