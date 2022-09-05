/* Fancybox 3 */
$('img').each(function () {
	if ($(this).closest('a').length == 0) {
		$(this).wrap($('<a/>', {
			href: $(this).attr('src'),
			class: "fancybox",
			'data-fancybox': 'gallery'
		}));
	}
})
$("a.fancybox").fancybox();



/* Когда есть маассив картинок */
/* Fancybox 3*/
var imgs = [];
var i = 0;
$('.images img').each(function () {
	var src = $(this).attr('src');
	if (imgs.join(',').indexOf(src) == -1) {
		imgs[i] = {
			'src': src,
			'type': 'image'
		};
		i++;
	}
})
$.fancybox.open(imgs);

/* Открываем ссылки на видео youtube */
/* Fancybox 3*/
$('.content a[href *= "youtube"]').each(function () {
	try {
		var src = $(this).attr('href');
		var regExp = /^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/;
		var match = src.match(regExp);
		var img = 'url(http://img.youtube.com/vi/' + match[1] + '/default.jpg)';
		$(this).css({
			'background-image': img,
			'background-size': 'cover'
		});
		$(this).addClass('youtube');
		$(this).fancybox();
	} catch (error) {
		console.log("Youtube fancybox Error: ");
		console.log(error);
	};
});




/*------------------------------*/
/* Fancybox 2*/
//Все изображения открываются
$("img").click(function () {
	var src = $(this).attr('src');
	$.fancybox({
		padding: 0,
		href: src
	});
});


/*Ссылки на изображения открывабтся так*/
$("a.galery").fancybox({
	padding: 0,
	helpers: {
		overlay: {
			locked: false
		}
	}
});



/* Когда есть маассив картинок */
/* Fancybox 2*/
var imgs = [];
var i = 0;
$('.images img').each(function () {
	var src = $(this).attr('src');
	if (imgs.join(',').indexOf(src) == -1) {

		imgs[i] = src;
		i++;
	}
})
/* Покажем массив картинок */
$.fancybox(imgs, {
	type: "image",
	padding: 0
});


/* Все картинки переделать в ссылки с классом fancybox */
$('img').each(function () {
	$(this).wrap($('<a/>', {
		href: $(this).attr('src'),
		class: "fancybox",
		rel: "artikel"
	}));
});




/* Открываем ссылки на видео youtube */
/* Fancybox 2*/

$('.content a[href *= "youtube"]').each(function () {
	try {
		var src = $(this).attr('href');
		var regExp = /^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/;
		var match = src.match(regExp);
		var img = 'url(http://img.youtube.com/vi/' + match[1] + '/default.jpg)';
		$(this).css({
			'background-image': img,
			'background-size': 'cover'
		});
		$(this).addClass('youtube');
		$(this).click(function () {
			var video_id = this.href.split('v=')[1];
			var ampersandPosition = video_id.indexOf('&');
			if (ampersandPosition != -1) {
				video_id = video_id.substring(0, ampersandPosition);
			}
			$.fancybox({
				'padding': 0,
				'autoScale': false,
				'transitionIn': 'none',
				'transitionOut': 'none',
				'width': 640,
				'height': 385,
				'href': 'https://www.youtube.com/embed/' + video_id + '?autoplay=1',
				'type': 'iframe',
				'scrolling': 'no',
				'iframe': {
					scrolling: 'no',
					preload: false
				},
				helpers: {
					overlay: {
						locked: false
					},
					title: {
						type: 'inside'
					}
				}
			});
			return false;
		});
	} catch (error) {
		console.log("Youtube fancybox Error: ");
		console.log(error);
	};
});