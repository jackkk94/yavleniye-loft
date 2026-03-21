const MODAL_REQUEST_SUCCESS_ID = 'requestSuccess';
const MODAL_REQUEST_ERROR_ID = 'requestError';
const MODAL_CLOSE_CLASS = 'modal-close';

const handleModalClick = ({ currentTarget, target }) => {
  const isClickedOnBackdrop = target === currentTarget;

  if (isClickedOnBackdrop || target?.className === MODAL_CLOSE_CLASS) {
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
