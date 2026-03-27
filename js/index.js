//**Initialize
const MENU_TOGGLER = document.getElementById('menuToggler');
const MENU_CLOSE = document.getElementById('closeMenu');
const ACCORDEON_EL_OPEN_CLASS = 'active';
const LOCATION_VIDEO_MODAL_ID = 'locationVideoModal';
const VIDEO_CONTAINER_ID = 'locationVideo';
const LOCATION_VIDEO_SRC = 'https://vk.com/video_ext.php?oid=-75418384&id=456240159';

//**/Initialize

document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener('click', function (e) {
    const id = e.target.hash;
    if (!id) {
      return;
    }

    const el = document.getElementById(id);
    if (el) {
      document.getElementById(el).scrollIntoView({
        behavior: 'smooth',
        block: 'start',
      });
    }
  });
});

document.querySelectorAll('.accordeon-item .title').forEach((el) => {
  el.addEventListener('click', function (e) {
    const parent = el.parentElement;
    if (parent.classList.contains(ACCORDEON_EL_OPEN_CLASS)) {
      parent.classList.remove(ACCORDEON_EL_OPEN_CLASS);
      return;
    }
    parent.classList.add(ACCORDEON_EL_OPEN_CLASS);
  });
});

const MENU_MOBILE_CLASS = 'menu-mobile';
const MENU_OPENED_CLASS = 'body-menu-opened';

MENU_TOGGLER.addEventListener('click', async () => {
  const el = document.querySelector(`.${MENU_MOBILE_CLASS}`);
  document.body.classList.toggle(MENU_OPENED_CLASS);
  if (el) {
    el.classList.toggle('opened');
  }
});

MENU_CLOSE.addEventListener('click', async () => {
  document.querySelector(`.${MENU_MOBILE_CLASS}`)?.classList?.remove?.('opened');
  document.body.classList.remove(MENU_OPENED_CLASS);
});

function openLocationVideo() {
  openModal(LOCATION_VIDEO_MODAL_ID, { videoContainerId: VIDEO_CONTAINER_ID, videoSrc: LOCATION_VIDEO_SRC });
}

//init page logic
$(document).on('ready', function () {
  initCarousel();
  listenControlsChange();
  handleCalculatorFormChange();
  initRequestForm();
}); 
