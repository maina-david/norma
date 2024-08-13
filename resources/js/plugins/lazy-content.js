window.initLazyContent = () => {
  document.querySelectorAll('[data-lazy-source]').forEach((el) => {
    fetch(el.getAttribute('data-lazy-source'))
      .then((response) => response.text())
      .then((response) => {
        el.innerHTML = response.split(/\n/).join('<br>');
      });
  });
};
