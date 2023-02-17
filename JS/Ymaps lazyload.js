/*
<div id="map"></div>
*/

if (typeof lozad != 'undefined') {
	/* Если на сайте используется Lozad */
	const observer = lozad("#map", {
		loaded: function(el) {
			LazyMapShow ();
		}
	});
	observer.observe();
} else {
	LazyMapShow ();
}

function LazyMap() {
	const script = document.createElement('script');
	script.onload = function () {
		ymaps.ready(initMap);
	};
	script.src = "https://api-maps.yandex.ru/2.1/?apikey={Your APIKEY}&lang=ru_RU";

	document.head.appendChild(script);
}

function initMap() {

	let myMap = new ymaps.Map("map", {
		center: [56.316667, 44.0], /* Центр карты */
		zoom: 16
	}, {
		maxZoom: 17
	});

 	/* Маркеры на карте */
	let markers =
		[
			{
				lat: 56.316667,
				lon: 44.0,
				text: 'Первый маркер',
				params: {
					preset: 'islands#icon',
					iconColor: '#0095b6'
				}
			},
			{
				lat: 56.316667,
				lon: 44.0,
				text: 'Второй маркер',
				params: {
					preset: 'islands#icon',
					iconColor: '#0095b6'
				}
			}
		];

	/* Расставим маркеры на карте */
	var myGeoObjects = [];
	for (i = 0; i < markers.length; i++) {
		let myPlacemark = new ymaps.Placemark([markers[i].lat, markers[i].lon], {
			balloonContent: markers[i].text
		}, markers[i].params
		);
		myGeoObjects.push(myPlacemark);
	};
	clusterer = new ymaps.Clusterer();
	clusterer.add(myGeoObjects);
	myMap.geoObjects.add(clusterer);

	/* Далее Сделаем выравнивание и зуммирование карты для того чтобы показать все маркеры */
	setTimeout(function() {
		myMap.setBounds(myMap.geoObjects.getBounds(),{checkZoomRange:true, zoomMargin:9});
	}, 1000);

}

