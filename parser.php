<?php

error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', '1');
ini_set('memory_limit', '2048M');

// Реквизиты
// define('DB_HOST', '');
// define('DB_USERNAME', '');
// define('DB_PASSWORD', '');
// define('DB_DATABASE_NAME', '');


// Простейшая авто-загрузка классов
spl_autoload_register(function ($class_name) {
    include 'classes/'.$class_name.'.php';
});

$parser = new ParserSite();
$searcher = new Searcher();

// Ссылки для парсинга
$sources = file_get_contents('_links.txt');
$sources = explode("\n", $sources);

if (!empty($sources)) {
    foreach ($sources as $key_sources => $source) {    
        // Получение заголовоков
        Helpers::printMessage('Сайт: '.($key_sources + 1).'/'.count($sources).' — Получим заголовки с сайта '.$source);
        $headers = $parser->getHeaders(trim($source));
        
        $article = new Article();
        
        $skip_domains = [];
        $skip_domains[] = parse_url($source, PHP_URL_HOST);
        
        if (empty($headers)) {
            Helpers::printMessage('Не удалось получить заголовки', 'red');
            continue;
        }

        foreach ($headers as $key_headers => $header) {
            // Получение сайтов по запросу
            Helpers::printMessage('Сайт: '.($key_sources + 1).'/'.count($sources).', заголовок: '.($key_headers + 1).'/'.count($headers).' — Получим сайты по запросу: '.$header['header']);
            $links = $searcher->getLinks($header['header']);
    
            if (!empty($links)) {
                foreach ($links as $key_links => $link) {
                    // Пропускаем первые 5 результатов
                    if ($key_links < 3)  { continue; }
    
                    // Пропускаем сайты информация с которых не подойдет
                    if (stripos($link, 'wiki') !== false) { continue; }
                    if (stripos($link, 'pinterest') !== false) { continue; }
                    if (stripos($link, 'reddit') !== false) { continue; }
                    if (stripos($link, 'ok.ru') !== false) { continue; }
                    if (stripos($link, 'ozon.ru') !== false) { continue; }
    
                    // Пропускаем сайты с которых уже брали информацию
                    if (in_array(parse_url($link, PHP_URL_HOST), $skip_domains)) { continue; } 
    
                    Helpers::printMessage('Пробуем получить статью: '.$link, 'grey');
                    $article_html = $parser->getArticle($link);
    
                    if (!empty($article_html)) {
                        $skip_domains[] = parse_url($link, PHP_URL_HOST);
    
                        Helpers::printMessage('Получим картинку', 'grey');
                        $image_src = $searcher->getImageSrc($header['header']);
    
                        $article->addArticleParts([
                            'source'        => $link,
                            'header'        => $header['header'],
                            'type_header'   => $header['type'],
                            'html'          => $article_html,
                            'image_src'     => $image_src,
                        ]);
    
                        break;
                    }
                }
            }
        }
    }
}

Helpers::printMessage('Статья сформирована', 'green');
$article->save(__DIR__.'/results');


// var_dump($article_parts); die();