(function($, Drupal, once) {
  Drupal.behaviors.dlaw_writeclearly = {
    attach: function(context, settings) {
      once('addWcrcTab', 'ul.primary.nav-tabs', context).forEach((tab) => {
        tab.innerHTML += `<li class="nav-item"><a href="javascript: dlaw_run_writeclearly()" class="nav-link">WriteClearly</a></li>`
      })

      window.dlaw_run_writeclearly = function () {
        let jsCode = document.createElement('script');

        jsCode.setAttribute('src', '//writeclearly.openadvocate.org/bookmarklet.js');

        document.body.appendChild(jsCode);
      };
    },
  };
})(jQuery, Drupal, once);
