<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Youtube API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
  <?php
    $API_KEY = "AIzaSyCDegFvEerlIxCT2RTq3jiHPxj0FiErVP8";
    $Channel_ID = "UCucLj6AYL67BUTpfR6Um5fw";
    $Max_Results = 10;
    $apiError = "Error";

	$api = 'https://www.googleapis.com/youtube/v3/activities?order=date&part=snippet';
	$apiData = $api . '&channelId=' . $Channel_ID . '&maxResults=' . $Max_Results . '&key=' . $API_KEY . '';
    $apiData1 = @file_get_contents($apiData);
    if ($apiData1) {
        $videoList = json_decode($apiData1);
        //echo '<pre>'; 
        //var_dump($videoList); 
        //echo '</pre>';
    }
    else {
        echo "API KEY atau Channel masih Salah!";  
        echo $apiData; 
    }
    ?>

<div class="container py-5">
		<div class="row">
			<div class="col-lg-12">
            <h4 class="text-center">Activities</h4>
            <?php if(!empty($videoList->items)) { ?>
            <div class="row">
                <?php foreach ($videoList->items as $item) : ?>
                    <?php if(!empty($item->id)) : ?>
                    <div class="col-md-3 mb-3">
                        <div class="card border-light">
                            <!--a href="https://www.youtube.com/watch?v=< ?= $item->id ?>" target="_blank"-->
                            <img src ="<?= $item->snippet->thumbnails->medium->url; ?>" class="card-img-top">
                            <div class="card-body">
                                <h6 class="card-title"><?= $item->snippet->title; ?></h6>
                                <small class="text-muted">Upload Date : <?= substr($item->snippet->publishedAt, 0, 10); ?></small>
                                <h6 class="card-title">By : <?= $item->snippet->channelTitle; ?></h6>
                                <!--?php echo anchor('activities/view/'.$item->id.'/'.$item->snippet->title, '<div class="btn btn-primary">Tonton Sekarang</div>'); ?-->
                            </div>
                            <!--/a-->
                        </div>
                    </div>
                    <?php else: ?>                
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
		<?php
        } else {
            echo $apiError;
        } ?>
            </div>
        </div>
</div>
</body>
</html>