<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//require_once 'vendor/autoload.php';
require_once APPPATH . 'libraries/google-api-php-client--PHP7.4/vendor/autoload.php';

use Google\Client;
use Google\Service\YouTube;
use Google\Service\YouTubeAnalytics;
use Google\Service\YoutubeAnalyticsMonetary;

class Views extends CI_Controller {
	private $client;

    public function __construct() {
        parent::__construct();
		
        $this->load->library('session');

		date_default_timezone_set('Asia/Jakarta');
    }

    public function index(){
    	$this->client = new Client();
        $this->client->setApplicationName('Demo Youtube API');

		//$this->client->setAuthConfig('application/views/youtube2.json');
		//$this->client->setAuthConfig('application/views/youtube.json');
		//$this->client->setAuthConfig('application/views/youtube1.json');
		$this->client->setAuthConfig('application/views/youtube2.json');
		$this->client->setRedirectUri(base_url('views')); // Set your redirect URI
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
				redirect(base_url('views'));
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
			redirect(base_url('views'));
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
                    redirect(base_url('views'));
            } else {
                $data['connected'] = true;

                // Inisialisasi YouTube Analytics API service
                $youtube = new YouTube($this->client);
                $analytics = new YoutubeAnalytics($this->client);

                $queryParams = [
                    'channelId' => 'UCQ7dUY53AOGGTYl_Myiurlw',
                    'maxResults' => 50, // Sesuaikan dengan jumlah video
                    'order' => 'date', // Atau gunakan 'viewCount' untuk mendapatkan video populer
                    'type' => 'video'
                ];

                $response = $youtube->search->listSearch('snippet', $queryParams);

                //echo json_encode($response);

                // Ambil seluruh videoId dari respon
                $videoIds = [];
                foreach ($response['items'] as $item) {
                    if (isset($item['id']['videoId'])) {
                        $videoIds[] = $item['id']['videoId'];
                    }
                    if (isset($item['snippet']['title'])) {
                        $videoTitles[] = $item['snippet']['title'];
                    }
                }

                $countryIdsString = "ID";

                // Menggabungkan array videoIds menjadi string yang dipisahkan koma
                $videoIdsString = implode(',', $videoIds);

                $startDate = '2022-12-10';
                $endDate = '2022-12-20';

                /*
                $response = array(
                    "Start Date: " => $startDate,
                    "End Date: " => $endDate,

                );

                echo '<pre>';
                echo json_encode($response);
                echo '</pre>';
                */

                echo '<pre>';
                echo "Start Date: ", $startDate;
                echo '</pre>';
                echo '<pre>';
                echo "End Date: ", $endDate;
                echo '</pre>';

                //echo json_encode($videoIdsString);

                //echo json_encode($videoTitles);

                /*
                    $startDate = $this->input->post();
                    $endDate = $this->input->post();
                */

                // Create a mapping of videoId to videoTitle
                $videoIdToTitle = array_combine($videoIds, $videoTitles);


                $analyticsResponse = $analytics->reports->query([
                    'ids' => 'channel==MINE',
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'metrics' => 'views,averageViewDuration,estimatedMinutesWatched',
                    'dimensions' => 'video,day',
                    'filters' => 'video==' . $videoIdsString,
                    //'sort' => '-views',
                    //'maxResults' => 200,

                ]);

                $analyticsResponse1 = $analytics->reports->query([
                	'ids' => 'channel==MINE',
                	'startDate' => $startDate,
    				'endDate' => $endDate,
    				'metrics' => 'views,averageViewDuration,estimatedMinutesWatched',
    				'dimensions' => 'video,country',
    				'filters' => 'video==' . $videoIdsString,
               	]);

                $analyticsResponse2 = $analytics->reports->query([
                    'ids' => 'channel==MINE',
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'metrics' => 'views,averageViewDuration,estimatedMinutesWatched',
                    'dimensions' => 'video,day,country',
                    'filters' => 'video==' . $videoIdsString.";country==".$countryIdsString,
                ]);

                //$estimatedRevenue = $this->estimatedRevenue();

                $viewSources = [];
                foreach ($analyticsResponse->getRows() as $row) {
                    $videoId = $row[0]; // Video
                    $day = $row[1]; // Day
                    $views = $row[2]; // Views
                    $averageViewDuration = $row[3]; // Average View Duration
                    $estimatedMinutesWatched = $row[4]; // Estimated Minutes Watched

                    // Get video title from the mapping
                    $videoTitle = $videoIdToTitle[$videoId] ?? 'Unknown Title';

                    // Memasukkan data ke array viewSources berdasarkan videoId dan day
                    if (!isset($viewSources[$day])) {
                        $viewSources[$day] = [];
                    }
                    
                    // Simpan berdasarkan hari
                    $viewSources[$day][] = [
                        'videoId' => $videoId,
                        'videoTitle' => $videoTitle,
                        'views' => $views,
                        'averageViewDuration' => $averageViewDuration,
                        'estimatedMinutesWatched' => $estimatedMinutesWatched,
                    ];
                }

                $viewSources1 = [];
                foreach ($analyticsResponse1->getRows() as $row) {
                    $videoId = $row[0]; // Video
                    $country = $row[1]; // Country
                    $views = $row[2]; // Views
                    $averageViewDuration = $row[3]; // Average View Duration
                    $estimatedMinutesWatched = $row[4]; // Estimated Minutes Watched

                    // Get video title from the mapping
                    $videoTitle = $videoIdToTitle[$videoId] ?? 'Unknown Title';

                    // Memasukkan data ke array viewSources berdasarkan videoId dan country
                    if (!isset($viewSources1[$country])) {
                        $viewSources1[$country] = [];
                    }
                    
                    // Simpan berdasarkan hari
                    $viewSources1[$country][] = [
                        'videoId' => $videoId,
                        'videoTitle' => $videoTitle,
                        'views' => $views,
                        'averageViewDuration' => $averageViewDuration,
                        'estimatedMinutesWatched' => $estimatedMinutesWatched,
                    ];
                }

                $viewSources2 = [];
                foreach ($analyticsResponse2->getRows() as $row) {
                    $videoId = $row[0]; // Video
                    $day = $row[1]; // Day
                    $country = $row[2]; // Country
                    $views = $row[3]; // Views
                    $averageViewDuration = $row[4]; // Average View Duration
                    $estimatedMinutesWatched = $row[5]; // Estimated Minutes Watched

                    // Get video title from the mapping
                    $videoTitle = $videoIdToTitle[$videoId] ?? 'Unknown Title';

                    // Memasukkan data ke array viewSources berdasarkan videoId dan day
                    if (!isset($viewSources2[$day])) {
                        $viewSources2[$day] = [];
                    }
                    
                    // Simpan berdasarkan hari
                    $viewSources2[$day][] = [
                        'videoId' => $videoId,
                        'videoTitle' => $videoTitle,
                        'country' => $country,
                        'views' => $views,
                        'averageViewDuration' => $averageViewDuration,
                        'estimatedMinutesWatched' => $estimatedMinutesWatched,
                    ];
                }

                // Urutkan data berdasarkan 'day' untuk setiap videoId
                foreach ($viewSources as $day => &$entries) {
                    usort($entries, function($a, $b) {
                        return strtotime($a['videoId']) - strtotime($b['videoId']); // Mengurutkan dari yang lama ke yang terbaru
                    });

                    // Setelah itu, urutkan berdasarkan 'views' (dari tertinggi ke terendah)
                    usort($entries, function($a, $b) {
                        return $b['views'] - $a['views']; // Mengurutkan dari yang tertinggi ke yang terendah
                    });
                }

                
                // Define the directory and file path
                $logDirectory = "log/";
                $logFilePath = $logDirectory . "views" . date("dMY") . ".txt";

                // Check if the directory exists; if not, create it
                if (!is_dir($logDirectory)) {
                    mkdir($logDirectory, 0755, true); // Create the directory with read/write permissions
                }

                // Open the file for writing
                $uchwyt = fopen($logFilePath, "a");

                // Write to the file
                fwrite($uchwyt, "===Youtube API==\r\n");
                fwrite($uchwyt, "Start Date:");
                fwrite($uchwyt, "$startDate\r\n");
                fwrite($uchwyt, "End Date :");
                fwrite($uchwyt, "$endDate\r\n");
                $analyticsResponseJSON = json_encode($analyticsResponse);
                fwrite($uchwyt, "Analytics Response :");
                fwrite($uchwyt, "$analyticsResponseJSON\r\n");
                $analyticsResponse1JSON = json_encode($analyticsResponse1);
                fwrite($uchwyt, "Analytics Response 1 :");
                fwrite($uchwyt, "$analyticsResponse1JSON\r\n");
                $analyticsResponse2JSON = json_encode($analyticsResponse2);
                fwrite($uchwyt, "Analytics Response 2 :");
                fwrite($uchwyt, "$analyticsResponse2JSON\r\n");

                $data['viewSources'] = $viewSources;
                $data['viewSources1'] = $viewSources1;
                $data['viewSources2'] = $viewSources2;
            }
    	}

    	$this->load->view('views', $data);
	}

	public function callback() {
        if (isset($_GET['code'])) {
            try {
                // Exchange the authorization code for an access token
                $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code'], $this->session->userdata('code_verifier'));
                $this->client->setAccessToken($token);
                $this->session->set_userdata('google_oauth_token', $token);
                // Redirect to the index method after connecting
                redirect(base_url('views'));
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
        redirect(base_url('views'));
    }

    private function estimatedRevenue($views) {
        $estimatedCPM = 2; // Estimasi CPM dalam USD
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
}