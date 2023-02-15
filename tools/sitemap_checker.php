<?php header('Access-Control-Allow-Origin: *'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sitemap.xml chekcer</title>
</head>
<body>

<input id="sitemapUrl" type="text" placeholder="https://site.ru/sitemap.xml" value="" />
<button id="btnCheck" type="button" onclick="Start()">Check</button>
<div id="res"></div>

<script>

let counter = 0;

function Start() {
	const sitemapUrl = document.querySelector('#sitemapUrl').value;
	const res = document.querySelector('#res');
	res.innerHTML = 'loading....';
	GetSitemap(sitemapUrl);
}

function GetSitemap(url) {
	if (url != '') {
        fetch(url, {
            method: 'GET',
			headers: {'Content-Type': 'application/json'},
        }).then(function (response) {
			return response.text();
        }).then(function (result){
			console.log(result);
			let parser = new DOMParser();
    		let SitemapContent = parser.parseFromString(result, "text/xml");
			CreateResultTable(SitemapContent);
		}).catch(function (err) {
			const res = document.querySelector('#res');
            res.innerHTML = 'ERROR cant get sitemap.xml';
            console.log('Something went wrong. ', err);
        });
    };
}

function CreateResultTable(SitemapContent) {

	const res = document.querySelector('#res');
	let table = '';
	let urls = SitemapContent.getElementsByTagName('url');
	if (urls.length == 0) {
		urls = SitemapContent.getElementsByTagName('sitemap');
	}

	if (urls.length > 0) {
		console.log(urls);
		for (var i = 0; i < urls.length; i++) {
        	const urlElement = urls[i];
			const loc = urlElement.getElementsByTagName('loc')[0].textContent;
			table += '<tr>';
			table += '<td class="link"  style="border-bottom: 1px solid #888;">';
			table += '<a href="' + loc + '" target="_blank">' + loc + '</a>';
			table +=  '</td>';
			table +=  '<td class="res">';
			table +=  '</td>';
			table +=  '<td class="res">';
			table +=  '<button type="button">Check Again</button>';
			table +=  '</td>';
			table +=  '</tr>';
		};

		if (table.length > 0) {
			table = '<table>' + table + '</table>';
        	res.innerHTML = table;
			CheckTable();
		}

	}
}

function CheckTable() {
	counter = 0;
    const trs = document.querySelectorAll('tr');
    trs.forEach(tr => {
        const reload = tr.querySelector('button');
        reload.addEventListener('click', function(event){
            CheckLine(tr);
        });
    });
	CheckLineLazy();
}

function CheckLineLazy() {
	const trs = document.querySelectorAll('tr');
	if ((trs.length > 0) && (counter <= trs.length)) {
		CheckLine(trs[counter]);
		counter ++;

	}
}



function CheckLine(tr) {
    const url = tr.querySelector('a').getAttribute('href');
    const res = tr.querySelector('.res');
    res.innerHTML = 'loading....';
    CheckLink(url, res);
}

function CheckLink(url, res) {
    if (url != '') {
        fetch(url, {
            method: 'GET',
			headers: {'Content-Type': 'application/json'},
        }).then(function (response) {
            res.innerHTML =  response.status;
			setTimeout(function() {
				CheckLineLazy();
			}, 100);

        }).catch(function (err) {
            res.innerHTML = 'ERROR';
            console.log('Something went wrong. ', err);
        });
    };
};

</script>

</body>
</html>
