<?php
class Helpers 
{
    public static function memoryUsage() 
    {
		echo "[memory used — ".Helpers::fileSizeConvert(memory_get_usage(true))."/".ini_get('memory_limit')."]\n";
	}
   

	public static function fileSizeConvert($size) 
    {
	    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
	    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}
}