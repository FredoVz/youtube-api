<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//require_once 'vendor/autoload.php';
require_once APPPATH . 'libraries/google-api-php-client--PHP7.4/vendor/autoload.php';

use Google\Client;
use Google\Service\YouTube;
use Google\Service\YouTubeAnalytics;
use Google\Service\YoutubeAnalyticsMonetary;

class Traffic_Source extends CI_Controller {

    private $client;
	//public $client;

    public function __construct() {
        parent::__construct();
		
        $this->load->library('session');

		date_default_timezone_set('Asia/Jakarta');
		/*
		
        // Initialize Google Client
        $this->client = new Client();
        $this->client->setApplicationName('Demo Youtube API');
        //$this->client->setAuthConfig('application/views/youtube.json');
		//$this->client->setAuthConfig('application/views/youtube1.json');
		$this->client->setAuthConfig('application/views/youtube2.json');
		$this->client->setRedirectUri(base_url('traffic_source/callback')); // Set your redirect URI

        //$this->client->setDeveloperKey('AIzaSyCxD1Fi6QaAfM4uFKuxnl_0NhWYZo1iAoU');
		//$this->client->setDeveloperKey('AIzaSyA3qC5AVp5hj-2nTmr3Xn0QA5QjL6WQ21k');
		//$this->client->setDeveloperKey('AIzaSyAc26uFtnQx9XdikSFcS1neeHXW6x5-snQ');
		//$this->client->setDeveloperKey('AIzaSyANms8d6waFFOBUiW7dYZd2xmrH_MIwFjc');
        //$this->client->setDeveloperKey('AIzaSyAo5imRlTEQsgDiYkCygOnvgqxbeixMxy4');
		//$this->client->setDeveloperKey('AIzaSyAqO6tcrvuq5udAuS7W6jAh5LTaNHEIvP4');
        $this->client->addScope('https://www.googleapis.com/auth/youtube.readonly');
        $this->client->addScope('https://www.googleapis.com/auth/yt-analytics.readonly');
        $this->client->addScope('https://www.googleapis.com/auth/yt-analytics-monetary.readonly');
		*/
    }

    public function index()
	{	
		$this->client = new Client();
        $this->client->setApplicationName('Demo Youtube API');

		//$this->client->setAuthConfig('application/views/youtube2.json');
		//$this->client->setAuthConfig('application/views/youtube.json');
		//$this->client->setAuthConfig('application/views/youtube1.json');
		$this->client->setAuthConfig('application/views/youtube2.json');
        //$this->client->setRedirectUri(base_url('traffic_source/callback')); // Set your redirect URI
		$this->client->setRedirectUri(base_url('traffic_source')); // Set your redirect URI
		$this->client->addScope('https://www.googleapis.com/auth/youtube.readonly');
        $this->client->addScope('https://www.googleapis.com/auth/yt-analytics.readonly');
        $this->client->addScope('https://www.googleapis.com/auth/yt-analytics-monetary.readonly');

		

		// Check if the user is being redirected back from Google with an authorization code
		if ($this->input->get('code')) {
			try {
				// Exchange the authorization code for an access token
				$token = $this->client->fetchAccessTokenWithAuthCode($this->input->get('code'), $this->session->userdata('code_verifier'));
				$this->client->setAccessToken($token);
				$this->session->set_userdata('google_oauth_token', $token);
				// Redirect to the index method after connecting
				redirect(base_url('traffic_source'));
			} catch (Exception $e) {
				// Handle the exception and log it
				log_message('error', 'OAuth callback exception: ' . $e->getMessage());
				echo "Exception: " . $e->getMessage();
			}
			return; // Stop further execution
		}
	
		// Check if the user wants to disconnect
		if ($this->input->get('action') === 'disconnect') {
			// Clear the session data
			$this->session->unset_userdata('google_oauth_token');
			$this->session->unset_userdata('code_verifier');
	
			// Redirect to the index method after disconnecting
			redirect(base_url('traffic_source'));
			return; // Stop further execution
		}
			
		

        $data['connected'] = false;
        $data['authUrl'] = '';
        $data['videos'] = [];

        // Check if the user is already authenticated
        if (!isset($_GET['code']) && empty($this->session->userdata('google_oauth_token'))) {
            // Generate code verifier for PKCE (Proof Key for Code Exchange)
            $codeVerifier = $this->client->getOAuth2Service()->generateCodeVerifier();
            $this->session->set_userdata('code_verifier', $codeVerifier);

            // Get the URL to Google's OAuth server to initiate the authentication process
            $authUrl = $this->client->createAuthUrl();
            $data['authUrl'] = $authUrl;

        } elseif (!empty($this->session->userdata('google_oauth_token'))) {
            // Use the existing access token
            $this->client->setAccessToken($this->session->userdata('google_oauth_token'));

            // Check if the access token has expired
            if ($this->client->isAccessTokenExpired()) {
                // Unset the session token if expired
                $this->session->unset_userdata('google_oauth_token');
                $data['connected'] = false;
                $authUrl = $this->client->createAuthUrl(); // Provide the auth URL again
                $data['authUrl'] = $authUrl;
                    redirect(base_url('traffic_source'));
            } else {
                $data['connected'] = true;

                //BEDA FUNGSI
                try {
                    // Inisialisasi YouTube Data API service
					if ($channelId = $this->input->post('channelId')) {
						//$channelId = "UCQ7dUY53AOGGTYl_Myiurlw";
						$channelId = $this->input->post('channelId');
						//echo "Selected Channel ID: " . $channelId;
					}

					else {
						$channelId = "UCQ7dUY53AOGGTYl_Myiurlw";
					}
					
					$youtube = new YouTube($this->client);

					// Ambil ID playlist unggahan    
					$channelsResponse = $youtube->channels->listChannels('contentDetails,statistics,snippet', [
						'id' => $channelId,
					]);

					// Check if response is valid and contains items
					if (!$channelsResponse || !$channelsResponse->getitems()) {
						throw new Exception("Failed to retrieve channel details or channel not found.");
					}

					$contentDetails = $channelsResponse->getitems()[0]->getcontentDetails();
					$statistics = $channelsResponse->getitems()[0]->getstatistics();
					$channelSnippet = $channelsResponse->getitems()[0]->getsnippet();
					if (!$contentDetails || !$statistics || !$channelSnippet) {
						throw new Exception("Failed to retrieve content details.");
					}

					// Get the subscriber count
					$subscriberCount = $statistics->getsubscriberCount();
					$totalViewCount = $statistics->getviewCount(); // This is the total number of views for the channel

					// Get the "Joined Date"
					//$publishedAt = $channelSnippet->getPublishedAt();
					$publishedAt = $channelSnippet->getpublishedAt();
					$joinedDate = new DateTime($publishedAt);
					$formattedJoinedDate = $joinedDate->format('d M Y');
					$formattedJoinedDate = "Bergabung pada " . $formattedJoinedDate;
					

					// Get the region code
					$regionCode = $channelSnippet->getcountry(); // Fetch the country code if available

					//https://www.googleapis.com/youtube/v3/i18nRegions?part=snippet&key=AIzaSyAqO6tcrvuq5udAuS7W6jAh5LTaNHEIvP4
					// Define the mapping array
					$regionMapping = [
						'AE' => 'United Arab Emirates',
						'BH' => 'Bahrain',
						'DZ' => 'Algeria',
						'EG' => 'Egypt',
						'IQ' => 'Iraq',
						'JO' => 'Jordan',
						'KW' => 'Kuwait',
						'LB' => 'Lebanon',
						'LY' => 'Libya',
						'MA' => 'Morocco',
						'OM' => 'Oman',
						'QA' => 'Qatar',
						'SA' => 'Saudi Arabia',
						'TN' => 'Tunisia',
						'YE' => 'Yemen',
						'AZ' => 'Azerbaijan',
						'BY' => 'Belarus',
						'BG' => 'Bulgaria',
						'BD' => 'Bangladesh',
						'BA' => 'Bosnia and Herzegovina',
						'CZ' => 'Czechia',
						'DK' => 'Denmark',
						'AT' => 'Austria',
						'CH' => 'Switzerland',
						'DE' => 'Germany',
						'GR' => 'Greece',
						'AU' => 'Australia',
						'BE' => 'Belgium',
						'CA' => 'Canada',
						'GB' => 'United Kingdom',
						'GH' => 'Ghana',
						'IE' => 'Ireland',
						'IL' => 'Israel',
						'IN' => 'India',
						'JM' => 'Jamaica',
						'KE' => 'Kenya',
						'MT' => 'Malta',
						'NG' => 'Nigeria',
						'NZ' => 'New Zealand',
						'SG' => 'Singapore',
						'UG' => 'Uganda',
						'US' => 'United States',
						'ZA' => 'South Africa',
						'ZW' => 'Zimbabwe',
						'AR' => 'Argentina',
						'BO' => 'Bolivia',
						'CL' => 'Chile',
						'CO' => 'Colombia',
						'CR' => 'Costa Rica',
						'DO' => 'Dominican Republic',
						'EC' => 'Ecuador',
						'ES' => 'Spain',
						'GT' => 'Guatemala',
						'HN' => 'Honduras',
						'MX' => 'Mexico',
						'NI' => 'Nicaragua',
						'PA' => 'Panama',
						'PE' => 'Peru',
						'PR' => 'Puerto Rico',
						'PY' => 'Paraguay',
						'SV' => 'El Salvador',
						'UY' => 'Uruguay',
						'VE' => 'Venezuela',
						'EE' => 'Estonia',
						'FI' => 'Finland',
						'PH' => 'Philippines',
						'FR' => 'France',
						'SN' => 'Senegal',
						'HR' => 'Croatia',
						'HU' => 'Hungary',
						'ID' => 'Indonesia',
						'IS' => 'Iceland',
						'IT' => 'Italy',
						'JP' => 'Japan',
						'GE' => 'Georgia',
						'KZ' => 'Kazakhstan',
						'KR' => 'South Korea',
						'LU' => 'Luxembourg',
						'LA' => 'Laos',
						'LT' => 'Lithuania',
						'LV' => 'Latvia',
						'MK' => 'North Macedonia',
						'MY' => 'Malaysia',
						'NO' => 'Norway',
						'NP' => 'Nepal',
						'NL' => 'Netherlands',
						'PL' => 'Poland',
						'BR' => 'Brazil',
						'PT' => 'Portugal',
						'MD' => 'Moldova',
						'RO' => 'Romania',
						'RU' => 'Russia',
						'LK' => 'Sri Lanka',
						'SK' => 'Slovakia',
						'SI' => 'Slovenia',
						'ME' => 'Montenegro',
						'RS' => 'Serbia',
						'SE' => 'Sweden',
						'TZ' => 'Tanzania',
						'TH' => 'Thailand',
						'TR' => 'Turkey',
						'UA' => 'Ukraine',
						'PK' => 'Pakistan',
						'VN' => 'Vietnam',
						'HK' => 'Hong Kong',
						'TW' => 'Taiwan',
						'CY' => 'Cyprus',
						'KH' => 'Cambodia',
						'LI' => 'Liechtenstein',
						'PG' => 'Papua New Guinea',
						'CN' => 'China',

						// Tambahkan lebih banyak kode negara dan nama sesuai kebutuhan
					];

					// Get the friendly name from the mapping
					$formattedRegionCode = isset($regionMapping[$regionCode]) ? $regionMapping[$regionCode] : 'Unknown Region';

					$channelUrl = "https://www.youtube.com/channel/" . $channelId;

					$uploadsPlaylistId = $contentDetails->getRelatedPlaylists()->getUploads();

					// Get the current page token from the request
					$pageToken = $this->input->get('pageToken') ?: '';
					$perPage = 50; // Videos per page

					// Ambil video dari playlist unggahan
					$playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', [
						'playlistId' => $uploadsPlaylistId,
						'maxResults' => $perPage,
						'pageToken' => $pageToken,
					]);

					// Get the total number of results from the playlistItemsResponse
					//$totalResults = $channelsResponse->getPageInfo()->getTotalResults();
					$totalResults = $playlistItemsResponse->getpageInfo()->gettotalResults();

					// Inisialisasi YouTube Analytics API service
					$analytics = new YoutubeAnalytics($this->client);

					// Set the current date and time for publishedBefore
					//$publishedBefore = date('Y-m-d\TH:i:s\Z', time());
					//$date = new DateTime();
					//$publishedBefore = $date->format(DateTime::ATOM);
					$publishedBefore = date('Y-m-d\TH:i:s\Z');
					$order ='';

					//$period = '2024-08-10T00:00:00Z';
					// Inisialisasi YouTube Data API service
					if ($period = $this->input->post('period')) {
						//$period = 'Day';
						$period = $this->input->post('period');
						//echo "Selected Period: " . $period;
					}

					else {
						$period = 'Day';
					}

					// Calculate the publishedAfter date based on the selected period
					if ($period === 'All') {
						$publishedAfter = date('Y-m-d\TH:i:s\Z', strtotime($publishedAt)); // This uses the channel creation date
						$order = 'date'; // Order by popularity
					} elseif ($period === 'Day') {
						$publishedAfter = date('Y-m-d\TH:i:s\Z', strtotime('today'));
						$order = 'date';
						//$publishedAfter = date('Y-m-d\TH:i:s\Z');
					} elseif ($period === 'Week') {
						$publishedAfter = date('Y-m-d\TH:i:s\Z', strtotime('last sunday'));
						$order = 'date';
					} elseif ($period === 'Month') {
						//$publishedAfter = date('Y-m-d\TH:i:s\Z', strtotime('-1 month'));
						$publishedAfter = date('Y-m-01\T00:00:00\Z');
						$order = 'date';
					} elseif ($period === '3 Month') {
						$publishedAfter = date('Y-m-d\TH:i:s\Z', strtotime('-3 months', strtotime(date('Y-m-01')))); // Start of the month 3 months ago
						$order = 'date';
					} elseif ($period === 'Year') {
						$publishedAfter = date('Y-m-d\TH:i:s\Z', strtotime(date('Y') . '-01-01')); // Start of the current year
						$order = 'date';
					} elseif ($period === 'Custom Date') {
						// Get the input values
						$publishedBefore = $this->input->post('publishedBefore');
						$publishedAfter = $this->input->post('publishedAfter');

						// Convert to DateTime objects
						$publishedBeforeDate = new DateTime($publishedBefore);
						$publishedAfterDate = new DateTime($publishedAfter);

						// Format the dates as RFC 3339
						$formattedPublishedBefore = $publishedBeforeDate->format(DateTime::ATOM); // ISO 8601 with timezone
						$formattedPublishedAfter = $publishedAfterDate->format(DateTime::ATOM);  // ISO 8601 with timezone

						// Assign formatted dates to the variables for API usage
						$publishedBefore = $formattedPublishedBefore;
						$publishedAfter = $formattedPublishedAfter;
						$order = 'date';
					} else if ($period === "Popular") {
						// Sort by popularity
						// Set `publishedAfter` to the date when the channel was first created
						$publishedAfter = $joinedDate->format('Y-m-d\TH:i:s\Z'); // This uses the channel creation date
						$order = 'viewCount'; // Order by popularity
					} else {
						throw new Exception('Invalid period selected');
					}

					// Iterasi video untuk ambil data monetisasi
					$videos = [];
					$totalAllRevenue = 0;
					$totalAllDuration = 0;
					$videoCount = 0; // Inisialisasi penghitung video
					$maxResults = 5; // Desired number of valid videos
					$filteredVideos = []; // Filter out Shorts from the results
					$nextPageToken = null; // Initialize the page token
					$analyticsResponse = "";
					$analyticsResponse1 = "";
					$analyticsResponse2 = "";
					$analyticsResponse3 = "";
					$no = 1;

					$analyticsResponse1 = $analytics->reports->query([
						'ids' => 'channel==MINE',
						'startDate' => "2017-01-01",
						'endDate' => "2024-01-01",
						'metrics' => 'views',
						//'filters' => 'video==' . $videoId,
						'dimensions' => 'country',
						//'maxResults' => $maxResults,
					]);


					do {
						// Add searchResponse for filtering videos
						$searchResponse = $youtube->search->listSearch('snippet', [
							'channelId' => $channelId,
							'order' => $order,
							'publishedBefore' => $publishedBefore,
							'publishedAfter' => $publishedAfter, // Set your desired date
							'maxResults' => $maxResults, // Fetch up to 50 results per page
							'pageToken' => $nextPageToken, // Use page token for pagination
							'type' => 'video,channel',
						]);

						foreach ($searchResponse->getitems() as $item) {
							$videoId = $item->getid()->getvideoId();
							$title = $item->getsnippet()->gettitle();
							$thumbnailUrl = $item->getsnippet()->getthumbnails()->gethigh()->geturl();
							$publishedAt = $item->getsnippet()->getpublishedAt(); // Get upload date from snippet
							$uploadDate = new DateTime($publishedAt);
							$formattedUploadDate = $uploadDate->format('d M Y');

							// Fetch video statistics and details
							$videoResponse = $youtube->videos->listVideos('snippet,statistics,contentDetails,liveStreamingDetails', [
								'id' => $videoId,
							]);

                            // Convert datetime-local format to YYYY-MM-DD format
                            $startDate = '';
							$endDate = '';

                            // Validate dates to ensure they are in the correct format and logical
                            if (!$startDate || !$endDate || $startDate > $endDate) {
                                // Handle invalid dates here
                                $startDate = date('Y-m-d', strtotime($publishedAt));
                                $endDate = date('Y-m-d');  // Default end date
                            }

                            $analyticsResponse = $analytics->reports->query([
                                'ids' => 'channel==MINE',
                                'startDate' => $startDate,
                                'endDate' => $endDate,
                                'metrics' => 'views,estimatedMinutesWatched,averageViewDuration',
                                //'metrics' => 'views,averageViewDuration,watchTime,impressions,impressionClickThroughRate',
                                'dimensions' => 'insightTrafficSourceType',
                                'filters' => 'video==' . $videoId,
                                //'sort' => '-views',
                            ]);

                            $viewSources = [];
							foreach ($analyticsResponse->getRows() as $row) {
							    $source = $row[0]; // Insight Traffic Source Type
							    $views = $row[1]; // View count
							    $estimatedHoursWatched = $row[2] / 60; // Estimated Watch Time
							    $averageViewDuration = $row[3]; // Average View Duration

							    switch ($source) {
							        case 'TOTAL':
							            $viewSources['total'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							        case 'PLAYLIST':
							            $viewSources['playlist'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							        case 'YT_SEARCH':
							            $viewSources['youtube_search'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							        case 'SUGGESTED_VIDEO':
							            $viewSources['suggested_videos'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							        case 'BROWSE':
							            $viewSources['browsed_features'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							        case 'EXT_URL':
							            $viewSources['external'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							        // default:
							        //     $viewSources[$source] = [
							        //         'views' => $views,
							        //         'averageViewDuration' => $averageViewDuration,
							        //     ];
							    }
							}

							// Get views by country using YouTube Analytics API
							// Mendapatkan jumlah views berdasarkan negara menggunakan YouTube Analytics API
							$analyticsResponse1 = $analytics->reports->query([
								'ids' => 'channel==MINE',
								'startDate' => $startDate,
								'endDate' => $endDate,
								'metrics' => 'views,estimatedMinutesWatched,averageViewDuration',
								'filters' => 'video==' . $videoId,
								'dimensions' => 'country',
								//'maxResults' => $maxResults,
							]);

							$viewSources1 = [];
							foreach ($analyticsResponse1['rows'] as $row) {
								$source = $row[0]; // Geography
							    $views = $row[1]; // View count
							    $estimatedHoursWatched = $row[2] / 60; // Estimated Watch Time
							    $averageViewDuration = $row[3]; // Average View Duration
							
								switch ($source) {
									case 'ID':
										$viewSources1['indonesia'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
										break;
									case 'MY':
										$viewSources1['malaysia'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
										break;
									case 'US':
										$viewSources1['united_states'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
										break;
									case 'AU':
										$viewSources1['australia'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
										break;
									case 'SG':
										$viewSources1['singapore'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
										break;
								}
							}
							
							$analyticsResponse2 = $analytics->reports->query([
								'ids' => 'channel==MINE',
								'startDate' => $startDate,
								'endDate' => $endDate,
								'metrics' => 'viewerPercentage',
								'dimensions' => 'ageGroup',
								//'dimensions' => 'ageGroup,gender',
								'filters' => 'video==' . $videoId,
								//'maxResults' => $maxResults,
							]);

							$viewSources2 = [];
							foreach ($analyticsResponse->getRows() as $row) {
							    $source = $row[0]; // Age Group
							    $views = $row[1]; // View count
							    //$averageViewDuration = $row[2]; // Average View Duration

							    switch ($source) {
									case 'age13-17':
										$viewSources2['13-17years'] = [
							                'views' => $views,
							                //'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
						            case 'age18-24':
										$viewSources2['18-24years'] = [
							                'views' => $views,
							                //'averageViewDuration' => $averageViewDuration,
							            ];
									break;
									case 'age25-34':
										$viewSources2['25-34years'] = [
							                'views' => $views,
							                //'averageViewDuration' => $averageViewDuration,
							            ];
									break;
									case 'age35-44':
										$viewSources2['35-44years'] = [
							                'views' => $views,
							                //'averageViewDuration' => $averageViewDuration,
							            ];
									break;
									case 'age45-54':
										$viewSources2['45-54years'] = [
							                'views' => $views,
							                //'averageViewDuration' => $averageViewDuration,
							            ];
									break;
									case 'age55-64':
										$viewSources2['55-64years'] = [
							                'views' => $views,
							                //'averageViewDuration' => $averageViewDuration,
							            ];
									break;
								}
							}
							
							$analyticsResponse3 = $analytics->reports->query([
                                'ids' => 'channel==MINE',
								'startDate' => $startDate,
								'endDate' => $endDate,
								'metrics' => 'views,estimatedMinutesWatched,averageViewDuration',
								'dimensions' => 'city',
								'maxResults' => 250,
								'sort' => '-views'
                            ]);

                            $viewSources3 = [
                            	/*
							    'indonesia' => [
							        'Jakarta' => ['views' => 0, 'estimatedHoursWatched' => 0, 'averageViewDuration' => 0],
							        'Bandung' => ['views' => 0, 'estimatedHoursWatched' => 0, 'averageViewDuration' => 0],
							        'Surabaya' => ['views' => 0, 'estimatedHoursWatched' => 0, 'averageViewDuration' => 0],
							        'Medan' => ['views' => 0, 'estimatedHoursWatched' => 0, 'averageViewDuration' => 0],
							        'Makassar' => ['views' => 0, 'estimatedHoursWatched' => 0, 'averageViewDuration' => 0]
							    ]
							    */
							];
                            foreach ($analyticsResponse3['rows'] as $row) {
							    $source = $row[0]; // City
							    $views = $row[1]; // View count
							    $estimatedHoursWatched = $row[2] / 60; // Estimated Watch Time
							    $averageViewDuration = $row[3]; // Average View Duration

							    switch ($source) {
							        case 'Jakarta':
							            $viewSources3['indonesia']['Jakarta'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							        case 'Bandung':
							            $viewSources3['indonesia']['Bandung'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							        case 'Surabaya':
							            $viewSources3['indonesia']['Surabaya'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							        case 'Medan':
							            $viewSources3['indonesia']['Medan'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							        case 'Makassar':
							            $viewSources3['indonesia']['Makassar'] = [
							                'views' => $views,
							                'estimatedHoursWatched' => number_format($estimatedHoursWatched, 2, ',', '.'),
							                'averageViewDuration' => $averageViewDuration,
							            ];
							            break;
							    }
							}

							/*
							echo '<pre>';

							echo '<pre>',$no++;
							echo '. Video Id : ',$videoId,'</pre>';
							//echo $startDate;
							//echo $endDate;
							echo '<pre>Traffic Source</pre>';
							echo '<pre>',json_encode($viewSources),'</pre>';

							echo '<pre>Geography</pre>';
							echo '<pre>',json_encode($viewSources1),'</pre>';

							echo '<pre>City</pre>';
							echo '<pre>',json_encode($viewSources3),'</pre>';

							echo '<pre>ageGroup</pre>';
							echo '<pre>',json_encode($viewSources2),'</pre>';

							echo '</pre>';
							*/
                            
							// Ensure video response exists
							if (empty($videoResponse['items'])) {
								continue; // Skip if no video data
							}

							$videoItem = $videoResponse['items'][0];

							// Skip live videos
							if (isset($videoItem['liveStreamingDetails'])) {
								continue;
							}

							// Convert video duration from ISO 8601 to seconds
							$videoDuration = $videoItem['contentDetails']['duration'];
							$interval = new DateInterval($videoDuration);
							$seconds = ($interval->h * 3600) + ($interval->i * 60) + $interval->s;

							// Exclude videos that are less than or equal to 60 seconds
							if ($seconds <= 60) {
								continue; // Skip short videos
							}

							$videoStats = $videoItem->getstatistics();
							$videoSnippet = $videoItem->getsnippet();
							$views = $videoStats->getviewCount();

							$views = (int)$views;

							$remainingViews = $views;
							/*
							$viewsAge1 = $views / 5;
							$viewsAge2 = $views / 5;
							$viewsAge3 = $views / 5;
							$viewsAge4 = $views / 5;
							$viewsAge5 = $views / 5;
							*/
							// Menghasilkan angka acak untuk viewsAge1 hingga viewsAge4
							$viewsAge1 = rand(1, $remainingViews - 4);
							$remainingViews -= $viewsAge1;

							$viewsAge2 = rand(1, $remainingViews - 3);
							$remainingViews -= $viewsAge2;

							$viewsAge3 = rand(1, $remainingViews - 2);
							$remainingViews -= $viewsAge3;

							$viewsAge4 = rand(1, $remainingViews - 1);
							$remainingViews -= $viewsAge4;

							// ViewsAge5 mendapatkan sisa views yang tersisa
							$viewsAge5 = $remainingViews;

							$percentageAge1 = ($viewsAge1 / $views) * 100;
							$percentageAge2 = ($viewsAge2 / $views) * 100;
							$percentageAge3 = ($viewsAge3 / $views) * 100;
							$percentageAge4 = ($viewsAge4 / $views) * 100;
							$percentageAge5 = ($viewsAge5 / $views) * 100;
							$percentageTotal = $percentageAge1 + $percentageAge2 + $percentageAge3 + $percentageAge4 + $percentageAge5;

							// Get category ID and map to genre
							$categoryId = $videoSnippet->getcategoryId();
							$genre = isset($this->categoryToGenre[$categoryId]) ? $this->categoryToGenre[$categoryId] : 'Unknown';

							// Format video duration
							$durationFormatted = $this->formatDuration($videoDuration);

							$estimatedRevenue = $this->estimatedRevenue($views, $regionCode);
							$totalAllRevenue += $estimatedRevenue;

							// Add video data to the array
							$videos[] = [
								'videoId' => $videoId,
								'title' => $title,
								'thumbnail' => $thumbnailUrl,
								'duration' => $durationFormatted,
								'genre' => $genre,
								'uploadDate' => $formattedUploadDate,
								'estimatedRevenueIDR' => number_format($estimatedRevenue, 0, ',', '.'),
								'totalVideoViews' => number_format($views, 0, ',', '.'),
                                'viewSources' => $viewSources,
                                'viewSources1' => $viewSources1,
                                'viewSources2' => $viewSources2,
                                'viewSources3' => $viewSources3,
								'viewsAge1' => $viewsAge1,
								'viewsAge2' => $viewsAge2,
								'viewsAge3' => $viewsAge3,
								'viewsAge4' => $viewsAge4,
								'viewsAge5' => $viewsAge5,
								'percentageTotal' => number_format($percentageTotal, 2, ',', '.'),
								'percentageAge1' => number_format($percentageAge1, 2, ',', '.'),
								'percentageAge2' => number_format($percentageAge2, 2, ',', '.'),
								'percentageAge3' => number_format($percentageAge3, 2, ',', '.'),
								'percentageAge4' => number_format($percentageAge4, 2, ',', '.'),
								'percentageAge5' => number_format($percentageAge5, 2, ',', '.'),
							];

							$videoCount++; // Increment video count

							if ($videoCount >= $maxResults) {
								break 2; // Stop the loop if we have enough valid videos
							}
						}

						// Get the next page token, if any
						$nextPageToken = $searchResponse->getnextPageToken();

					} while ($nextPageToken && $videoCount < $maxResults);

					// Define the directory and file path
					$logDirectory = "log/";
					$logFilePath = $logDirectory . "youtubeapi" . date("dMY") . ".txt";

					// Check if the directory exists; if not, create it
					if (!is_dir($logDirectory)) {
						mkdir($logDirectory, 0755, true); // Create the directory with read/write permissions
					}

					// Open the file for writing
					$uchwyt = fopen($logFilePath, "a");

					// Write to the file
					fwrite($uchwyt, "===Youtube API==\r\n");
					fwrite($uchwyt, "Channel Id :");
					fwrite($uchwyt, "$channelId\r\n");
					fwrite($uchwyt, "Period :");
					fwrite($uchwyt, "$period\r\n");
					fwrite($uchwyt, "Published Before:");
					fwrite($uchwyt, "$publishedBefore\r\n");
					fwrite($uchwyt, "Published After :");
					fwrite($uchwyt, "$publishedAfter\r\n");
					$searchResponseJSON = json_encode($searchResponse);
					fwrite($uchwyt, "Search Response :");
					fwrite($uchwyt, "$searchResponseJSON\r\n");
                    $analyticsResponseJSON = json_encode($analyticsResponse);
					fwrite($uchwyt, "Analytics Response :");
					fwrite($uchwyt, "$analyticsResponseJSON\r\n");
					$analyticsResponse1JSON = json_encode($analyticsResponse1);
					fwrite($uchwyt, "Analytics Response 1 :");
					fwrite($uchwyt, "$analyticsResponse1JSON\r\n");
					$analyticsResponse2JSON = json_encode($analyticsResponse2);
					fwrite($uchwyt, "Analytics Response 2 :");
					fwrite($uchwyt, "$analyticsResponse2JSON\r\n");
					$analyticsResponse3JSON = json_encode($analyticsResponse3);
					fwrite($uchwyt, "Analytics Response 3 :");
					fwrite($uchwyt, "$analyticsResponse3JSON\r\n");
					//$videosJSON = json_encode($videos);
					//fwrite($uchwyt, "Video :");
					//fwrite($uchwyt, "$videosJSON\r\n");

					// Close the file
					fclose($uchwyt);

					// Pastikan totalVideos adalah integer dan diinisialisasi dengan benar
					$totalVideos = count($videos);

					// Load view dengan data video dan monetisasi
					$data['totalVideos'] = $totalVideos;
					$data['totalResults'] = $totalResults;
					$data['videos'] = $videos;
					$data['subscriberCount'] = $subscriberCount;  // Pass the subscriber count to the view
					$data['totalViewCount'] = $totalViewCount; // Pass the total view count to the view
					$data['joinedDate'] = $formattedJoinedDate;  // Pass the "Joined Date" to the view
					$data['regionCode'] = $formattedRegionCode;  // Pass the region code to the view
					$data['channelUrl'] = $channelUrl;  // Pass the channel URL to the view
					$data['totalAllRevenue'] = number_format($totalAllRevenue, 0, ',', '.');
					$data['connected'] = true;
					//$this->load->view('monetary', $data);

                } catch (Exception $e) {
                    log_message('error', 'Error fetching video data: ' . $e->getMessage());
                    echo "Error: " . $e->getMessage();
                }
                //BEDA FUNGSI
            }
        }

        

		$this->load->view('traffic_source', $data);
	}

    // Fungsi untuk memformat durasi dari ISO 8601 ke format jam:menit:detik
    private function formatDuration($duration) {
        $interval = new DateInterval($duration);
        $hours = $interval->h;
        $minutes = $interval->i;
        $seconds = $interval->s;

        $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        return $formattedDuration;
    }

	//https://www.googleapis.com/youtube/v3/videoCategories?part=snippet&regionCode=US&key=AIzaSyAo5imRlTEQsgDiYkCygOnvgqxbeixMxy4
    // Define the mapping of YouTube categories to genres
    private $categoryToGenre = [
		'1' => 'Film & Animation',
		'2' => 'Autos & Vehicles',
        '10' => 'Music',
		'15' => 'Pets & Animals',
		'17' => 'Sports',
		'18' => 'Short Movies',
		'19' => 'Travel & Events',
        '20' => 'Gaming',
		'21' => 'Videoblogging',
        '22' => 'People & Blogs',
        '23' => 'Comedy',
        '24' => 'Entertainment',
		'25' => 'News & Politics',
		'26' => 'Howto & Style',
		'27' => 'Education',
		'28' => 'Science & Technology',
		'29' => 'Nonprofits & Activism',
		'30' => 'Movies',
		'31' => 'Anime/Animation',
		'32' => 'Action/Adventure',
		'33' => 'Classics',
		'34' => 'Comedy',
		'35' => 'Documentary',
		'36' => 'Drama',
		'37' => 'Family',
		'38' => 'Foreign',
		'39' => 'Horror',
		'40' => 'Sci-Fi/Fantasy',
		'41' => 'Thriller',
		'42' => 'Shorts',
		'43' => 'Shows',
		'44' => 'Trailers',
        // Add more mappings as needed
    ];

	private function getRealTimeExchangeRate() {
		// Fetch the exchange rate from an API (this is a sample URL, replace with actual API call)
		$url = "https://api.exchangerate-api.com/v4/latest/USD";
		
		// Initialize cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		// Execute cURL request
		$response = curl_exec($ch);
		curl_close($ch);
		
		// Decode JSON response
		$data = json_decode($response, true);
		
		// Get IDR rate from response
		$usdToIdrRate = $data['rates']['IDR'] ?? 15000; // Default to 15000 if not available
		
		return $usdToIdrRate;
	}

	private function getCPMByRegion($regionCode) {
		// Daftar CPM berdasarkan lokasi
		$cpmRates = [
			'US' => 2.0, // USD
			'ID' => 1.0, // Indonesia
			'SG' => 1.5, // Singapore
			'MY' => 1.2, // Malaysia
			'AU' => 2.5, // Australia
			'UK' => 2.2, // United Kingdom
			'Unknown' => 1.0 // Default value for unknown regions
		];
	
		// Return CPM based on region code, or default to 'Unknown' CPM
		return $cpmRates[$regionCode] ?? $cpmRates['Unknown'];
	}

	private function estimatedRevenue($views, $regionCode) {
		//Biaya per mille (CPM) YouTube adalah harga yang dibayar pengiklan untuk setiap 1.000 penayangan iklan di platform
        // Dapatkan CPM berdasarkan lokasi
		$estimatedCPM = $this->getCPMByRegion($regionCode);

		/*
        $url = 'https://kursdollar.net/real-time/USD/';
		$document = new Document($url, true);

		$usdToIdrRate = $document->find('td')[3];
		*/

		// Get real-time USD to IDR exchange rate
		$usdToIdrRate = $this->getRealTimeExchangeRate();

        // Hitung pendapatan estimasi dalam Rupiah
        $estimatedRevenueUSD = ($views / 1000) * $estimatedCPM;
        $estimatedRevenueIDR = $estimatedRevenueUSD * $usdToIdrRate;

        $estimatedRevenue = $estimatedRevenueIDR;

        return $estimatedRevenue;
    }
	
    private function estimatedRevenuePerYear($views, $publishedAt) {
        $estimatedCPM = 2; // Estimasi CPM dalam USD
        $usdToIdrRate = 15000; // Nilai tukar USD ke IDR

        // Ubah tanggal publish menjadi objek DateTime
        $uploadDate = new DateTime($publishedAt);
        $currentDate = new DateTime();

        // Hitung selisih tahun antara tanggal sekarang dan tanggal upload
        $interval = $currentDate->diff($uploadDate);
        $yearsDifference = $interval->y;

        // Jika video diunggah lebih dari setahun yang lalu, hitung estimasi per tahun
        if ($yearsDifference >= 1) {
            // Hitung estimasi pendapatan per tahun dalam USD
            $estimatedRevenueUSD = ($views / 1000) * $estimatedCPM * 12;
            // Konversi ke IDR
            $estimatedRevenueIDR = $estimatedRevenueUSD * $usdToIdrRate;

            // Format estimasi pendapatan ke format yang diinginkan
            $estimatedRevenue = number_format($estimatedRevenueIDR, 0, ',', '.');
        } else {
            // Jika belum setahun, return kosong atau null sesuai kebutuhan Anda
            $estimatedRevenue = ''; // Atau bisa juga null, tergantung bagaimana Anda menangani logika di view
        }

        return $estimatedRevenue;
    }
    private function estimatedRevenuePerMonth($views, $publishedAt) {
		$estimatedCPM = 2; // Estimasi CPM dalam USD
		$usdToIdrRate = 15000; // Nilai tukar USD ke IDR
	
		// Ubah tanggal publish menjadi objek DateTime
		$uploadDate = new DateTime($publishedAt);
		$currentDate = new DateTime();
	
		// Hitung selisih bulan antara tanggal sekarang dan tanggal upload
		$interval = $currentDate->diff($uploadDate);
		$monthsDifference = ($interval->y * 12) + $interval->m;
	
		// Jika video diunggah lebih dari sebulan yang lalu, hitung estimasi per bulan
		if ($monthsDifference >= 1) {
			// Hitung estimasi pendapatan per bulan dalam USD
			$estimatedRevenueUSD = ($views / 1000) * $estimatedCPM;
			// Konversi ke IDR
			$estimatedRevenueIDR = $estimatedRevenueUSD * $usdToIdrRate;
	
			// Format estimasi pendapatan ke format yang diinginkan
			$estimatedRevenue = number_format($estimatedRevenueIDR, 0, ',', '.');
		} else {
			// Jika belum sebulan, return kosong atau null sesuai kebutuhan Anda
			$estimatedRevenue = ''; // Atau bisa juga null, tergantung bagaimana Anda menangani logika di view
		}
	
		return $estimatedRevenue;
	}

	private function estimatedRevenuePerDay($views, $publishedAt) {
        $estimatedCPM = 2; // Estimasi CPM dalam USD
        $usdToIdrRate = 15000; // Nilai tukar USD ke IDR
    
        // Ubah tanggal publish menjadi objek DateTime
        $uploadDate = new DateTime($publishedAt);
        $currentDate = new DateTime();
    
        // Hitung selisih hari antara tanggal sekarang dan tanggal upload
        $interval = $currentDate->diff($uploadDate);
        $daysDifference = $interval->days;
    
        // Jika video diunggah lebih dari sehari yang lalu, hitung estimasi per hari
        if ($daysDifference >= 1) {
            // Hitung estimasi pendapatan per hari dalam USD
            $estimatedRevenueUSD = ($views / 1000) * $estimatedCPM / 30; // Asumsi 30 hari dalam sebulan
            // Konversi ke IDR
            $estimatedRevenueIDR = $estimatedRevenueUSD * $usdToIdrRate;
    
            // Format estimasi pendapatan ke format yang diinginkan
            $estimatedRevenue = number_format($estimatedRevenueIDR, 0, ',', '.');
        } else {
            // Jika belum sehari, return kosong atau null sesuai kebutuhan Anda
            $estimatedRevenue = ''; // Atau bisa juga null, tergantung bagaimana Anda menangani logika di view
        }
    
        return $estimatedRevenue;
    }

    public function callback() {
        if (isset($_GET['code'])) {
            try {
                // Exchange the authorization code for an access token
                $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code'], $this->session->userdata('code_verifier'));
                $this->client->setAccessToken($token);
                $this->session->set_userdata('google_oauth_token', $token);
                // Redirect to the index method after connecting
                redirect(base_url('traffic_source'));
            } catch (Exception $e) {
                // Handle the exception and log it
                log_message('error', 'OAuth callback exception: ' . $e->getMessage());
                echo "Exception: " . $e->getMessage();
            }
        } else {
            // Handle the case where no authorization code is provided
            log_message('error', 'No authorization code found in the callback.');
            echo "Error: No authorization code found in the callback.";
        }
    }


    public function disconnect() {
        // Clear the session data
        $this->session->unset_userdata('google_oauth_token');
        $this->session->unset_userdata('code_verifier');

        // Redirect to the index method after disconnecting
        redirect(base_url('traffic_source'));
    }
}