<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Youtube API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  </head>
  <body>
      <div class="container py-5">
        <div class="row">
          <div class="col-lg-12">
            <div id="video-data">
              <h2>Video Details</h2>
                <div id="video-title"></div>
                <div id="video-description"></div>
                <div id="video-thumbnail"></div>
                <div class="d-grid mt-5">
                  <a href="<?= base_url() ?>" class="btn btn-success">Kembali ke List Video</a>
                </div>
            </div>
          </div>
        </div>
      </div>
    
      


    <script>
      $(document).ready(function() {
    var apiKey = 'AIzaSyAnJAuRtMY6xAJBhz1KWfdgGawBRWLmnTg';
    var videoId = 'fG08dcJ8xFE';
    var apiUrl = 'https://www.googleapis.com/youtube/v3/videos';

    $.ajax({
        url: apiUrl,
        type: 'GET',
        data: {
            part: 'snippet',
            id: videoId,
            key: apiKey
        },
        success: function(response) {
            if (response.items.length > 0) {
                var video = response.items[0].snippet;
                $('#video-title').text(video.title);
                $('#video-description').text(video.description);
                //$('#video-thumbnail').html('<img src="' + video.thumbnails.default.url + '" alt="Thumbnail">');
                $('#video-thumbnail').html('<img src="' + video.thumbnails.high.url + '" alt="Thumbnail" class="img-fluid">');
            } else {
                $('#video-data').html('<p>No video found</p>');
            }
        },
        error: function() {
            $('#video-data').html('<p>Failed to retrieve video data</p>');
        }
    });
});

    </script>
    </body>
</html>
