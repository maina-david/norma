<style>
  .zopim:not(.loaded) {
    display: none !important;
  }
</style>
<script data-turbo-cache="false" type="text/javascript">
  var Chat = {
    started: false,
    timeout: null,
    init: function () {
      if (this.started) {
        this.addClass();
        return;
      }

      this.started = true;

      if (window.$zopim) {
        this.loadScripts();
        this.addClass();
        return;
      }

      this.prepare();

      var that = this;

      window.$zopim(function() {
        window.$zopim.livechat.hideAll();
        window.$zopim.livechat.setOnConnected(() => {
          setTimeout(() => {
            if (window.$zopim.livechat.getEmail().trim().length > 2) {
              window.$zopim.livechat.window.show();
              window.$zopim.livechat.window.hide();
              that.addClass();
            }
          }, 500);
        });
      });

    },
    addClass: function () {
      if (this.timeout) {
        clearTimeout(this.timeout);
      }

      this.timeout = setTimeout(function () {
        window.$zopim.livechat.window.hide();
        document.querySelectorAll('.zopim').forEach(function (e) { e.classList.add('loaded'); });
      }, 3000);
    },
    loadScripts: function () {
      document.querySelectorAll('.zopim').forEach(function (e) { e.remove(); });
      document.querySelectorAll('.jx_ui_StyleSheet').forEach(function (e) { e.remove(); });

      var scripts = [
        'https://v2.zopim.com/?2c4IWwQuBUQ9zF0R4567i761ZH6qamMC',
        'https://v2.zopim.com/w?2c4IWwQuBUQ9zF0R4567i761ZH6qamMC',
      ];

      var docHead = document.querySelector('head');

      scripts.forEach(function (src) {
        document.querySelectorAll('script[src="' +  src + '"]').forEach(function (e) { e.remove(); });

        var node = document.createElement('script');
        node.type = 'text/javascript';
        node.src = src;
        node.setAttribute('charset', 'utf-8');
        node.setAttribute('data-turbo-cache', 'false');
        docHead.append(node);
      });
    },
    prepare: function () {
      (function(d, s) {
        var z = ($zopim = function(c) {
              z._.push(c);
            }),
            $ = (z.s = d.createElement(s)),
            e = d.getElementsByTagName(s)[0];
        z.set = function(o) {
          z.set._.push(o);
        };
        z._ = [];
        z.set._ = [];
        $.async = !0;
        $.setAttribute('charset', 'utf-8');
        $.setAttribute('data-turbo-cache', 'false');
        $.src = 'https://v2.zopim.com/?2c4IWwQuBUQ9zF0R4567i761ZH6qamMC';
        z.t = +new Date();
        $.type = 'text/javascript';
        e.parentNode.insertBefore($, e);
      })(document, 'script');
    },
  };

  document.addEventListener('turbo:load', () => {
    Chat.init();
  });
</script>
