<?php
set_time_limit(0);
$fp = fopen('../cars.sql', 'r');
$sqlStr = array();
while ($fstr = fgets($fp)) {
  preg_match('/VALUES\s\((\d+),.*\)$/', $fstr, $ins_str);
  if (count($ins_str) > 0) {
    $htmlModel = file_get_contents('http://avtosale.ua/car/?Perform=1&page=1&markId=' . $ins_str[1], false);
    //$htmlModel = file_get_contents('newhtml.html');
    preg_match('/<select.*id="models".*>.*[-\n\s\ta-zA-ZабвгдеёжзийклмнопрстуфхцчшщъьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЬЭЮЯ\<\>\=\"0-9\/_\&\;\(\)\:\'\+]*<\/select>/', $htmlModel, $modelsSelect);
    if (count($modelsSelect) > 0) {
      $modelsSelect = explode("\n", $modelsSelect[0]);
      foreach ($modelsSelect as $opStr) {
        preg_match('/<option.*>(.*)<\/option>/', $opStr, $modelStr);
        if (count($modelStr) > 0 && strpos($modelStr[1],'Выберите') === false)$sqlStr[] = 'INSERT INTO `car_model`(`brand_id`, `value`) VALUES ('.$ins_str[1].', \''.$modelStr[1].'\');'."\n";
        else echo 'No pattern for: <b>' . $opStr. '</b><br>';
      }
    } else echo 'no models for ' . $ins_str[1] . '<br>';
  }
}
fclose($fp);
file_put_contents('../models.sql', $sqlStr);
?>
