const MODAL_REQUEST_SUCCESS_ID = 'requestSuccess';
const MODAL_REQUEST_ERROR_ID = 'requestError';
const MODAL_CLOSE_CLASS = 'modal-close';

const handleModalClick = ({ currentTarget, target }, videoContainer) => {
  const isClickedOnBackdrop = target === currentTarget;

  if (isClickedOnBackdrop || target?.className === MODAL_CLOSE_CLASS) {
    if (videoContainer) {
      videoContainer.currentTime = 0;
      videoContainer.pause();
    }

    currentTarget.close();
  }
};

function openModal(id, videoId) {
  if (!id) return;
  const modal = document.getElementById(id);
  if (!modal) return;

  const videoContainer = videoId ? document.getElementById(videoId) : null;

  modal.showModal();

  setTimeout(() => {
    modal.addEventListener('click', (event) => handleModalClick(event, videoContainer));
  }, 0);
}
