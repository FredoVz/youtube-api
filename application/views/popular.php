<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Video Title</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
        <div class="container my-3">
        <h1>YouTube Authorization Demo</h1>
            <p>
                <strong>Status:</strong>
                <?php if(isset($connected) && $connected): ?>
                    Authorized. <a href='<?php echo base_url('popular/disconnect'); ?>'>Disconnect</a>

                    <div class="text-center">
                        <h1>Popular</h1>

                        <div class="row justify-content-center align-items-center">
                            <div class="col-auto">
                                <form method="post" action="<?php echo base_url('popular'); ?>">
                                    <!--input type="hidden" name="period" id="periodInput" value="month"-->
                                    <!--button type="submit" class="btn btn-primary" onclick="onClick('week')">Week</button>
                                    <button type="submit" class="btn btn-primary" onclick="onClick('month')">Month</button-->
                                    <button type="submit" class="btn btn-primary" name="period" value="week">Week</button>
                                    <button type="submit" class="btn btn-primary" name="period" value="month">Month</button>
                                </form>
                            </div>
                        </div>


                        <!--form method="post" action="< ?php echo base_url('popular'); ?>">
                            <div class="row justify-content-center align-items-center">
                                <div class="col-auto">
                                    <div class="dropdown">
                                        <button id="dropdownButton" class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Select
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" onclick="selectChannel('Week', 'week')">Week</a></li>
                                            <li><a class="dropdown-item" onclick="selectChannel('Month', 'month')">Month</a></li>
                                        </ul>
                                        //Hidden input to store selected channel id
                                            <input type="hidden" name="period" id="periodInput" value="month">
                                    </div>
                                </div>
                                
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form-->
                        

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Video Id</th>
                                    <th>Views</th>
                                </tr>
                            </thead>
                            <tbody id="responseData">
                                <?php if(!empty($viewSources)): ?>
                                    <?php foreach($viewSources as $day => $entries): ?>
                                        <?php foreach($entries as $data): ?>
                                            <tr>
                                                <td><?php echo $day; ?></td>
                                                <td><?php echo $data['videoId']; ?></td>
                                                <td><?php echo $data['views']; ?></td>
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

                        <!-- Display response -->
                        <!--div id="responseData"></div-->
                    </div>

                <?php else: ?>
                    Not authorized. 
                    <a href='<?php echo isset($authUrl) ? $authUrl : '#'; ?>'>Authorize with YouTube...</a>
                <?php endif; ?>
            </p>
        </div>



<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        // Handle Week button click
        $('#weekButton').click(function() {
            $.ajax({
                url: 'popular/index', // Ganti dengan nama controller Anda
                type: 'POST',
                data: { period: 'week' },
                success: function(response) {
                    //var data = JSON.parse(response);
                    $('#responseData').html(response);
                },
                error: function() {
                    $('#responseData').html('<p>Error retrieving data</p>');
                }
            });
        });

        // Handle Month button click
        $('#monthButton').click(function() {
            $.ajax({
                url: 'popular/index', // Ganti dengan nama controller Anda
                type: 'POST',
                data: { period: 'month' },
                success: function(response) {
                    //var data = JSON.parse(response);
                    $('#responseData').html(response);
                        //'<p>Period: ' + data.period + '</p>' +
                        //'<p>Start Date: ' + data.startDate + '</p>' +
                        //'<p>End Date: ' + data.endDate + '</p>'
                },
                error: function() {
                    $('#responseData').html('<p>Error retrieving data</p>');
                }
            });
        });
    });

    function selectChannel(channelName, period) {
        document.getElementById('periodInput').value = period;

        // Change the dropdown button text to the selected channelName
        document.getElementById('dropdownButton').textContent = channelName;
    }

    function onClick(period) {
        document.getElementById('periodInput').value = period;
    }

</script>
</body>
</html>