<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$app['debug'] = true;

$app->get('/', function() {
    echo '<form method="post" enctype="multipart/form-data">';
    for ($i = 0; $i < 3; $i++) {
        echo '<input type="file" name="file[]">';
    }
    echo '<input type="submit">';
    echo '</form>';

    return '';
});

$app->post('/', function(Request $req) {
    $files = $req->files->get('file');
    foreach ($files as $index => $file) {
        $fileName = strval($index).'.jpg';
        $file->move('upload', $fileName);

        $img = new Imagick('upload/'.$fileName);

        $targetWidth = 600;
        $targetHeight = 480;

        $img->thumbnailImage($targetWidth, $targetHeight, true);

        $width = $img->getImageWidth();
        $height = $img->getImageHeight();

        $x = -($targetWidth - $width) / 2;
        $y = -($targetHeight - $height) / 2;

        $img->setImageBackgroundColor('black');
        $img->extentImage($targetWidth, $targetHeight, $x, $y);

        $img->writeImage('upload/'.$fileName);

        echo '<img src="upload/'.$fileName.'">';

        $img->clear();
    }
    exec('/usr/local/bin/ffmpeg -y -framerate 1/3 -i upload/%d.jpg -vf'
        .' scale=640:480,setsar=1 -r 5 -vcodec mpeg4 upload/movie.mp4'.' 2>&1'
        , $result);
    //var_dump($result);

    echo '<a href="upload/movie.mp4" download="upload/movie.mp4">動画ダウンロード</a>';

    return '';
});

$app->run();