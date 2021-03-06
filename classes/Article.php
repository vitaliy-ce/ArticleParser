<?php

class Article
{
    private $article_parts;

    public function __construct()
    {
        $this->article_parts = [];
        $this->article_html = '';
    }

    public function addArticleParts($data)
    {
        $this->article_parts[] = [
            'source'        => $data['source']            ?? '',
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

        $article_xml = "\t<article>\n";
        $article_xml .= "\t\t<name>".$this->article_parts[0]['header']."</name>\n";
        $article_xml .= "\t\t<title>".$this->article_parts[0]['header']."</title>\n";
        $article_xml .= "\t\t<sources><![CDATA[".$this->getSources()."]]></sources>\n";
        $article_xml .= "\t\t<text><![CDATA[".$this->getHtml()."]]></text>\n";
        $article_xml .= "\t</article>\n";
        
        $old_xml = '';
        if (file_exists($storage_dir.'/result.xml')) {
            $old_xml = file_get_contents($storage_dir.'/result.xml');
            $old_xml = str_replace("<articles>\n", '', $old_xml);
            $old_xml = str_replace("</articles>\n", '', $old_xml);
        }
        
        $xml = "<articles>\n";
        $xml .=  $old_xml;
        $xml .=  $article_xml;
        $xml .= "</articles>\n";

        file_put_contents($storage_dir.'/result.xml', $xml);
    }

    // Сборка html статьи из частей
    private function getHtml()
    {
        $html = '';
        if (!empty($this->article_parts)) {
            foreach ($this->article_parts as $key => $article_part) {
                if (!empty($article_part['image_src']) && $key) {
                    $html .= '<img src="'.$article_part['image_src'].'">'."\n";
                }
                $html .= '<'.$article_part['type_header'].'>'.$article_part['header'].'</'.$article_part['type_header'].'>'."\n";                
                $html .= $article_part['html']."\n";
            }
        }

        return $html;
    }

    // Сайты с которых была вязта информация
    private function getSources()
    {
        $sources = [];
        if (!empty($this->article_parts)) {
            foreach ($this->article_parts as $article_part) {
                if (!empty($article_part['source'])) {
                    $sources[] = parse_url($article_part['source'], PHP_URL_HOST);
                }
            }
        }

        return implode(", ", $sources);
    }
}