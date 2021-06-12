<?php

// Загрузка файла
$file = __DIR__.'/results/result.xml';
if (!file_exists($file)) {
    echo '<h1>Файл "'.$file.'" не существует</h1>';
    exit();
}

$xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

// Сохранение результата
if (isset($_POST['article_index'])) {
    if (isset($_POST['text'])) {
        $xml->article[ (int)$_POST['article_index'] ]->text = $_POST['text'];
    }

    if (isset($_POST['name'])) {
        $xml->article[ (int)$_POST['article_index'] ]->name = $_POST['name'];
    }

    if (isset($_POST['title'])) {
        $xml->article[ (int)$_POST['article_index'] ]->title = $_POST['title'];
    }

    if (isset($_POST['sources'])) {
        $xml->article[ (int)$_POST['article_index'] ]->sources = $_POST['sources'];
    }

    $xml->asXml($file);

    $alert = 'Статья сохранена';
}

// Отображаемая статья
$article_index = (int)$_GET['article_index'] ? (int)$_GET['article_index'] : 0;


?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>


<div class="container mt-5">
    <?php if (!empty($alert)) { ?>
        <div class="alert alert-success" role="alert"><?=$alert?></div>
    <?php } ?>

    <div class="mb-5">
        <h1>Статья #<?=$article_index?> — <?=$xml->article[ $article_index ]->name ?? ''?></h1>
        <a href="/xml-redactor.php?article_index=<?=$article_index - 1?>" class="btn btn-secondary">Назад</a>
        <a href="/xml-redactor.php?article_index=<?=$article_index + 1?>" class="btn btn-secondary">Вперед</a>
    </div> 

    <form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
        <input type="hidden" name="article_index" value="<?=$article_index?>">
        <div class="mb-3">
            <textarea class="form-control summernote" name="text" rows="10"><?=$xml->article[ $article_index ]->text ?? ''?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Название</label>
            <input type="text" class="form-control" name="name" value="<?=htmlspecialchars($xml->article[ $article_index ]->name ?? '')?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" value="<?=htmlspecialchars($xml->article[ $article_index ]->title ?? '')?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Источники</label>
            <textarea class="form-control" name="sources" rows="2"><?=$xml->article[ $article_index ]->sources ?? ''?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Сохранить</button>
    </form>
</div>

<script>
    $(document).ready(function(){
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 600
            });
        });
    })
</script>