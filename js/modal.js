const MODAL_REQUEST_SUCCESS_ID = 'requestSuccess';
const MODAL_REQUEST_ERROR_ID = 'requestError';
const MODAL_CLOSE_CLASS = 'modal-close';

const handleModalClick = ({ currentTarget, target }, videoFrame) => {
  const isClickedOnBackdrop = target === currentTarget;

  if (isClickedOnBackdrop || target?.className === MODAL_CLOSE_CLASS) {
    if (videoFrame) {
      videoFrame.innerHTML = '';
      videoFrame.remove?.();
    }

    currentTarget.close();
  }
};

function openModal(id, videoMeta) {
  if (!id) return;
  const modal = document.getElementById(id);
  if (!modal) return;

  let videoFrame;

  if (videoMeta) {
    const { videoContainerId, videoSrc } = videoMeta;
    const videoContainer = document.getElementById(videoContainerId);
    if (videoContainer && videoSrc) {
      videoFrame = renderVideoPlayer(videoContainer, videoSrc);
    }
  }

  modal.showModal();

  setTimeout(() => {
    modal.addEventListener('click', (event) => handleModalClick(event, videoFrame));
  }, 0);
}
