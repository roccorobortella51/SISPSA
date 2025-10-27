function updatestatus(id, estatus) {

  let csrfToken = $('#csrf-token').val();
  var parametros = {
    id: id,
    "_csrf" : csrfToken,
  };

  $.ajax({
    url: "updatestatus",
    type: "post",
    dataType: "json",
    data: parametros,
    success: function (data) {
      console.log(data);
    },
  });
}


