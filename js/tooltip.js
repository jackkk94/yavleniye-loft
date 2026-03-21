const TOOLTIP_ICONS = document.querySelectorAll('.icon-tooltip');

const showEvents = ['mouseenter', 'focus'];
const hideEvents = ['mouseleave', 'blur'];

TOOLTIP_ICONS?.forEach((icon) => {
  const tooltipId = icon.getAttribute('aria-describedby');
  if (!tooltipId) {
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