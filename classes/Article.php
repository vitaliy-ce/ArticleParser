<?php

class Article
{
    private $article_parts;
    private $article_html;

    public function __construct()
    {
        $this->article_parts = [];
        $this->article_html = '';
    }

    public function addArticleParts($data)
    {
        $this->article_parts[] = [
            'source'        => $data['link']            ?? '',
            'header'        => $data['header']          ?? '',
            'type_header'   => $data['type_header']     ?? 'h2',
            'html'          => $data['html']            ?? '',
            'image_src'     => $data['image_src']       ?? '',
        ];
    }

    public function save($storage_dir)
    {
        if (!is_dir($storage_dir)) {
            mkdir($storage_dir, 0755, true);
        }

        $this->generateArticle();
        file_put_contents($storage_dir.'/result.html', $this->article_html);
    }

    private function generateArticle()
    {
        if (!empty($this->article_parts)) {
            foreach ($this->article_parts as $article_part) {
                if (!empty($article_part['image_src'])) {
                    $this->article_html .= '<img src="'.$article_part['image_src'].'">'."\n";
                }
                $this->article_html .= '<'.$article_part['type_header'].'>'.$article_part['header'].'</'.$article_part['type_header'].'>'."\n";                
                $this->article_html .= $article_part['html']."\n";
            }
        }
    }
}