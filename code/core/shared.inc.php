<?php
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function isValidExcelDate($str)
{
	if(strstr($str, "-")) {
		list($m, $d, $y) = explode("-", $str);
		return is_numeric($d) && is_numeric($m) && is_numeric($y) && checkdate($m, $d, $y);
	} else {
		list($d, $m, $y) = explode("/", $str);
		return is_numeric($d) && is_numeric($m) && is_numeric($y) && checkdate($m, $d, $y);
	}
}

function convertExcelDateToTimestamp($str)
{
	if(isValidExcelDate($str)) {
		if(strstr($str, "-")) {
			list($m, $d, $y) = explode("-", $str);
			return mktime(0, 0, 0, $m, $d, $y);
		} else {
			list($d, $m, $y) = explode("/", $str);
			return mktime(0, 0, 0, $m, $d, $y);
		}
	}
}

?>