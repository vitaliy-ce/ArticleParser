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
        $url = 'https://searx.roughs.ru/search?q='.urlencode($query).'&format=json';
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
}