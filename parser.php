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

// Получение заголовоков
$url = 'https://www.botanichka.ru/article/pochemu-plachet-abrikos-o-kamedetechenii-podrobno/';
Helpers::printMessage('Получим заголовки с сайта '.$url);
$headers = $parser->getHeaders($url);

$article_parts = [];

if (!empty($headers)) {
    foreach ($headers as $key_headers => $header) {
        // Получение сайтов по запросу
        Helpers::printMessage('['.($key_headers + 1).' из '.count($headers).'] Получим сайты по запросу: '.$header['header']);
        $links = $searcher->getLinks($header['header']);

        if (!empty($links)) {
            foreach ($links as $key_links => $link) {
                // Пропускаем первые 5 результатов
                if ($key_links < 5)  { continue; }

                Helpers::printMessage('Пробуем получить статью: '.$link, 'grey');
                $article = $parser->getArticle($link);
                if (!empty($article)) {
                    $article_parts[] = [
                        'source' => $link,
                        'header' => $header,
                        'html'   => $article,
                    ];

                    break;
                }
            }
        }
    }
}

Helpers::printMessage('Статья сформирована: '.$link, 'green');
$html = implode("\n\n", array_column($article_parts, 'html'));
file_put_contents('result.html', $html);


// var_dump($article_parts); die();