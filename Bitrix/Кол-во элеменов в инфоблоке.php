<?
$cnt = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => xx, ],
    [], /* Важно чтобы тут был пустой массив */
    false, /* Важно чтобы тут был false */
    ['ID', 'NAME']
);
echo $cnt;