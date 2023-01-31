(function($, Drupal) {
  Drupal.behaviors.dlaw_topics_icon = {
    attach: function(context, settings) {
      // Add behavior to category icon list buton.
      $(".topics-icon__options").on("click", function(e) {
        var url = $(this)
          .find("img")
          .attr("src");

        $("#edit-field-icon-url-wrapper input")
          .addClass("yellow-fade")
          .val(url)
          .delay(1000)
          .queue(function(next) {
            $(this).removeClass("yellow-fade");
            next();
          });
      });
    },
  };
})(jQuery, Drupal, once);
