$(document).on('ready', function () {
  const ranges = document.querySelectorAll('.range-input');
  ranges?.forEach((range) => {
    range.addEventListener('input', handleInputRange);
  });
});

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
