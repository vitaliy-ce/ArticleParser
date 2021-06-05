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

$article = new Article();

$skip_domains = [];
$skip_domains[] = parse_url($url, PHP_URL_HOST);

if (!empty($headers)) {
    foreach ($headers as $key_headers => $header) {
        // Получение сайтов по запросу
        Helpers::printMessage('['.($key_headers + 1).' из '.count($headers).'] Получим сайты по запросу: '.$header['header']);
        $links = $searcher->getLinks($header['header']);

        if (!empty($links)) {
            foreach ($links as $key_links => $link) {
                // Пропускаем первые 5 результатов
                if ($key_links < 3)  { continue; }

                // Пропускаем сайты в которых встречается wiki т.к. на них много лишнего
                if (stripos($link, 'wiki') !== false) { continue; }

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

Helpers::printMessage('Статья сформирована', 'green');
$article->save(__DIR__.'/results');


// var_dump($article_parts); die();