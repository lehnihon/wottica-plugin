jQuery(document).ready(function () {
  jQuery(".uploader-send").click(function (event) {
    event.preventDefault();

    if ("undefined" === typeof jQuery(".uploader-file")[0].files[0]) {
      alert("Select a file!");
      return;
    }
    // Grab the file from the input.
    var file = jQuery(".uploader-file")[0].files[0];
    var formData = new FormData();
    formData.append("file", file);
    formData.append("title", "titulo arquivo");
    formData.append("caption", "caption arquivo");

    jQuery(this).html("...enviando").prop("disabled", true);
    // Fire the request.
    jQuery
      .ajax({
        url: localizeObj.endpoint,
        method: "POST",
        processData: false,
        contentType: false,
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-WP-Nonce", localizeObj.nonce);
        },
        data: formData,
      })
      .success(function (response) {
        jQuery
          .ajax({
            url: localizeObj.api,
            type: "POST",
            dataType: "json",
            data: {
              action: "lki_update_user_photo",
              facial_photo_id: response?.id,
              facial_photo: response?.source_url,
            },
          })
          .success(function (response) {
            window.location.reload();
          })
          .error(function (response) {
            console.log("error");
            console.log(response);
          });
      })
      .error(function (response) {
        console.log("error");
        console.log(response);
      });
  });

  jQuery(".uploader-send-prescription").click(function (event) {
    event.preventDefault();

    if (
      "undefined" === typeof jQuery(".uploader-file-prescription")[0].files[0]
    ) {
      alert("Select a file!");
      return;
    }
    // Grab the file from the input.
    var file = jQuery(".uploader-file-prescription")[0].files[0];
    var formData = new FormData();
    formData.append("file", file);
    formData.append("title", "titulo arquivo");
    formData.append("caption", "caption arquivo");

    jQuery(this).html("...enviando").prop("disabled", true);
    // Fire the request.
    jQuery
      .ajax({
        url: localizeObj.endpoint,
        method: "POST",
        processData: false,
        contentType: false,
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-WP-Nonce", localizeObj.nonce);
        },
        data: formData,
      })
      .success(function (response) {
        jQuery
          .ajax({
            url: localizeObj.api,
            type: "POST",
            dataType: "json",
            data: {
              action: "lki_update_user_prescription",
              prescription_photo_id: response?.id,
              prescription_photo: response?.source_url,
            },
          })
          .success(function (response) {
            window.location.reload();
          })
          .error(function (response) {
            console.log("error");
            console.log(response);
          });
      })
      .error(function (response) {
        console.log("error");
        console.log(response);
      });
  });
});
