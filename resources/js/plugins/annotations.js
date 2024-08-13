function isInViewport (elem) {
  const bounding = elem.getBoundingClientRect();
  return (
      bounding.top >= 0 &&
      bounding.left >= 0 &&
      bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
      bounding.right <= (window.innerWidth || document.documentElement.clientWidth)
  );
}

window.initAnnotations = () => {
  // only load on annotations page.
  if (!document.querySelector('#annotations')) {
    return;
  }

  setTimeout(function () {
    const active = document.querySelector('#annotation-references .active');
    if (active && !isInViewport(active)) {
      active.scrollIntoView();
    }
  }, 100);
}
