/**
 * @Auth: wonli <wonli@live.com>
 * api.tpl.php
 */
if (!empty($_REQUEST)) {
    $expire_time = time() + 86400000;
    if(!empty($_REQUEST['platform'])) {
        setcookie('platform', $_REQUEST['platform'], $expire_time);
    }

    if (!empty($_REQUEST['channel'])) {
        setcookie('channel', $_REQUEST['channel'], $expire_time);
    }

    if (!empty($_REQUEST['version'])) {
        setcookie('version', $_REQUEST['version'], $expire_time);
    }

    if (!empty($_SERVER['HTTP_REFERER'])) {
        header("Location: {$_SERVER['HTTP_REFERER']}");
    }
}

if (!isset($_COOKIE['platform'])) {
    $_COOKIE['platform'] = '';
}

if (!isset($_COOKIE['channel'])) {
    $_COOKIE['channel'] = '';
}

if (!isset($_COOKIE['version'])) {
    $_COOKIE['version'] = '';
}


