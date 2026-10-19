const COOCKIES_BLOCK_BTN_IDS = {
  ACCEPT: 'cookiesAcceptAll',
  NECESSARY: 'cookiesAcceptNecessary',
};

const COOKIES_BANNER_ID = 'coockiesBanner';
const LOCAL_STORAGE_KEY = 'coockies';
const COOCKIES_BANNER_LINK = 'cookie-banner-link';

function initYm() {
  (function (m, e, t, r, i, k, a) {
    m[i] =
      m[i] ||
      function () {
        (m[i].a = m[i].a || []).push(arguments);
      };
    m[i].l = 1 * new Date();
    for (var j = 0; j < document.scripts.length; j++) {
      if (document.scripts[j].src === r) {
        return;
      }
    }
    ((k = e.createElement(t)),
      (a = e.getElementsByTagName(t)[0]),
      (k.async = 1),
      (k.src = r),
      a.parentNode.insertBefore(k, a));
  })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js?id=113555078', 'ym');

  ym(113555078, 'init', {
    ssr: true,
    webvisor: true,
    clickmap: true,
    ecommerce: 'dataLayer',
    referrer: document.referrer,
    url: location.href,
    accurateTrackBounce: true,
    trackLinks: true,
  });
}

export function initCookiesBanner() {
  addBtnClickListener(COOCKIES_BLOCK_BTN_IDS.ACCEPT, () => initYm());
  addBtnClickListener(COOCKIES_BLOCK_BTN_IDS.NECESSARY);

  const banner = document.getElementById(COOKIES_BANNER_ID);
  let meta;
  try {
    meta = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY) ?? '{}');
  } catch (e) {
    meta = {};
  }

  if (!meta?.expires) {
    if (banner) {
      banner.style.display = 'flex';
    }

    return;
  }

  const expires = new Date(meta.expires);
  const currentDate = new Date();

  if (expires - currentDate <= 0) {
    if (banner) {
      banner.style.display = 'flex';
    }

    localStorage.removeItem(LOCAL_STORAGE_KEY);
    return;
  }

  if (meta?.type === COOCKIES_BLOCK_BTN_IDS.ACCEPT) {
    initYm();
  }
}

export function initCookiesBannerLinkListener() {
  const link = document.querySelectorAll(`.${COOCKIES_BANNER_LINK}`);
  if (!link) {
    return;
  }

  link.forEach((l) =>
    l.addEventListener('click', () => {
      const banner = document.getElementById(COOKIES_BANNER_ID);
      if (!banner) {
        return;
      }

      localStorage.removeItem(LOCAL_STORAGE_KEY);
      banner.style.display = 'flex';
      window.location?.reload?.();
    }),
  );
}

function addBtnClickListener(btnId, fn) {
  const banner = document.getElementById(COOKIES_BANNER_ID);
  if (!banner) {
    return;
  }

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
}
