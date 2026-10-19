import './../styles/style.scss';
import { initTooltips } from './tooltip.js';
import { initCarousel } from './carousel.js';
import { listenControlsChange } from './controls.js';
import { listenCalculatorFormChange, handleCalculatorFormChange } from './calculator.js';
import { initRequestForm } from './request.js';
import { initCookiesBanner, initCookiesBannerLinkListener } from './accept-cookies.js';
import { openModal } from './modal.js';
import { initNavbar } from './navbar.js';

const LOCATION_VIDEO_MODAL_ID = 'locationVideoModal';
const VIDEO_CONTAINER_ID = 'videoPlayer';

function listenOpenLocationVideoButtonClick() {
  const btn = document.getElementById('openLocationVideo');
  if (!btn) {
    return;
  }

  btn.addEventListener('click', () => openLocationVideo());
}

function openLocationVideo() {
  openModal(LOCATION_VIDEO_MODAL_ID, VIDEO_CONTAINER_ID);
}

document.addEventListener(
  'DOMContentLoaded',
  function () {
    initNavbar();
    initCarousel();
    initTooltips();
    initCookiesBanner();
    initCookiesBannerLinkListener();
    listenControlsChange();
    listenCalculatorFormChange();
    handleCalculatorFormChange();
    initRequestForm();
    listenOpenLocationVideoButtonClick();
  },
  { once: true },
);
