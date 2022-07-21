/* Настройка для вывода ошибок в файл */

'exception_handling' =>
  array (
    'value' =>
        array (
          'debug' => false,
          'handled_errors_types' => 4437,
          'exception_errors_types' => 4437,
          'ignore_silence' => false,
          'assertion_throws_exception' => true,
          'assertion_error_type' => 256,
          'log' => array (
              'settings' => array (
                  'file'     => '__error.log',
                  'log_size' => 1000000,
              )
        ),
    ),
    'readonly' => false,
  ),