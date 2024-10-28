<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Video Per Day and Per Country</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
	<div class="container my-3">
        <h1>YouTube Authorization Demo</h1>
            <p>
                <strong>Status:</strong>
                <?php if(isset($connected) && $connected): ?>
                    Authorized. <a href='<?php echo base_url('views/disconnect'); ?>'>Disconnect</a>

                <div class="text-center mt-5 mb-5">
                    <h1>Video Per Day (Cara ke-1)</h1>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Video Id</th>
                                <th>Video Title</th>
                                <th>Views</th>
                                <th>Average View Duration</th>
                                <th>Estimated Minutes Watched</th>
                            </tr>
                        </thead>
                        <tbody id="responseData">
                            <?php if(!empty($viewSources)): ?>
                                <?php foreach($viewSources as $day => $entries): ?>
                                    <?php foreach($entries as $data): ?>
                                        <tr>
                                            <td><?php echo $day; ?></td>
                                            <td><?php echo $data['videoId']; ?></td>
                                            <td><?php echo $data['videoTitle']; ?></td>
                                            <td><?php echo $data['views']; ?></td>
                                            <td><?php echo $data['averageViewDuration']; ?></td>
                                            <td><?php echo $data['estimatedMinutesWatched']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-5 mb-5">
                    <h1>Video Per Country (Cara ke-1)</h1>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Video Id</th>
                                <th>Video Title</th>
                                <th>Views</th>
                                <th>Average View Duration</th>
                                <th>Estimated Minutes Watched</th>
                            </tr>
                        </thead>
                        <tbody id="responseData">
                            <?php if(!empty($viewSources1)): ?>
                                <?php foreach($viewSources1 as $country => $entries): ?>
                                    <?php foreach($entries as $data): ?>
                                        <tr>
                                            <td><?php echo $country; ?></td>
                                            <td><?php echo $data['videoId']; ?></td>
                                            <td><?php echo $data['videoTitle']; ?></td>
                                            <td><?php echo $data['views']; ?></td>
                                            <td><?php echo $data['averageViewDuration']; ?></td>
                                            <td><?php echo $data['estimatedMinutesWatched']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-5 mb-5">
                    <h1>Video Per Day and Per Country (Cara ke-2)</h1>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Video Id</th>
                                <th>Video Title</th>
                                <th>Country</th>
                                <th>Views</th>
                                <th>Average View Duration</th>
                                <th>Estimated Minutes Watched</th>
                            </tr>
                        </thead>
                        <tbody id="responseData">
                            <?php if(!empty($viewSources2)): ?>
                                <?php foreach($viewSources2 as $day => $entries): ?>
                                    <?php foreach($entries as $data): ?>
                                        <tr>
                                            <td><?php echo $day; ?></td>
                                            <td><?php echo $data['videoId']; ?></td>
                                            <td><?php echo $data['videoTitle']; ?></td>
                                            <td><?php echo $data['country']; ?></td>
                                            <td><?php echo $data['views']; ?></td>
                                            <td><?php echo $data['averageViewDuration']; ?></td>
                                            <td><?php echo $data['estimatedMinutesWatched']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php else: ?>
                    Not authorized. 
                    <a href='<?php echo isset($authUrl) ? $authUrl : '#'; ?>'>Authorize with YouTube...</a>
                <?php endif; ?>
            </p>
    </div>
</body>
</html>