<?php
class ParserSite
{
    private $charset_fix;
    
    public function __construct(string $charset = 'utf-8')
    {
        // Фикс кодировки, для некоторых сайтов без этого не работает парсинг
        switch ($charset) {
            case 'windows-1251': 	
                $this->charset_fix = '<meta charset="utf-8">'; break;
            default: 				
                $this->charset_fix = '<meta charset="utf-8">'; break;
        }
    }

    /**
     * Получим заголовки со страницы
     * 
     * @param  string $url Ссылка на страницу
     */
    public function getHeaders(string $url): array
    {
        $headers = [];
        $document = $this->getDocument($url);

        // Заголовок h1
        $headers[] = [
            'type'    => 'h1',
            'header'  => $document->find('h1')->text(),
        ];

        // Заголовки h2
        foreach($document->find('.td-post-content h2') as $header) {
            $headers[] = [
                'type'    => 'h2',
                'header'  => pq($header)->text(),
            ];
        }

        // Очистим память
        unset($document);
        $this->clearMemory();

        return $headers;
    }

    /**
     * Получим текст стать
     * 
     * @param  string $url Ссылка на страницу
     */
    public function getArticle(string $url): string
    {
        $document = $this->getDocument($url);

        // Алгоритм
        // https://habr.com/ru/company/mailru/blog/200394/

        // Получим плоский список всех div
        $items = [];
        foreach($document->find('div') as $key => $div) {
            $div_pq = pq($div); 

            $tmp = [];
            $tmp['raw']         = $div;
            $tmp['class']       = $div_pq->attr('class');
            $tmp['count_p']     = $div_pq->find('p')->length;
            $tmp['count_div']   = $div_pq->find('div')->length;

            if ($tmp['count_div'] > 0 && $tmp['count_p'] > 0) {
                $tmp['ratio'] = $tmp['count_p'] / $tmp['count_div'];
            } else {
                $tmp['ratio'] = 0;
            }

            // формируем ключ для сортировки
            // так чтобы последний элемент массива имел максимальное количество тегов p при минимальном количестве тегов div
            $key_items = '';
            $key_items .= str_pad(round($tmp['ratio'] * $tmp['count_p'] * 10000), 10, 0, STR_PAD_LEFT);
            $key_items .= str_pad(($tmp['count_p']), 5, 0, STR_PAD_LEFT);
            $key_items .= str_pad($key, 5, 0, STR_PAD_LEFT);

            $items[ $key_items ] = $tmp;

            unset($div_pq);
        }

        if (empty($items)) { return ''; }

        // Текст статьи содержится тут т.к. тут больше всего тегов p
        ksort($items);
        $container = array_pop($items);
        $container_pq = pq($container['raw']);


        // Заранее удалим не нужные теги
        $remove_tags = ['blockquote', 'iframe', 'script', 'style', 'table'];
        foreach ($remove_tags as $item) {
            $container_pq->find($item)->remove();
        }

        // Для следующих шагов нужен html код
        $container_html = trim($container_pq->html());
        
        // Вырежем ссылки оставим только анкор
        $container_html = preg_replace('/<a.*?>(.*?)<\/a>/m', '$1', $container_html);

        // Вырежем теги в внутри тегов li
        preg_match_all ('/<li.*?>(.*?)<\/li>/m', $container_html, $matches, PREG_SET_ORDER);
        if (!empty($matches)) {
            foreach ($matches as $item) {
                $container_html = str_replace($item[0], '<li>'.strip_tags($item[1]).'</li>', $container_html);
            }
        }
        
        // Костыль.. пока нет решения как сделать лучше
        // Для сохранения порядка следования все превращаем вымышленный тег find, а потом исправим обратно
        $search = [
            '<p', '</p>',
            '<ul', '</ul>',
            '<ol', '</ol>',
            '<h2', '</h2>',
            '<h3', '</h3>',
        ];
        $replace = [
            '<find data-old_tag="p"', '</find>',
            '<find data-old_tag="ul"', '</find>',
            '<find data-old_tag="ul"', '</find>',
            '<find data-old_tag="h2"', '</find>',
            '<find data-old_tag="h3"', '</find>',
        ];
        
        $container_html = str_replace($search, $replace, $container_html);


        // Оборачиваем в div, без этого некоректно работает find()
        $container_html = '<div>'.$container_html.'</div>';

        // Далее нужно получим нужные нам теги и преобразовать их обратно
        $content = [];
        foreach(pq($container_html)->find('find') as $item) {
            $item_pq = pq($item); 
            $text = trim($item_pq->text());
            $old_tag = $item_pq->attr('data-old_tag') ?? 'p';

            if (!empty($text)) {
                if ($old_tag == 'ul') {
                    $lists = [];
                    foreach ($item_pq->find('li') as $item_1) {
                        $text_1 = trim(pq($item_1)->text());
                        if (!empty($text_1)) {
                            $lists[] = '<li>'.trim($text_1).'</li>';
                        }
                    }
                    if (!empty($lists)) {
                        $content[] = "<ul>\n".implode("\n", $lists)."\n</ul>";
                    }
                } else {
                    $content[] = '<'.$old_tag.'>'.trim($item_pq->text()).'</'.$old_tag.'>';
                }
            }
        }

        // Соберем контент если нашлось больше 5 элементов (p, ul и т.д.)
        $article = '';
        if (count($content) >= 5) {
            $article = implode("\n", $content);
        }
        
        // Очистим память
        unset($document);
        $this->clearMemory();

        return $article;
    }



    private function getDocument(string $url)
    {
        $html = file_get_contents($url);
        $document = phpQuery::newDocument($this->charset_fix.$html);

        return $document;
    }

    // Очистка памяти т.к. без этого происходит утечка памяти
    private function clearMemory()
    {
        phpQuery::unloadDocuments();
        phpQuery::resetAllData();
    }
}