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
      resetLens();
    }
  });
  $("input#_frame").trigger("change");
});
