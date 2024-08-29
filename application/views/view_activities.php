<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Youtube API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>

	<div class="container py-5">
		<div class="row">
			<div class="col-lg-12">
				<h4 class="text-center"><?= $title; ?></h4>
                    <iframe src="https://www.youtube.com/embed/<?= $id; ?>" frameborder="0" allowfullscreen
                    style="width:100%; height:500px"></iframe>
                    <div class="d-grid">
                    <a href="<?= base_url() ?>" class="btn btn-success">Kembali ke List Video</a>
                </div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
