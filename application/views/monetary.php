<?php
function formatNumber($number) {
    if ($number >= 1000000) {
        return number_format($number / 1000000, 2) . ' jt';
    } elseif ($number >= 1000) {
        return number_format($number / 1000, 2) . ' rb';
    } else {
        return number_format($number);
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Youtube API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  </head>
  <style>
    /*.card-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-height: 3em; /* Adjust based on your design 
    }*/

    .card-body {
        display: flex;
        flex-direction: column;
    }

    .card-text {
        margin-top: auto;
    }

    .card {
        height: 700px;
    }

    /* Default Light Mode Styles */
    body {
        background-color: #ffffff;
        color: #000000;
    }

    .card {
        background-color: #f8f9fa;
        color: #000000;
    }

    .text-decoration-none {
        color: #000000; /* default link color for light mode */
    }

    /* Dark Mode Styles */
    body.dark-mode {
        background-color: #212529;
        color: #ffffff;
    }

    .card.dark-mode {
        background-color: #343a40;
        color: #ffffff;
    }

    .text-decoration-none.dark-mode {
        color: #ffffff; /* link color for dark mode */
    }

    /* Positioning for the toggle button */
    #toggleMode {
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 1000; /* Ensures it's above other elements */
    }
  </style>

  <body>

<div class="container py-5">
    <h1>YouTube Authorization Demo</h1>
    <p>
        <strong>Status:</strong>
        <?php if(isset($connected) && $connected): ?>
            Authorized. <a href='<?php echo base_url('monetary/disconnect'); ?>'>Disconnect</a>

            <!-- Search Input -->
            <div class="container my-3">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search videos...">
                    <span class="input-group-text" id="cancelSearch" style="cursor: pointer; display: none;">
                        <i class="bi bi-x"></i>
                    </span>
                </div>
            </div>

            <button id="toggleMode" class="btn btn-secondary">Toggle Dark Mode</button>

            <?php if(isset($connected) && $connected): ?>
            <div class="container py-5">
            <div class="text-center">
                <h1>List Video</h1>
                <p class="text-center">
                    <a href="<?php echo $channelUrl; ?>" class="text-decoration-none">
                        <i class="bi bi-globe"></i> <?php echo $channelUrl; ?>
                    </a>
                </p>
                <p class="text-center"><i class="bi bi-person"></i> <?php echo formatNumber($subscriberCount); ?> subscriber</p>
                <p class="text-center"><i class="bi bi-file-play"></i> <?php echo number_format($totalResults, 0, ',', '.'); ?> video</p>
                <p class="text-center"><i class="bi bi-file-play"></i> <?php echo number_format($totalVideos, 0, ',', '.'); ?> video in page</p>
                <p class="text-center"><i class="bi bi-graph-up-arrow"></i> <?php echo number_format($totalViewCount, 0, ',', '.'); ?> x ditonton</p>
                <p class="text-center"><i class="bi bi-info-circle"></i> <?php echo $joinedDate; ?></p>
                <p class="text-center"><i class="bi bi-globe-americas"></i> <?php echo $regionCode; ?></p>

				<form id="filterForm" method="post" action="<?php echo base_url('monetary'); ?>">
                    <div class="row justify-content-center align-items-center">
                        <div class="col-auto">
                        <div class="dropdown">
                          <button id="dropdownButton" class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Select
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" onclick="selectChannel('Fredo', 'UCQ7dUY53AOGGTYl_Myiurlw')">Fredo</a></li>
                            <li><a class="dropdown-item" onclick="selectChannel('XINN', 'UCDfcUKYZOjC0G-5uM0UwYTg')">XINN</a></li>
                            <li><a class="dropdown-item" onclick="selectChannel('Jess No Limit', 'UCvh1at6xpV1ytYOAzxmqUsA')">Jess No Limit</a></li>
                            <li><a class="dropdown-item" onclick="selectChannel('Susan B', 'UCLyz8iEvzxyhKEBzOTs6bJQ')">Susan B</a></li>
                            <li><a class="dropdown-item" onclick="selectChannel('MiawAug', 'UC3J4Q1grz46bdJ7NJLd4DGw')">MiawAug</a></li>
                            <li><a class="dropdown-item" onclick="selectChannel('Indo Programmer', 'UCucLj6AYL67BUTpfR6Um5fw')">Indo Programmer</a></li>
                            <li><a class="dropdown-item" onclick="selectChannel('Impact Music Indonesia', 'UCrLp5XWCXQHJnmK8P36KTsQ')">Impact Music Indonesia</a></li>
							<li><a class="dropdown-item" onclick="selectChannel('R-7', 'UCTg0Ue7PAI8ep3_ITQMqQcA')">R-7</a></li>
                          </ul>
                        <!-- Hidden input to store selected channel id -->
                                <input type="hidden" name="channelId" id="channelIdInput" value="UCQ7dUY53AOGGTYl_Myiurlw">
                        </div>
                    </div>
					<div class="col-auto">
                        <div class="dropdown">
                          <button id="dropdownButton1" class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Select
                          </button>
                          <ul class="dropdown-menu">
						  <li><a class="dropdown-item" onclick="selectChannel1('Day', 'Day')">Day</a></li>
                            <li><a class="dropdown-item" onclick="selectChannel1('Week', 'Week')">Week</a></li>
							<li><a class="dropdown-item" onclick="selectChannel1('Month', 'Month')">Month</a></li>
							<li><a class="dropdown-item" onclick="selectChannel1('3 Month', '3 Month')">3 Month</a></li>
							<li><a class="dropdown-item" onclick="selectChannel1('Year', 'Year')">Year</a></li>
							<li><a class="dropdown-item" onclick="selectChannel1('Custom Date', 'Custom Date')">Custom Date</a></li>
							<li><a class="dropdown-item" onclick="selectChannel1('Popular', 'Popular')">Popular</a></li>
                          </ul>
                        <!-- Hidden input to store selected channel id -->
                                <input type="hidden" name="period" id="periodInput" value="Day">
                        </div>
                    </div>
					<div id="customDateInputs" style="display: none;">
						<label for="">Published Before</label>
						<input type="datetime-local" class="form-control" name="publishedBefore" id="published_before">
						<label for="">Published After</label>
						<input type="datetime-local" class="form-control" name="publishedAfter" id="published_after">
					</div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                    </div>
                </form>
            </div>
             <div class="row mt-5" id="video-container">
                <?php if(isset($videos) && !empty($videos)): ?>
                    <?php foreach ($videos as $video): ?>
                        <div class="col-md-4 align-items-stretch">
                            <a href="https://www.youtube.com/watch?v=<?php echo htmlspecialchars($video['videoId'], ENT_QUOTES, 'UTF-8'); ?>" class="text-decoration-none" target="_blank">
                                <div class="card mb-4">
                                    <img src="<?php echo htmlspecialchars($video['thumbnail'], ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="card-body d-flex flex-column">
										<h5 class="card-title"><?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                        <p class="card-text mt-auto"><i class="bi bi-eye-fill"></i> Views: <?php echo ($video['totalVideoViews']); ?></p>
                                        <p class="card-text"><i class="bi bi-hand-thumbs-up-fill"></i> Likes: <?php echo ($video['totalVideoLikes']); ?></p>
                                        <p class="card-text"><i class="bi bi-hand-thumbs-down-fill"></i> Dislikes: <?php echo ($video['totalVideoDislikes']); ?></p>
                                        <p class="card-text"><i class="bi bi-chat-left-text-fill"></i> Comments: <?php echo ($video['totalVideoComments']); ?></p>
                                        <p class="card-text"><i class="bi bi-clock-fill"></i> Duration: <?php echo ($video['duration']); ?></p>
                                        <p class="card-text"><i class="bi bi-person-fill"></i> Genre: <?php echo ($video['genre']); ?></p>
                                        <p class="card-text"><i class="bi bi-person-fill"></i> Upload Date: <?php echo ($video['uploadDate']); ?></p>
                                        <p class="card-text"><i class="bi bi-cash"></i> Estimated Revenue: Rp. <?php echo ($video['estimatedRevenueIDR']); ?></p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-md-12">
                        <div class="alert alert-warning" role="alert">
                            No videos found.
                        </div>
                    </div>
                <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Pagination controls -->
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center" id="pagination-controls">
                    <!-- Pagination buttons will be injected here by JavaScript -->
                </ul>
            </nav>

            <div class="container py-5">
                <button id="getMonetizationData" class="btn btn-primary">Get Monetization Data</button>
                <div id="monetizationData"></div>
            </div>

            <!--div>
                < ?php echo anchor('/editvideo/', '<div class="btn btn-success">Edit Video</div>'); ?>
            </div-->

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
      $(document).ready(function(){
        $('#getMonetizationData').click(function(){
          $.ajax({
            url: '<?php echo base_url('monetary/monetization_data'); ?>',
            method: 'GET',
            success: function(data) {
              if(data.error) {
                $('#monetizationData').html('<p>Error: ' + data.error + '</p>');
              } else {
                var output = '<h3>Monetization Data</h3><ul>';
                $.each(data, function(index, row) {
                  output += '<li>Date: ' + row[0] + ', Revenue: $' + row[1] + ', Impressions: ' + row[2] + ', Monetized Playbacks: ' + row[3] + '</li>';
                });
                output += '</ul>';
                $('#monetizationData').html(output);
              }
            },
            error: function(error) {
              $('#monetizationData').html('<p>An error occurred while fetching the data.</p>');
            }
          });
        });
      });

    function selectChannel(channelName, channelId) {
        // Set the hidden input value to the selected channelId
        document.getElementById('channelIdInput').value = channelId;

        // Change the dropdown button text to the selected channelName
        document.getElementById('dropdownButton').textContent = channelName;
    }

	function selectChannel1(channelName1, period) {
		document.getElementById('periodInput').value = period;

		// Change the dropdown button text to the selected channelName
        document.getElementById('dropdownButton1').textContent = channelName1;

		// Show/hide the custom date inputs based on selection
		if (period === 'Custom Date') {
			document.getElementById('customDateInputs').style.display = 'block';
		} else {
			document.getElementById('customDateInputs').style.display = 'none';
		}
	}

	document.querySelector('#filterForm').addEventListener('submit', function(event) {
		var beforeInput = document.getElementById('published_before').value;
		var afterInput = document.getElementById('published_after').value;

		function toISOStringWithoutMs(date) {
			return date.toISOString().split('.')[0] + 'Z'; // Removing milliseconds
		}

		var beforeISO = '';
		var afterISO = '';

		if (beforeInput) {
			var beforeDate = new Date(beforeInput);
			if (isNaN(beforeDate.getTime())) {
				console.error("Invalid 'published_before' date:", beforeInput);
			} else {
				beforeISO = toISOStringWithoutMs(beforeDate);
				console.log("Formatted 'published_before' (ISO):", beforeISO);
			}
		}

		if (afterInput) {
			var afterDate = new Date(afterInput);
			if (isNaN(afterDate.getTime())) {
				console.error("Invalid 'published_after' date:", afterInput);
			} else {
				afterISO = toISOStringWithoutMs(afterDate);
				console.log("Formatted 'published_after' (ISO):", afterISO);
			}
		}

		// Debug final values before API call
		/*
		console.log("API Request Data:", {
			published_before: beforeISO,
			published_after: afterISO
		});
		*/
	});
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var toggleButton = document.getElementById('toggleMode');
    var body = document.body;
    var cards = document.querySelectorAll('.card');
    var links = document.querySelectorAll('.text-decoration-none');
    
    // Load the user's preference from local storage
    if (localStorage.getItem('darkMode') === 'enabled') {
      body.classList.add('dark-mode');
      cards.forEach(card => card.classList.add('dark-mode'));
      links.forEach(link => link.classList.add('dark-mode'));
    }

    toggleButton.addEventListener('click', function() {
      if (body.classList.contains('dark-mode')) {
        // Switch to light mode
        body.classList.remove('dark-mode');
        cards.forEach(card => card.classList.remove('dark-mode'));
        links.forEach(link => link.classList.remove('dark-mode'));
        localStorage.setItem('darkMode', 'disabled');
      } else {
        // Switch to dark mode
        body.classList.add('dark-mode');
        cards.forEach(card => card.classList.add('dark-mode'));
        links.forEach(link => link.classList.add('dark-mode'));
        localStorage.setItem('darkMode', 'enabled');
      }
    });
  });
</script>

<script>
    var videos = <?php echo json_encode($videos); ?>;
    var videosPerPage = 10;
    var currentPage = 1;
    var filteredVideos = videos;

    function renderVideos() {
        var videoContainer = document.getElementById('video-container');
        videoContainer.innerHTML = ''; // Clear previous videos

        var start = (currentPage - 1) * videosPerPage;
        var end = start + videosPerPage;
        var paginatedVideos = filteredVideos.slice(start, end);

        paginatedVideos.forEach(video => {
            var videoCard = `
                <div class="col-md-4 align-items-stretch">
                    <a href="https://www.youtube.com/watch?v=${video.videoId}" class="text-decoration-none" target="_blank">
                        <div class="card mb-4">
                            <img src="${video.thumbnail}" class="card-img-top" alt="${video.title}">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">${video.title}</h5>
                                <p class="card-text mt-auto"><i class="bi bi-eye-fill"></i> Views: ${video.totalVideoViews}</p>
                                <p class="card-text"><i class="bi bi-hand-thumbs-up-fill"></i> Likes: ${video.totalVideoLikes}</p>
                                <p class="card-text"><i class="bi bi-hand-thumbs-down-fill"></i> Dislikes: ${video.totalVideoDislikes}</p>
                                <p class="card-text"><i class="bi bi-chat-left-text-fill"></i> Comments: ${video.totalVideoComments}</p>
                                <p class="card-text"><i class="bi bi-clock-fill"></i> Duration: ${video.duration}</p>
                                <p class="card-text"><i class="bi bi-person-fill"></i> Genre: ${video.genre}</p>
                                <p class="card-text"><i class="bi bi-person-fill"></i> Upload Date: ${video.uploadDate}</p>
                                <p class="card-text"><i class="bi bi-cash"></i> Estimated Revenue: Rp. ${video.estimatedRevenueIDR}</p>
                            </div>
                        </div>
                    </a>
                </div>
            `;
            videoContainer.insertAdjacentHTML('beforeend', videoCard);
        });
    }

    function renderPaginationControls() {
        var paginationControls = document.getElementById('pagination-controls');
        paginationControls.innerHTML = ''; // Clear previous controls

        var totalPages = Math.ceil(filteredVideos.length / videosPerPage);

        // Previous Button
        var prevButton = `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${currentPage - 1})">Previous</a>
            </li>
        `;
        paginationControls.insertAdjacentHTML('beforeend', prevButton);

        // Page Number Buttons
        for (let i = 1; i <= totalPages; i++) {
            var paginationButton = `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
                </li>
            `;
            paginationControls.insertAdjacentHTML('beforeend', paginationButton);
        }

        // Next Button
        var nextButton = `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${currentPage + 1})">Next</a>
            </li>
        `;
        paginationControls.insertAdjacentHTML('beforeend', nextButton);
    }

    function goToPage(page) {
        currentPage = page;
        renderVideos();
        renderPaginationControls();
    }

    function cancelSearch() {
        $('#searchInput').val(''); // Clear the search input
        filteredVideos = videos; // Reset to all videos
        $('#cancelSearch').hide(); // Hide the cancel button
        currentPage = 1; // Reset to the first page
        renderVideos();
        renderPaginationControls();
    }

    $('#searchInput').on('input', function() {
        var searchQuery = $(this).val().toLowerCase();
        if (searchQuery) {
            filteredVideos = videos.filter(video => video.title.toLowerCase().includes(searchQuery));
            $('#cancelSearch').show(); // Show the cancel button
        } else {
            filteredVideos = videos; // Reset to all videos
            $('#cancelSearch').hide(); // Hide the cancel button
        }
        currentPage = 1; // Reset to the first page
        renderVideos();
        renderPaginationControls();
    });

    $('#cancelSearch').on('click', function() {
        cancelSearch();
    });

    // Initial render
    renderVideos();
    renderPaginationControls();
</script>
</body>
</html>
