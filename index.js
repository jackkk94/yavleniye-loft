//**Initialize

const COPY_TEXT_BUTTON = document.getElementById('textToCopy');
const MENU_TOGGLER = document.getElementById('menuToggler');
const COPY_TEXT_SUCCESS_MESSAGE = 'Текст скопирован';
const COPY_TEXT_ERROR_MESSAGE = 'Не удалось скопировать текст: ';
const TEXT_TO_COPY = 'Пожертвование для МРГ "Явление Духа и Силы"';
const ACCORDEON_EL_OPEN_CLASS = 'active';
const TOOLTIP_ICONS = document.querySelectorAll('.icon-tooltip');
//**/Initialize

$(document).on('ready', function () {
  $('.carousel').slick({
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
  });

  $('.carousel-prev, .carousel-gradient--left').on('click', function () {
    $('.carousel').slick('slickPrev');
  });

  $('.carousel-next, .carousel-gradient--right').on('click', function () {
    $('.carousel').slick('slickNext');
  });

  const ranges = document.querySelectorAll('.range-input');
  ranges?.forEach((range) => {
    range.addEventListener('input', handleInputRange);
  });
});

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

MENU_TOGGLER.addEventListener('click', async () => {
  const el = document.querySelector('.menu-mobile');
  if (el) {
    el.classList.toggle('opened');
  }
});

const handleModalClick = ({ currentTarget, target }) => {
  const isClickedOnBackdrop = target === currentTarget;

  if (isClickedOnBackdrop) {
    currentTarget.close();
  }
};

function openModal(id) {
  if (!id) return;
  const modal = document.getElementById(id);
  if (!modal) return;

  modal.showModal();
  setTimeout(() => {
    modal.addEventListener('click', handleModalClick);
  }, 0);
}


//** input range logic */
function handleInputRange(event) {
  event.target.nextElementSibling.value = event.target.value + ' ч';
  const input = event.target;
  const labelWidth = input.parentNode.querySelector('.range-output')?.offsetWidth;

  const min = input.min ? +input.min : 0;
  const max = input.max ? +input.max : 100;
  const currentValue = +input.value;

  const baseOffset = ((currentValue - min) / (max - min)) * 100 || 1;
  const percent = `${baseOffset}% - ${labelWidth / 2}px + ${12 / baseOffset}px`;
  input.parentNode.style.setProperty('--offset-x', percent);
}
//** /input range logic */


//**tooltip logic
const showEvents = ['mouseenter', 'focus'];
const hideEvents = ['mouseleave', 'blur'];

TOOLTIP_ICONS?.forEach((icon) => {
  const tooltipId = icon.getAttribute('aria-describedby');
  if(!tooltipId) {
    return;
  }

  const tooltipContent = document.querySelector('#' + tooltipId);
  const tooltipInstance = Popper.createPopper(icon, tooltipContent, {
    modifiers: [
      {
        name: 'offset',
        options: {
          offset: [0, 8],
          flipVariations: true,
        },
      },
    ],
  });

  showEvents.forEach((event) => {
    icon.addEventListener(event, () => showTooltip(tooltipInstance, tooltipContent));
  });

  hideEvents.forEach((event) => {
    icon.addEventListener(event, () => hideTooltip(tooltipInstance, tooltipContent));
  });
});

function showTooltip(tooltipInst, tooltipContainer) {
  tooltipContainer.setAttribute('data-show', '');

  tooltipInst.setOptions((options) => ({
    ...options,
    modifiers: [...options.modifiers, { name: 'eventListeners', enabled: true }],
  }));

  tooltipInst.update();
}

function hideTooltip(tooltipInst, tooltipContainer) {
  tooltipContainer.removeAttribute('data-show');

  tooltipInst.setOptions((options) => ({
    ...options,
    modifiers: [...options.modifiers, { name: 'eventListeners', enabled: false }],
  }));
}

//**/tooltip logic
