import './../styles/style.scss';

import { initCookiesBanner } from './accept-cookies.js';
import { initNavbar } from './navbar.js';

//init page logic
document.addEventListener(
  'DOMContentLoaded',
  function () {
    initNavbar();
    initCookiesBanner();
  },
  { once: true },
);
