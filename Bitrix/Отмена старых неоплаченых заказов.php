<?
function cancelExpiredOrder()
{
	$obTargetDate = new \Bitrix\Main\Type\Date();
	$obTargetDate->add('-30 days'); /* -30 дней */
	$targetDate = $obTargetDate->format('d.m.Y');

	$arOrders = \Bitrix\Sale\OrderTable::getList([
		'filter' => [
			'PAYED'        => 'N',
			'CANCEL'       => 'N',
			'<DATE_INSERT' => $targetDate,

			/* Если надо по времени статуса */
			/*
			'<DATE_STATUS' => $targetDate,
			'STATUS_ID'    => 'NB',
			*/
		],
		'select' => ['ID']
	])->fetchAll();

	$cancelDescription = "Автоотмена заказа. Просрочена оплата";

	foreach ($arOrders as $arOrder) {
		\Bitrix\Sale\Compatible\OrderCompatibility::cancel($arOrder['ID'], 'Y', $cancelDescription);
	}
	return 'cancelExpiredOrder();'
}
