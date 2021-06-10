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

    public static function getHtmlByUrl($url)
    {
        $headers = array(
            'cache-control: max-age=0',
            'upgrade-insecure-requests: 1',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.77 Safari/537.36',
            'sec-fetch-user: ?1',
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'x-compress: null',
            'sec-fetch-site: none',
            'sec-fetch-mode: navigate',
            'accept-encoding: deflate, br',
            'accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
        );
         
        $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/../cookie.txt');
        // curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/../cookie.txt');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //следование 302 redirect 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

        $html = curl_exec($ch);
        
        // DEBUG
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_STDERR, fopen('php://stderr', 'w'));
        // if ($html === FALSE) {
        //     echo 'cURL Error: ' . curl_error($ch)."\n";
        //     echo 'cURL Error: ' . curl_errno($ch)."\n";
        //     var_dump($html); die();
        //     return;
        // }
        
        curl_close($ch);        
        
        return $html;
    }

    public static function printMessage($message, $text_color = '')
    {
        switch ($text_color) {
            case 'red':
                $color_code = '0;31'; break;
            case 'green':
                $color_code = '0;32'; break;
            case 'yellow':
                $color_code = '1;33'; break;
            case 'white':
                $color_code = '1;37'; break;
            case 'grey':
                $color_code = '0;37'; break;

            default: 
                $color_code = '1;37'; break;
        }
        
        $total_message = "\033[0;37m[".date('H:i:s d.m.Y')."]\033[0m ";
        $total_message .= "\033[".$color_code."m".$message."\033[0m";
        $total_message .= "\n";

        echo $total_message;
    }
}