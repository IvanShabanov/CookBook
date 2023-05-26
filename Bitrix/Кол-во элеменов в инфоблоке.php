<?
$cnt = CIBlockElement::GetList(
    array(),
    array('IBLOCK_ID' => xx, .....),
    array(),
    false, /* Важно чтобы тут был false */
    array('ID', 'NAME')
);
echo $cnt;