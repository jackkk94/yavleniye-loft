const CALCULATOR_VIEW_IDS = {
  RENT_TIME: 'RENT_TIME',
  TECH_TIME: 'TECH_TIME',
  TECHNICIAN_ON_DUTY: 'TECHNICIAN_ON_DUTY',
  LIGHT_OPERATOR: 'LIGHT_OPERATOR',
  SOUND_ENGINEER: 'SOUND_ENGINEER',
  TOTAL_PRICE: 'TOTAL_PRICE',
  CALCULATOR_FORM: 'CALCULATOR_FORM',
  WITH_EQUIPMENT: 'WITH_EQUIPMENT',
};

const CALCULATOR_RESULT_VIEW_META = {
  [CALCULATOR_VIEW_IDS.RENT_TIME]: {
    time: CALCULATOR_VIEW_IDS.RENT_TIME,
    price: 'RENT_TOTAL',
  },
  [CALCULATOR_VIEW_IDS.TECH_TIME]: {
    time: CALCULATOR_VIEW_IDS.TECH_TIME,
    price: 'TECH_TOTAL',
  },
  [CALCULATOR_VIEW_IDS.TECHNICIAN_ON_DUTY]: {
    price: 'TECHNICIAN_ON_DUTY_TOTAL',
  },
  [CALCULATOR_VIEW_IDS.LIGHT_OPERATOR]: {
    price: 'LIGHT_OPERATOR_TOTAL',
  },
  [CALCULATOR_VIEW_IDS.SOUND_ENGINEER]: {
    price: 'SOUND_ENGINEER_TOTAL',
  },
  [CALCULATOR_VIEW_IDS.TOTAL_PRICE]: {
    price: CALCULATOR_VIEW_IDS.TOTAL_PRICE,
  },
};

const PRICE_META = {
  //Аренда без оборудования
  RENT_WITHOUT_EQUIPMENT: {
    PRICE: {
      DEFAULT: 6000,
      SPECIAL: 5500,
    },
    MIN_SPECIAL_HOURS: 6,
  },
  //Аренда с оборудованием
  RENT_WITH_EQUIPMENT: {
    PRICE: {
      DEFAULT: 8000,
      SPECIAL: 7500,
    },
    MIN_SPECIAL_HOURS: 6,
  },
  //Техническое время
  TECH_TIME: {
    PRICE: {
      DEFAULT: 1500,
    },
  },
  //Дежурный
  [CALCULATOR_VIEW_IDS.TECHNICIAN_ON_DUTY]: {
    PRICE: {
      DEFAULT: 4000,
      SPECIAL: 900,
    },
    MIN_SPECIAL_HOURS: 9,
  },
  //Звукарь
  [CALCULATOR_VIEW_IDS.SOUND_ENGINEER]: {
    PRICE: {
      DEFAULT: 5000,
      SPECIAL: 900,
    },
    MIN_SPECIAL_HOURS: 9,
  },

  //световик
  [CALCULATOR_VIEW_IDS.LIGHT_OPERATOR]: {
    PRICE: {
      DEFAULT: 5000,
      SPECIAL: 900,
    },
    MIN_SPECIAL_HOURS: 9,
  },
};

const INACTIVE_RESULT_ITEM_VIEW_CLASS = 'result-item--inactive';

const getElById = (id) => document.getElementById(id);

const FORM = getElById(CALCULATOR_VIEW_IDS.CALCULATOR_FORM);
const tariffControl = getElById(CALCULATOR_VIEW_IDS.WITH_EQUIPMENT);
let initialFormValue = {};

FORM?.addEventListener('input', handleCalculatorFormChange);

document.addEventListener('DOMContentLoaded', function () {
  handleCalculatorFormChange();
});

function handleCalculatorFormChange() {
  const formData = new FormData(FORM);

  let values = {};
  formData.forEach((value, key) => {
    values[key] = value;
  });

  const { Tariff, TechTime, Time } = values;

  const isTariffWithEquipment = Tariff === 'withEquipment';

  const RentTimeNumber = Number(Time ?? 0);

  const TechTimeNumber = Number(TechTime ?? 0);

  [
    CALCULATOR_VIEW_IDS.LIGHT_OPERATOR,
    CALCULATOR_VIEW_IDS.SOUND_ENGINEER,
    CALCULATOR_VIEW_IDS.TECHNICIAN_ON_DUTY,
  ].forEach((id) => {
    const checkbox = getElById(id);
    checkbox.disabled = !isTariffWithEquipment;
    if (!isTariffWithEquipment) {
      checkbox.checked = false;
    }
  });

  if (!isTariffWithEquipment) {
    values = {
      ...values,
      Technician: undefined,
      SoundEngineer: undefined,
      LightOperator: undefined,
    };
  }

  const technicCheckbox = getElById(CALCULATOR_VIEW_IDS.TECHNICIAN_ON_DUTY);
  const soundEngineerCheckbox = getElById(CALCULATOR_VIEW_IDS.SOUND_ENGINEER);

  if (isTariffWithEquipment && !values.Technician && initialFormValue.Technician && !values.SoundEngineer) {// дежурный техник по-умолчанию включен, если не выбран звуковик
    technicCheckbox.checked = true;
    values.Technician = 'on';
  }


  if (initialFormValue.Tariff !== values.Tariff && isTariffWithEquipment) { 
    technicCheckbox.checked = true;
    values.Technician = 'on';
  }

  if (values.SoundEngineer && !initialFormValue.SoundEngineer) {
    values.Technician = undefined;
    technicCheckbox.checked = false;
  } else if (values.Technician && !initialFormValue.Technician) {
    values.SoundEngineer = undefined;
    soundEngineerCheckbox.checked = false;
  }


  if (isTariffWithEquipment && !values.SoundEngineer && initialFormValue.SoundEngineer && !values.Technician) { //Если отключить звуковика, должен включиться дежурный техник
    technicCheckbox.checked = true;
    values.Technician = 'on';
  }

  const technicsPrice = {};
  [
    CALCULATOR_VIEW_IDS.LIGHT_OPERATOR,
    CALCULATOR_VIEW_IDS.SOUND_ENGINEER,
    CALCULATOR_VIEW_IDS.TECHNICIAN_ON_DUTY,
  ].forEach((id) => {
    const checkbox = getElById(id);
    if (checkbox.checked) {
      const priceMeta = PRICE_META[id];
      const maxDefaultPriceHours = priceMeta.MIN_SPECIAL_HOURS - 1;
      const overTime = RentTimeNumber - maxDefaultPriceHours;
      const totalPrice = priceMeta.PRICE.DEFAULT + (overTime > 0 ? overTime : 0) * (priceMeta.PRICE.SPECIAL ?? 0);

      technicsPrice[id] = totalPrice;
    }
  });

  const hoursPriceMeta = PRICE_META[isTariffWithEquipment ? 'RENT_WITH_EQUIPMENT' : 'RENT_WITHOUT_EQUIPMENT'];
  const rentTimePrice =
    hoursPriceMeta.PRICE[RentTimeNumber >= hoursPriceMeta.MIN_SPECIAL_HOURS ? 'SPECIAL' : 'DEFAULT'] * RentTimeNumber;
  const techTimePrice = PRICE_META.TECH_TIME.PRICE.DEFAULT * TechTimeNumber;

  const totalPrice =
    rentTimePrice +
    techTimePrice +
    Object.keys(technicsPrice).reduce((acc, key) => (acc += technicsPrice[key] ?? 0), 0);

  renderCalculatorResult({
    [CALCULATOR_VIEW_IDS.RENT_TIME]: {
      time: RentTimeNumber,
      price: rentTimePrice,
    },
    [CALCULATOR_VIEW_IDS.TECH_TIME]: {
      price: techTimePrice,
      time: TechTimeNumber,
    },
    [CALCULATOR_VIEW_IDS.LIGHT_OPERATOR]: {
      price: technicsPrice[CALCULATOR_VIEW_IDS.LIGHT_OPERATOR],
    },
    [CALCULATOR_VIEW_IDS.SOUND_ENGINEER]: {
      price: technicsPrice[CALCULATOR_VIEW_IDS.SOUND_ENGINEER],
    },
    [CALCULATOR_VIEW_IDS.TECHNICIAN_ON_DUTY]: {
      price: technicsPrice[CALCULATOR_VIEW_IDS.TECHNICIAN_ON_DUTY],
    },
    [CALCULATOR_VIEW_IDS.TOTAL_PRICE]: {
      price: totalPrice,
    },
  });

  initialFormValue = { ...values };
}

function renderCalculatorResult(result) {
  Object.keys(result).forEach((key) => {
    const meta = result[key];
    if (meta.time !== undefined && meta.time !== null) {
      renderValueById(key, meta.time);
    }

    renderValueById(key, meta.price, true);
  });
}

function renderValueById(key, value, isMoney = false) {
  const meta = CALCULATOR_RESULT_VIEW_META[key];
  const id = isMoney ? meta.price : meta.time;
  const el = document.getElementById(id);
  const wrapperEl = el.closest('.result-item');

  el.innerText = `${value ?? 0}${isMoney ? ' ₽' : ''}`;
  if (!wrapperEl) {
    return;
  }

  if (value > 0) {
    wrapperEl.classList.remove(INACTIVE_RESULT_ITEM_VIEW_CLASS);
  } else {
    wrapperEl.classList.add(INACTIVE_RESULT_ITEM_VIEW_CLASS);
  }
}
