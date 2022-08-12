jQuery(function ($) {
  function resetLens() {
    $(".show_if_lens").hide();
    $(".hide_if_lens").hide();
  }

  function resetFrame() {
    $(".show_if_frame").hide();
    $(".hide_if_frame").hide();
  }

  $("input#_lens").change(function () {
    var is_lens = $("input#_lens:checked").size();
    resetLens();

    if (is_lens) {
      $(".hide_if_lens").hide();
      $(".show_if_lens").show();
      $("input#_frame").prop("checked", false);
      $("#frame_options").hide();
      resetFrame();
    }
  });
  $("input#_frame").change(function () {
    var is_frame = $("input#_frame:checked").size();
    resetFrame();

    if (is_frame) {
      $(".hide_if_frame").hide();
      $(".show_if_frame").show();
      $("input#_lens").prop("checked", false);
      $("#lens_options").hide();
      resetLens();
    }
  });
  $("input#_lens").trigger("change");
  $("input#_frame").trigger("change");

  $(document).on("woocommerce_variations_loaded", function (event) {
    $("input#_lens").trigger("change");
    $("input#_frame").trigger("change");
  });

  var frame;
  $(".upload-custom-img").on("click", function (event) {
    event.preventDefault();
    var buttonAdd = $(this);

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: "Selecione a foto da marca",
      button: {
        text: "Usar essa foto",
      },
      multiple: false,
    });

    // When an image is selected in the media frame...
    frame.on("select", function () {
      // Get media attachment details from the frame state
      var attachment = frame.state().get("selection").first().toJSON();

      // Send the attachment URL to our custom image input field.
      buttonAdd
        .parent()
        .find(".custom-img-container")
        .append(
          '<img src="' +
            attachment.url +
            '" alt="" style="max-width:150px; max-height:150px"/>'
        );

      // Send the attachment id to our hidden input
      buttonAdd.parent().find(".custom-img-id").val(attachment.id);

      // Hide the add image link
      buttonAdd.addClass("hidden");

      // Unhide the remove image link
      buttonAdd.parent().find(".delete-custom-img").removeClass("hidden");
    });

    // Finally, open the modal on click
    frame.open();
  });

  // DELETE IMAGE LINK
  $(".delete-custom-img").on("click", function (event) {
    event.preventDefault();

    // Clear out the preview image
    $(this).parent().find(".custom-img-container").html("");

    // Un-hide the add image link
    $(this).parent().find(".custom-img-id").removeClass("hidden");

    // Hide the delete image link
    $(this).addClass("hidden");

    // Delete the image id from the hidden input
    $(this).parent().find(".custom-img-id").val("");
  });
});
