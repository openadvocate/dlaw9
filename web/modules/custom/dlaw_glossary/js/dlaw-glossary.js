// "use strict";

(function ($, Drupal) {
  Drupal.behaviors.dlaw_glossary = {

    attach: function (context, settings) {
      // Run once on page load.
      if (context !== document) {
        return;
      }

      if (typeof OARC !== 'undefined') {
        let gl = drupalSettings.dlaw.glossary;

        OARC.init(true, gl.location, gl.theme, gl.footnotes, gl.glossary);
      }

    }
  }
}(jQuery, Drupal));
