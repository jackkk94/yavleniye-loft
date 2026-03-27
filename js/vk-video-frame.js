function createVkIframe(src, autoplay = false) {
  const autoplayParam = autoplay ? '1' : '0';
  const iframe = document.createElement('iframe');
  iframe.setAttribute('src', `${src}&autoplay=${autoplayParam}`);
  iframe.setAttribute('allow', 'autoplay; encrypted-media; fullscreen; picture-in-picture; screen-wake-lock;');
  iframe.setAttribute('allowfullscreen', 'true');
  iframe.style.width = '100%';
  iframe.style.height = '100%';
  iframe.style.backgroundColor = '#000';
  iframe.frameBorder = '0';
  return iframe;
}

function renderVideoPlayer(container, videoSrc) {
  if (!container || !videoSrc) {
    return;
  }

  container.innerHTML = '';
  const iframe = createVkIframe(videoSrc);
  container.appendChild(iframe);

  return iframe;
}