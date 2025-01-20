# Пример минимальной разметки Product


## Товар с ценой и отзывами

	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "Product",
		"name": "Наименование товара",
		"description": "Описание",
		"image": "https://site.ru/upload/image.jpg",
		"offers": {
			"@type": "Offer",
			"availability": "https://schema.org/InStock",
			"price": "55.00",
			"priceCurrency": "RUB"
		},

		/* Итоговый рейтинг товара  и отзывы */
		"aggregateRating": {
			"@type": "AggregateRating",
			"ratingValue": "3.5",
			"reviewCount": "11"
		},
		"review": [
			{
				"@type": "Review",
				"author": "Имя автора",
				"datePublished": "2025-04-01",
				"reviewBody": "Текст отзыва",
				"name": "Заголовок отзыва",
				"reviewRating": {
					"@type": "Rating",
					"bestRating": "5",
					"ratingValue": "5",
					"worstRating": "1"
				}
			},
			{
				"@type": "Review",
				"author": "Коля",
				"datePublished": "2024-03-25",
				"reviewBody": "Средненько, хотелось бы лучше. ",
				"name": "Так себе",
				"reviewRating": {
					"@type": "Rating",
					"bestRating": "5",
					"ratingValue": "3",
					"worstRating": "1"
				}
			}
		]
	}
	</script>

## Товар с торговыми предложениями и разной ценой

	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "Product",
		"name": "Гаименование товара",
		"image": "https://site.ru/upload/image.jpg",

		"aggregateRating": {
			"@type": "AggregateRating",
			"bestRating": "100",
			"ratingCount": "24",
			"ratingValue": "87"
		},

		"offers": {
			"@type": "AggregateOffer",
			"priceCurrency": "RUB",
			"highPrice": "9900",
			"lowPrice": "1100",
			"offerCount": "2",
			"offers": [
				{
					"@type": "Offer",
					"url": "https://site.ru/product/item/"
				},
				{
					"@type": "Offer",
					"url": "https://site.ru/product/item/?oid=456"
				}
			]
		}
	}
	</script>

## Список товаров

	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "ItemList",
		"url": "http://site.ru/catalog/section/",
		"numberOfItems": "2",
		"itemListElement": [
			{
				"@type": "Product",
				"name": "Товар 1",
				"image": "https://site.ru/upload/image.jpg",
				"url": "https://site.ru/product/tovar_1/",

				"offers": {
					"@type": "Offer",
					"priceCurrency": "RUB",
					"price": "4399.00"
				}
			},
			{
				"@type": "Product",
				"name": "Товар 2",
				"image": "https://site.ru/upload/image2.jpg",
				"url": "https://site.ru/product/tovar_2/",

				"offers": {
					"@type": "Offer",
					"priceCurrency": "RUB",
					"price": "1000.00"
				}
			}
		]
	}
	</script>

## Каталог товаров

	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "OfferCatalog",
		"url": "http://site.ru/catalog/section/",
		"name": "Название каталога",
		"image": "http://sitete.ru/image.jpg",
		"description": "Описание каталога",
		"numberOfItems": "2",
		"itemListElement": [
			{
				"@type": "Offer",
				"name": "Товар 1",
				"description": "Описание товара",
				"image": "https://site.ru/upload/image.jpg",
				"url": "https://site.ru/product/tovar_1/",
				"availability": "https://schema.org/InStock"
				"priceCurrency": "RUB",
				"price": "4399.00"
			},
			{
				"@type": "Offer",
				"name": "Товар 2",
				"description": "Описание товара 2",
				"image": "https://site.ru/upload/image2.jpg",
				"url": "https://site.ru/product/tovar_2/",
				"availability": "https://schema.org/InStock"
				"priceCurrency": "RUB",
				"price": "1000.00"
			}
		]
	}
	</script>