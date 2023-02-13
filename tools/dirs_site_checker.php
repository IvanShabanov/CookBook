<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dir as link chekcer</title>
</head>
<body>



<?php

/***********************************************************/
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
/***********************************************************/
function DirList($directory, $ignore = array('bitrix', 'upload', 'uploads')) {
    $result = array();
    if ($handle = opendir($directory)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' and $file != '..' and is_dir($directory.$file)) {
                if (!in_array($file, $ignore)) {
                    $result[] = $directory.$file.'/';
                    $result = array_merge($result, DirList($directory.$file.'/', $ignore));
                };
            };
        };
    };
    closedir($handle);
    return $result;
};

function get_http_response_code($theURL) {
    $headers = get_headers($theURL);
    return substr($headers[0], 9, 3);
}

$result = DirList($_SERVER['DOCUMENT_ROOT'].'/', array(
    'bitrix',
    'upload',
    'local',
    'images'
));
echo '<table>';
foreach ($result as $r) {
    $https = 'https://'.$_SERVER['HTTP_HOST'].'/';
    $link = $https.str_replace($_SERVER['DOCUMENT_ROOT'].'/','',$r);
    echo '<tr>';
    echo '<td class="link"  style="border-bottom: 1px solid #888;">';
    echo '<a href="'.$link.'" target="_blank">'.$link.'</a>';
    echo '</td>';
    echo '<td class="res">';
    echo '</td>';
    echo '<td class="res">';
    echo '<button type="button">Check Again</button>';
    echo '</td>';
    echo '</tr>';
}
echo '</table>';
?>
<script>
function ChecrLink(url, res) {
    if (url != '') {
        fetch(url, {
            method: 'GET',
        }).then(function (response) {
            res.innerHTML =  response.status;
        })
        .catch(function (err) {
            res.innerHTML = 'ERROR';
            console.log('Something went wrong. ', err);
        });
    };
};

function CheckLine(tr) {
    const url = tr.querySelector('a').getAttribute('href');
    const res = tr.querySelector('.res');
    res.innerHTML = 'loading....';
    ChecrLink(url, res);
}

document.addEventListener('DOMContentLoaded', function(){
    const trs = document.querySelectorAll('tr');
    trs.forEach(tr => {
        CheckLine(tr);
        const reload = tr.querySelector('button');
        reload.addEventListener('click', function(event){
            CheckLine(tr);
        });
    });
})
</script>
</body>
</html>
