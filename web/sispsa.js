function updatestatus(id, estatus) {

  var parametros = {
    id: id,
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