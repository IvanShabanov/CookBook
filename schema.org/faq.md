Пример минимальной разметки FAQ / Q&A

	<div itemscope itemtype="http://schema.org/FAQPage">
		<div itemprop="mainEntity" itemscope itemtype="http://schema.org/Question">
			<div itemprop="name">Это вопрос</div>
			<div itemscope itemprop="acceptedAnswer" itemtype="http://schema.org/Answer">
				<div itemprop="text">Здесь размещается ответ на указанный вопрос</div>
			</div>
		</div>

		<div itemprop="mainEntity" itemscope itemtype="http://schema.org/Question">
			<div itemprop="name">Это вопрос</div>
			<div itemscope itemprop="acceptedAnswer" itemtype="http://schema.org/Answer">
				<div itemprop="text">Здесь размещается ответ на указанный вопрос</div>
			</div>
		</div>
	</div>


Пример LD JSON

	<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "Вопрос?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Тут ответ"
          }
        },
        {
          "@type": "Question",
          "name": "Вопрос 2?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Полный ответ на вопрос 2"
          }
        }
      ]
    }
    </script>