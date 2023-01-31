(function ($, Drupal) {
  Drupal.behaviors.dlaw_node = {
    attach: function (context, settings) {

      // Char length warning on title.
      $('.node-form #edit-title-0-value')
        .after('<p id="title-length-check"></p>').keyup(function() {
        let length = $(this).val().length;
        let msg, color;
        if (length < 50) {
          msg = length + ' chars. Increase title to optimize for search engine.';
          color = '#fc0';
        }
        else if (length <= 60) {
          msg = length + ' chars (optimal range 50-60 chars).';
          color = '#eee';
        }
        else {
          msg = length + ' chars. Shorten title to optimize for search engines.';
          color = '#f93';
        }
        $(this).next('#title-length-check').css('background-color', color).html(msg);
      });

      // Char length warning on body summary.
      $('.node-form .text-summary-wrapper label[for=edit-body-0-summary]')
        .after('<p id="summary-length-check"></p>');

      $('.node-form .text-summary-wrapper #edit-body-0-summary').keyup(function() {
        let length = $(this).val().length;
        let msg, color;
        if (length < 70) {
          msg = length + ' chars. Increase summary to optimize for search engine.';
          color = '#fc0';
        }
        else if (length <= 160) {
          msg = length + ' chars (optimal range 70-160 chars).';
          color = '#eee';
        }
        else {
          msg = length + ' chars. Shorten summary to optimize for search engines.';
          color = '#f93';
        }
        $('#summary-length-check').css('background-color', color).html(msg);
      });


    }
  };
})(jQuery, Drupal);
