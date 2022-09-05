function FlyToBasket(item, flyto) {
	if ((typeof item != 'undefined') && (typeof flyto != 'undefined')) {
		$(item).clone().appendTo("body").css({
			position: "absolute",
			left: $(item).offset().left,
			top: $(item).offset().top,
			width: $(item).width(),
			height: $(item).height(),
			'z-index': 10000
		}).animate({
			opacity: 0.5,
			left: $(flyto).offset().left,
			top: $(flyto).offset().top,
			width: 32,
			height: 32
		}, 1000, function () {
			$(this).remove();
		});
	};
}