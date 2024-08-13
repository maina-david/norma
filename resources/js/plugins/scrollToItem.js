window.scrollToItem = (item, scrollContainerQuery, behavior) => {
  if (!item) {
    return;
  }
  const topPos = item.getBoundingClientRect().top;
  const container = scrollContainerQuery ? item.closest(scrollContainerQuery) : window;
  container.scrollBy({ top: topPos - 400, left: 0, behavior:  behavior || 'smooth' });
}

window.scrollToAttempts = 0;
window.scrollToItemWhenAvailable = (selector, scrollContainerQuery, behavior) => {
  if (window.scrollToAttempts >= 50) {
      window.scrollToAttempts = 0;
      return;
    }
  const item = document.querySelector(selector);
  if (item) {
    window.scrollToAttempts = 0;
    // item.scrollIntoView({behavior});
    window.scrollToItem(item, scrollContainerQuery, behavior);
  } else {
    window.setTimeout(function() {
      window.scrollToAttempts++;
      window.scrollToItemWhenAvailable(selector, scrollContainerQuery, behavior);
    }, 300);
  }
}
