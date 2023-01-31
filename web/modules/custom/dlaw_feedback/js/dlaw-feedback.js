(function($, Drupal, once) {
  Drupal.behaviors.dlaw_feedback = {
    attach: function(context, settings) {
      // Form visibility when "how helpful" was clicked.

      $(once("feedback-vote", ".vote-form input[type=radio]", context)).on(
        "click",
        function() {
          let how_helpful = $(this).val();

          $(document).ajaxComplete(function() {
            // 3 == Very helpful
            if (how_helpful == 3) {
              $(".vote-form")
                .slideUp()
                .after("<p>Thanks for your feedback!</p>");
            } else {
              $(".field--name-field-feedback").slideDown();
            }
          });
        }
      );

      // Reveal comment form.
      $(
        once(
          "feedback-voted-unhelpful",
          "#edit-field-why-unhelpful input[type=radio]",
          context
        )
      ).on("click", function() {
        $("#comment-form .text-format-wrapper").slideDown();
        $("#comment-form .button--primary").slideDown();
      });

      // Enforce maxlength (1,000 chars) to the comment body.
      if ($("#comment-length-check").length === 0) {
        $(
          once(
            "feedback-add-comment-form",
            "#comment-form .form-textarea-wrapper",
            context
          )
        ).append(
          '<div id="comment-length-check" class="comment-length-check"></div>'
        );
      }

      $("#comment-form textarea").keyup(function() {
        let length = $(this).val().length;
        let title_msg, title_color;
        if (length < 1000) {
          title_msg = length + " chars. Max 1,000 chars allowed.";
          title_color = "#fff";
        } else {
          title_msg = "Max 1,000 chars reached.";
          title_color = "#fc0";

          this.value = this.value.substring(0, 1000);
        }
        $("#comment-length-check")
          .css("background-color", title_color)
          .html(title_msg);
      });

      // Disable submit after first click to prevent multiple submission.
      $(
        once(
          "feedback-disable-double-sbm",
          "#comment-form .btn-primary",
          context
        )
      ).on("click", function() {
        $(this)
          .text("Processing ...")
          .prop("disabled", true);
        $("#comment-form").submit();
      });
    },
  };
})(jQuery, Drupal, once);
