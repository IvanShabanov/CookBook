preg_match_all('/\/\S+\.(png|jpe?g|gif)/i', $html, $media);
$uniqueArr = array_unique($media[0]);  
foreach($media[0] as $image) {

}