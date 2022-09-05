<?php
/* Веорнет минимум 1 (если кол-во элементов = 0) или максимальное кол-во страниц */
$maxpages = max(1, ceil($total_items / $items_on_page));

if (!function_exists('remove_key_from_url')) {
	function remove_key_from_url($key)
	{
		parse_str($_SERVER['QUERY_STRING'], $vars);
		$url = strtok($_SERVER['REQUEST_URI'], '?') . http_build_query(array_diff_key($vars, array($key => "")));
		return $url;
	}
}

if (!function_exists('paginator')) {
	function paginator($page, $maxpage, $url = '', $pageParam = "p", $range = 3, $showFirstLast = true, $showPrevNext = true)
	{
		if ($page < 1) {
			$page = 1;
		};
		if ($page > $maxpage) {
			$page = $maxpage;
		};

		if ($url == '') {
			$url = remove_key_from_url($pageParam);
		};
		$paginator = '';
		$afterurl = '';
		if (strpos($url, '?') > 0) {
			$afterurl = '&';
		} else {
			$afterurl = '?';
		}
		if ($maxpage > 1) {
			$i = 1;
			$paginator .= '<div class="paginator">';
			$disabled = '';
			if ($page == 1) {
				$disabled = ' disabled';
			}
			if ($showFirstLast) {
				$paginator .= '<a href="' . $url . '" class="first ' . $disabled . '">&lt;&lt;</a>';
			};
			if ($showPrevNext) {
				if (max($page - 1, 1) == 1) {
					$paginator .= '<a href="' . $url . '" class="prev ' . $disabled . '">&lt;</a>';
				} else {
					$paginator .= '<a href="' . $url . $afterurl . $pageParam . '=' . max($page - 1, 1) . '" class="prev">&lt;</a>';
				};
			};

			while ($i <= $maxpage) {
				if (($i > $page - $range) and ($i < $page + $range)) {
					if ($i == $page) {
						$paginator .= '<span class="current">' . $i . '</span>';
					} else {
						if ($i == 1) {
							$paginator .= '<a href="' . $url . '" class="pagenumber">' . $i . '</a>';
						} else {
							$paginator .= '<a href="' . $url . $afterurl . $pageParam . '=' . $i . '" class="pagenumber">' . $i . '</a>';
						}
					}
				} elseif (($i == $page - $range) or ($i == $page + $range)) {
					$paginator .= '<a href="' . $url . $afterurl . $pageParam . '=' . $i . '" class="pagedots">...</a>';
				}
				$i++;
			};
			$disabled = '';
			if ($page == $maxpage) {
				$disabled = ' disabled';
			}
			if ($showPrevNext) {
				$paginator .= '<a href="' . $url . $afterurl . $pageParam . '=' . min($page + 1, $maxpage) . '" class="next' . $disabled . '">&gt;</a>';
			};
			if ($showFirstLast) {
				$paginator .= '<a href="' . $url . $afterurl . $pageParam . '=' . $maxpage . '" class="last' . $disabled . '">&gt;&gt;</a>';
			};

			$paginator .= '</div>';
		}
		return $paginator;
	}
}


?>

<style>
	.paginator {
		text-align: center;
		margin: 30px;
	}

	.paginator>a,
	.paginator>* {
		display: inline-block;
		width: 30px;
		height: 30px;
		line-height: 30px;
		text-align: center;
		text-decoration: none;
		background: none;
		color: #555;
		margin: 5px;
		font-style: normal;
		font-weight: normal;
	}

	.paginator>a:hover {
		background: #E2383B;
		color: #fff;
		cursor: pointer;
	}

	.paginator>.current {
		background: #F2484B;
		color: #fff;
	}
</style>