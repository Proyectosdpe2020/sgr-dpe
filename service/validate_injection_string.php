<?php

function cleanTextInjec($str){

	$str=trim($str);
	$str=stripslashes($str);
	$str=str_ireplace("<script>", "", $str);
	$str=str_ireplace("</script>", "", $str);
	$str=str_ireplace("<script src", "", $str);
	$str=str_ireplace("<script type=", "", $str);
	$str=str_ireplace("SELECT * FROM", "", $str);
	$str=str_ireplace("DELETE FROM", "", $str);
	$str=str_ireplace("INSERT INTO", "", $str);
	$str=str_ireplace("DROP TABLE", "", $str);
	$str=str_ireplace("DROP DATABASE", "", $str);
	$str=str_ireplace("TRUNCATE TABLE", "", $str);
	$str=str_ireplace("SHOW TABLES;", "", $str);
	$str=str_ireplace("SHOW DATABASES;", "", $str);
	$str=str_ireplace("<?php", "", $str);
	$str=str_ireplace("?>", "", $str);
	$str=str_ireplace("--", "", $str);
	$str=str_ireplace("^", "", $str);
	$str=str_ireplace("<", "", $str);
	$str=str_ireplace("[", "", $str);
	$str=str_ireplace("]", "", $str);
	$str=str_ireplace("==", "", $str);
	$str=str_ireplace("=", "", $str);
	$str=str_ireplace(";", "", $str);
	$str=str_ireplace("::", "", $str);
	$str=str_ireplace("'", "", $str);
	$str=trim($str);
	$str=stripslashes($str);

	return $str;
}

?>