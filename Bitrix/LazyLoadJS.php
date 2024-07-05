<?

namespace IsPro;

use Bitrix\Main\Context;

class LazyLoadJS
{
	public static function do(&$content)
	{
		$arParams = [
			'IGNORE_AJAX' => 'Y',
			'IGNORE_POST' => 'Y',
			'IGNORE_SCRIPT' => [
				// Правила для игнорирования скриптов
				'type' => [
					// Игнорировать скрипт если в type
					'json',
				],
				'data-not-lazy' => [
					'Y'
				],
				'src' => [
					// Игнорировать скрипт если в src
					'img2picture',
					'lozad'
				],
				'text' => [
					// Игнорировать скрипт если в коде скрипта встречается
				]
			],
			'IGNORE_URL' => [
				'/bitrix/',
				'/local/',
				'/personal/',
			],
			'JS_EVENTS' => [
				'click',
				'scroll',
				'keydown',
				'keyup',
				'touchstart',
				'touchmove',
				'touchend',
				'mousemove',
				'mouseenter',
				'mouseleave',
			],
		];

		if (!is_array($arParams['JS_EVENTS'])) {
			return;
		}

		$context = Context::getCurrent();
		$request = $context->getRequest();


		if (is_array($arParams['IGNORE_URL'])) {
			foreach ($arParams['IGNORE_URL'] as $url) {
				if (\CSite::InDir($url)) {
					return;
				}
			}
		}

		if ($arParams['IGNORE_POST'] == 'Y' && $request->isPost()) {
			return;
		}

		if ($arParams['IGNORE_AJAX'] == 'Y' && $request->isAjaxRequest()) {
			return;
		}

		$arScripts = self::get_tags('script', $content);

		if (!is_array($arScripts)) {
			return;
		}

		$arResult = self::PrepareResult($arScripts, $arParams);

		if (!is_array($arResult['remove'])) {
			return;
		}

		$resultJs = "<script>
			document.addEventListener('DOMContentLoaded', () => {
				window.lazyLoadJsWork = false;
				window.lazyLoadJsStartEvent = null;
				";
		$resultJs .= "const events = [";

		foreach ($arParams['JS_EVENTS'] as $key => $event) {
			if ($key > 0) {
				$resultJs .= ", ";
			}
			$resultJs .= "'{$event}'";
		}

		$resultJs .= "];";


		$resultJs .= "
			window.lazyLoadJsSrc = [
		";

		$preload = '';

		if (is_array($arResult['src'])) {
			foreach ($arResult['src'] as $key => $src) {
				if ($key > 0) {
					$resultJs .= ", ";
				}
				$resultJs .= "'{$src}'";
				$preload .= "<link rel='preload' href='{$src}' as='script' />";
			}
		}
		$resultJs .= "
				];";

		$resultJs .= "
				events.forEach(event => {
					document.addEventListener(event, function (e) {
						if (event == 'click') {
							window.lazyLoadJsStartEvent = {
								'target': e.target,
								'event': event
							};
						}

						if (window.lazyLoadJsWork) {
							return;
						}
						window.lazyLoadJsWork = true;
						document.dispatchEvent(new CustomEvent('lazyLoadJs'));
					}, { once: true });
				})

				setTimeout(function() {
					if (window.lazyLoadJsWork) {
						return;
					}
					document.dispatchEvent(new CustomEvent('lazyLoadJs'));
				}, 30000);

			}, { once: true });


		";

		$resultJs .= "
		document.addEventListener('lazyLoadJs', () => {
			LazyStart();
			LazyJS();
		})
		";

		$resultJs .= "

		LazyStart = () => {
			const body = document.querySelector('body');
			if (body.classList.contains('lazyLoadJs')) {
				return;
			}
			body.classList.add('lazyLoadJs');
			body.style.transition = 'opacity 0.3s';
			body.style.opacity = '0.8';
		}

		LazyFinish = () => {
			const body = document.querySelector('body');
			body.style.opacity = '1';
			if (window.lazyLoadJsStartEvent !== null) {
				window.lazyLoadJsStartEvent.target.click();
			}
		}

		LazyJS = () => {
			if (window.lazyLoadJsSrc.length == 0) {
				document.dispatchEvent(new CustomEvent('Lazyloadedjs'));
				document.dispatchEvent(new Event('DOMContentLoaded'));
				LazyFinish();
				return;
			}
			LoadJS(window.lazyLoadJsSrc[0]);
			window.lazyLoadJsSrc.shift();
		}

		function LoadJS(src) {
			const scrpt = document.createElement('script');
			const head = document.querySelector('head');
			scrpt.type = 'text/javascript';
			scrpt.src = src;
			scrpt.onload = LazyJS;
			head.appendChild(scrpt);
		}

		";
		$resultJs .= '</script>';

		if (is_array($arResult['script'])) {

			foreach ($arResult['script'] as $script) {
				$resultJs .= "<script>
					document.addEventListener('Lazyloadedjs', function() {
						";
				$resultJs .= ";{$script};";
				$resultJs .= "});
				</script>";
			}
		}

		foreach ($arResult['remove'] as $remove) {
			$content = str_replace($remove, '', $content);
		}

		$content = str_replace('</head>', $preload . '</head>', $content);
		$content = str_replace('</body>', $resultJs . '</body>', $content);
	}

	public static function PrepareResult($arScripts, $arParams): array
	{
		$arResult = [];
		foreach ($arScripts as $scriptItem) {
			if (self::isIgnore($scriptItem, $arParams)) {
				continue;
			};
			if (isset($scriptItem['src'])) {
				$arResult['src'][]    = $scriptItem['src'];
				$arResult['remove'][] = $scriptItem['tag'];
			} else if (isset($scriptItem['text'])) {
				$arResult['script'][] = $scriptItem['text'];
				$arResult['remove'][] = $scriptItem['tag'];
			}
		}

		return $arResult;
	}

	public static function get_tags($tag, $content, $haveClosedTag = true)
	{
		if ($haveClosedTag) {
			$arTag['tag'] = '/(<' . $tag . '[^>]*>)(.*)<\/' . $tag . '>/ismuU';;
		} else {
			$arTag['tag'] = '/(<' . $tag . '[^>]*>)/ismuU';
		};
		$arTag['attr'][0] = '/\s+([a-zA-Z-]+)\s*=\s*"([^"]*)"/ismuU';
		$arTag['attr'][] = str_replace('"', "'", $arTag['attr'][0]);
		$result = array();
		if (preg_match_all($arTag['tag'], $content, $matches)) {
			foreach ($matches[0] as $k => $match) {
				$res_tag = array();
				$res_tag['tag'] = $match;
				if (isset($matches[1][$k])) {
					foreach ($arTag['attr'] as $arTagAttr) {
						unset($attr_matches);
						preg_match_all($arTagAttr, $matches[1][$k], $attr_matches);
						if (is_array($attr_matches[1])) {
							foreach ($attr_matches[1] as $key => $val) {
								$res_tag[$val] = $attr_matches[2][$key];
							};
						};
					};
				};
				if (isset($matches[2][$k])) {
					$res_tag['text'] = $matches[2][$k];
				};
				$result[] = $res_tag;
			};
		};
		return $result;
	}

	public static function isIgnore(array $script, array $arParams): bool
	{
		$result = false;
		if (!is_array($arParams['IGNORE_SCRIPT'])) {
			return $result;
		}
		foreach ($arParams['IGNORE_SCRIPT'] as $key => $arVal) {
			if (!isset($script[$key])) {
				continue;
			}
			if (is_array($arVal)) {
				foreach ($arVal as $val) {
					if (mb_stripos($script[$key], $val) !== false) {
						$result = true;
						return $result;
					}
				}
			} else if (mb_stripos($script[$key], $arVal) !== false) {
				$result = true;
				return $result;
			}
		}
		return $result;
	}
}
