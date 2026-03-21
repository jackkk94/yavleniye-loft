const REQUEST_VIEW_IDS = {
  FORM: 'REQUEST_FORM',
  NAME: 'REQUEST_FORM_NAME',
  PHONE: 'REQUEST_FORM_PHONE',
  DATE: 'REQUEST_FORM_DATE',
  COMMENTS: 'REQUEST_FORM_COMMENTS',
};
const CONTROL_ERROR_CLASS = 'error';
const REQUEST_FORM = document.getElementById('REQUEST_FORM');
const DATE_CONTROL = document.getElementById(REQUEST_VIEW_IDS.DATE);

$(document).on('ready', function () {
  REQUEST_FORM.addEventListener('submit', handleRequest);

  DATE_CONTROL.addEventListener('change', (e) => {
    const el = e?.target;
    let date;
    if (!el?.value?.length) {
      el?.parentElement.classList.add(CONTROL_ERROR_CLASS);
      return;
    }

    try {
      date = new Date(el?.value);
    } catch (e) {
      el?.parentElement.classList.add(CONTROL_ERROR_CLASS);
      return;
    }

    if (date) {
      el?.parentElement.classList.remove(CONTROL_ERROR_CLASS);
    }
  });

  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  const formattedDate = `${year}-${month}-${day}`;
  DATE_CONTROL.value = formattedDate;
});

async function handleRequest(e) {
  const form = REQUEST_FORM?.elements;
  e.preventDefault();

  if (!form) {
    return;
  }

  const date = form[REQUEST_VIEW_IDS.DATE]?.value;
  if (!date?.length) {
    form[REQUEST_VIEW_IDS.DATE]?.parentElement?.classList?.add(CONTROL_ERROR_CLASS);
    return;
  }

  const request = buildPayload(form);

  if (!request) {
    return;
  }

  try {
    const response = await fetch('send.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(request),
    });

    if (!response.ok) {
      openRequestErrorModal();
      throw new Error('Сетевая ошибка: ' + response.status);
    } else {
      openRequestSuccessModal();
    }
  } catch (error) {
    console.error('Ошибка:', error);
  }
}

function buildPayload(form) {
  const name = form[REQUEST_VIEW_IDS.NAME]?.value;
  const phone = form[REQUEST_VIEW_IDS.PHONE]?.value;
  const date = form[REQUEST_VIEW_IDS.DATE]?.value;
  const comments = form[REQUEST_VIEW_IDS.COMMENTS]?.value;
  const utmTags = parseUtmParameters(window.location.href);

  return {
    name,
    phone,
    date,
    comments,
    ...buildCalculatorRequestData(),
    utmTags,
  };
}

function openRequestSuccessModal() {
  openModal(MODAL_REQUEST_SUCCESS_ID);
}

function openRequestErrorModal() {
  openModal(MODAL_REQUEST_ERROR_ID);
}
