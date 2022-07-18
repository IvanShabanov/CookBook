var data = new FormData();
/* Собираем все поля */
data.append('name', $('input[name=name]').val());
data.append('phone', $('input[name=phone]').val());
/* Добавляем файл */
data.append('file', $('input[type=file]')[0].files[0]);
/* Отправляем */
$.ajax({
    type: "POST",
    processData: false,
    contentType: false,
    url: "/targeturl/",
    data: data,
    success: function (data)
    {
    }
});