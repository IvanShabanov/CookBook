'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

/***************************************/

$currentLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

/***************************************/

function getCurrentUrl($server, $useForwardedHost = false){
    $ssl      = ( ! empty( $server['HTTPS'] ) && $server['HTTPS'] == 'on' );
    $sp       = strtolower( $server['SERVER_PROTOCOL'] );
    $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
    $port     = $server['SERVER_PORT'];
    $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
    $host     = ( $useForwardedHost && isset( $server['HTTP_X_FORWARDED_HOST'] ) ) ? $server['HTTP_X_FORWARDED_HOST'] : ( isset( $server['HTTP_HOST'] ) ? $server['HTTP_HOST'] : null );
    $host     = isset( $host ) ? $host : $server['SERVER_NAME'] . $port;
    return $protocol . '://' . $host . $server['REQUEST_URI'];
}

echo getCurrentUrl($_SERVER);