<?php
class Searcher
{
    private $charset_fix;
    
    public function __construct()
    {

    }

    /**
     * Получим ссылки на сайты
     * https://m.habr.com/ru/post/545196/
     * 
     * @param  string $query Поисковой запрос
     */
    public function getLinks(string $query): array
    {
        $url = 'https://searx.roughs.ru/search?q='.urlencode($query).'&language=ru-RU&format=json';
        $response = file_get_contents($url);
        $response = json_decode($response, true);
        
        $links = [];
        if (!empty($response['results'])) {
            foreach ($response['results'] as $item) {
                $links[] = $item['url'];
            }
        }

        return $links;
    }

    /**
     * Получение картинки по запросу
     * https://m.habr.com/ru/post/545196/
     * 
     * @param  string $query Поисковой запрос
     */
    public function getImageSrc(string $query)
    {
        $url = 'https://searx.roughs.ru/search?q='.urlencode($query).'&language=ru-RU&format=json&categories=images&safesearch=1';
        $response = file_get_contents($url);
        $response = json_decode($response, true);
        
        $image_src = null;
        if (!empty($response['results'][0]['img_src'])) {
            $image_src = $response['results'][0]['img_src'];
        }

        return $image_src;
    }
}