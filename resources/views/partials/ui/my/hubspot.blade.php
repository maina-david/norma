<script data-turbo-cache="false" type="text/javascript">
  function setupHubspot() {
    var countHubspotRetries = 0;

    function initHubspot() {
      if (countHubspotRetries >= 3) {
        return;
      }

      // eslint-disable-next-line no-underscore-dangle
      if (window._hsq === undefined || window._hsq.push === undefined) {
        countHubspotRetries += 1;
        window.setTimeout(function () {
          initHubspot();
        }, 500);
        return;
      }

      setTimeout(function () {
        window._hsq.push(['refreshPageHandlers']);
        window._hsq.push(['identify', { email: '{{ auth()->user()->email }}' }]);
        window._hsq.push(['setPath', window.location.pathname]);
        window._hsq.push(['trackPageView']);
      }, 1000);
    }

    initHubspot();
  }

  document.addEventListener('turbo:load', () => {
    setupHubspot();
  });
</script>
