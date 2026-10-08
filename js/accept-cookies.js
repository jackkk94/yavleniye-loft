const COOCKIES_BLOCK_BTN_IDS = {
  ACCEPT: 'cookiesAcceptAll',
  NECESSARY: 'cookiesAcceptNecessary',
};

const COOKIES_BANNER_ID = 'coockiesBanner';
const LOCAL_STORAGE_KEY = 'coockies';
const COOCKIES_BANNER_LINK = 'cookie-banner-link';

function initCookiesBanner() {
  const banner = document.getElementById(COOKIES_BANNER_ID);
  let meta;
  try {
    meta = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY) ?? '{}');
  } catch (e) {
    meta = {};
  }

  if (!meta?.expires) {
    banner.style.display = 'flex';
    return;
  }

  const expires = new Date(meta.expires);
  const currentDate = new Date();

  if (expires - currentDate <= 0) {
    //показываем баннер и очищаем стор
    banner.style.display = 'flex';
    localStorage.removeItem(LOCAL_STORAGE_KEY);
  }

  console.log(expires - currentDate);
}

const initCookiesBannerLinkListener = () => {
  const link = document.querySelector(`.${COOCKIES_BANNER_LINK}`);
  if (!link) {
    return;
  }

  link.addEventListener('click', () => {
    const banner = document.getElementById(COOKIES_BANNER_ID);
    if (!banner) {
      return;
    }

    localStorage.removeItem(LOCAL_STORAGE_KEY);
    banner.style.display = 'flex';
  });
};

const addBtnClickListener = (btnId, fn) => {
  const banner = document.getElementById(COOKIES_BANNER_ID);
  const btn = document.getElementById(btnId);
  if (!btn) {
    return;
  }

  btn.addEventListener('click', () => {
    const date = new Date();
    date.setFullYear(date.getFullYear() + 1);
    const expires = date.getTime();

    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify({ type: btnId, expires }));

    if (banner) {
      banner.style.display = 'none';
    }

    fn?.(btnId);
  });
};

addBtnClickListener(COOCKIES_BLOCK_BTN_IDS.ACCEPT, () => alert(1));
addBtnClickListener(COOCKIES_BLOCK_BTN_IDS.NECESSARY);
