/* Плавный переход по ссылкам */
$("a[href^='#']").click(function (event) {
    /* забираем идентификатор бока с атрибута href */
    var id = $(this).attr('href');
    var ankor = id.replace('#', 'a[name=');
    ankor = ankor + ']';
    id = id + ', ' + ankor;
    var el = $(id);
    /* Если елеметы есть, то */
    if ($(el).length > 0) {
        /* отменяем стандартную обработку нажатия по ссылке */
        event.preventDefault();
        /* узнаем высоту от начала страницы до блока на который ссылается якорь */
        var top = $(id).first().offset().top - 30;
        /* анимируем переход на расстояние - top  */
        $('body,html').stop();
        $('body,html').animate({ scrollTop: top }, 500);
    }
});

/****************************************************/
/* Если ссылка на элемент со внешней страницы */
if (window.location.href.indexOf('#') > 0) {
    var id = window.location.href;
    id = id.substr(id.indexOf('#'));
    var ankor = id.replace('#', 'a[name=');
    ankor = ankor + ']';
    id = id + ', ' + ankor;
    var el = $(id);
    if ($(el).length > 0) {
        setTimeout(function () {
            var top = $(id).first().offset().top - 30;
            /* анимируем переход на расстояние - top */
            $('body,html').stop();
            $('body,html').animate({ scrollTop: top }, 500);
        }, 500);
    }
}