<?php
$locale = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
echo $locale;
phpinfo();
?>
