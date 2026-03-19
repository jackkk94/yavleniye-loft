const REQUEST_VIEW_IDS = {
  FORM: 'REQUEST_FORM',
  NAME: 'REQUEST_FORM_NAME',
  PHONE: 'REQUEST_FORM_PHONE',
  DATE: 'REQUEST_FORM_DATE',
  COMMENTS: 'REQUEST_FORM_COMMENTS',
};

async function handleRequest(e) {
  e.preventDefault();
  const form = document.getElementById('REQUEST_FORM')?.elements;
  if (!form) {
    return;
  }

  const request = buildPayload(form);

  console.log(request);

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
      throw new Error('Сетевая ошибка: ' + response.status);
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
  const tags = parseUtmParameters(window.location.href);
  
  return {
    name,
    phone,
    date,
    comments,
    ...buildCalculatorRequestData(),
    tags,
  };
}
