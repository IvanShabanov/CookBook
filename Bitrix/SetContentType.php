<?php
set_time_limit(0);
error_reporting(E_ERROR);

// statistics off
@define('STOP_STATISTIC', true);
@define('NO_KEEP_STATISTIC', true);
@define('NO_AGENT_STATISTIC', 'Y');

// perfmon stop
@define('PERFMON_STOP', true);

// check permissions off
@define('NOT_CHECK_PERMISSIONS', true);

// dont show errors
@define('PUBLIC_AJAX_MODE', true);

// dont run agents
@define('NO_AGENT_CHECK', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
global $APPLICATION, $DB, $USER;

$APPLICATION->RestartBuffer();
$app = \Bitrix\Main\Application::getInstance();
$response = $app->getContext()->getResponse();
$responseHeadersHandler = $response->getHeaders();
$responseHeadersHandler->set('Content-Type', 'text/javascript; charset=UTF-8');

$buffer = 'Тут контент';

$response->flush($buffer);
$app->terminate();