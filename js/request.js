const REQUEST_VIEW_IDS = {
  FORM: 'REQUEST_FORM',
  NAME: 'REQUEST_FORM_NAME',
  PHONE: 'REQUEST_FORM_PHONE',
  DATE: 'REQUEST_FORM_DATE',
  COMMENTS: 'REQUEST_FORM_COMMENTS',
};
const CONTROL_ERROR_CLASS = 'error';
const REQUEST_FORM = document.getElementById('REQUEST_FORM');
const PHONE_CONTROL = document.getElementById(REQUEST_VIEW_IDS.PHONE);
const POLICY_AGREEPMENT_CHECKBOX = document.getElementById('POLICY_AGREEPMENT');
const EMAIL_AGREEPMENT_CHECKBOX = document.getElementById('EMAIL_AGREEPMENT');
const MIN_PHONE_LENGTH = 8;
const REQUEST_BTNS = document.querySelectorAll('.btn-request-submit');
const DATE_CONTROL = document.getElementById(REQUEST_VIEW_IDS.DATE);

function initRequestForm() {
  REQUEST_FORM.addEventListener('submit', handleRequest);

  PHONE_CONTROL.addEventListener('input', (e) => {
    const el = e?.target;
    if ((el?.value?.length ?? 0) < MIN_PHONE_LENGTH) {
      return;
    }

    el?.parentElement.classList.remove(CONTROL_ERROR_CLASS);
  });

  POLICY_AGREEPMENT_CHECKBOX.addEventListener('change', function () {
    REQUEST_BTNS?.forEach((btn) => (btn.disabled = !this.checked));
  });

  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  const formattedDate = `${year}-${month}-${day}`;
  DATE_CONTROL.value = formattedDate;
}

async function handleRequest(e) {
  const form = REQUEST_FORM?.elements;
  e.preventDefault();

  if (!form || !POLICY_AGREEPMENT_CHECKBOX.checked) {
    return;
  }

  const phone = form[REQUEST_VIEW_IDS.PHONE]?.value;
  if ((phone?.length ?? 0) < MIN_PHONE_LENGTH) {
    form[REQUEST_VIEW_IDS.PHONE]?.parentElement?.classList?.add(CONTROL_ERROR_CLASS);
    return;
  }

  const request = buildPayload(form);

  if (!request) {
    return;
  }

  REQUEST_BTNS?.forEach((btn) => {
    btn.classList.add('btn--loading');
    btn.disabled = true;
  });

  try {
    const response = await fetch('send.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(request),
    });

    REQUEST_BTNS?.forEach((btn) => {
      btn.disabled = false;
      btn.classList.remove('btn--loading');
    });

    if (!response.ok) {
      openRequestErrorModal();
      throw new Error('Сетевая ошибка: ' + response.status);
    } else {
      openRequestSuccessModal();
    }
  } catch (error) {
    openRequestErrorModal();
    REQUEST_BTNS?.forEach((btn) => {
      btn.classList.remove('btn--loading');
      btn.disabled = undefined;
    });
  }
}

function buildPayload(form) {
  const name = form[REQUEST_VIEW_IDS.NAME]?.value;
  const phone = form[REQUEST_VIEW_IDS.PHONE]?.value;
  const date = form[REQUEST_VIEW_IDS.DATE]?.value;
  const comments = form[REQUEST_VIEW_IDS.COMMENTS]?.value;
  const utmTags = parseUtmParameters(window.location.href);
  const enableMailing = EMAIL_AGREEPMENT_CHECKBOX.checked;

  return {
    name,
    phone,
    date,
    comments,
    ...buildCalculatorRequestData(),
    utmTags,
    enableMailing,
  };
}

function openRequestSuccessModal() {
  openModal(MODAL_REQUEST_SUCCESS_ID);
}

function openRequestErrorModal() {
  openModal(MODAL_REQUEST_ERROR_ID);
}
