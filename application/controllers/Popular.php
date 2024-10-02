<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/google-api-php-client--PHP7.4/vendor/autoload.php';

use Google\Client;
use Google\Service\YouTube;
use Google\Service\YouTubeAnalytics;

class Popular extends CI_Controller {

	private $client;

    public function __construct() {
        parent::__construct();
		
        $this->load->library('session');

		date_default_timezone_set('Asia/Jakarta');
    }

	public function index()
	{
		$this->client = new Client();
        $this->client->setApplicationName('Demo Youtube API');

		//$this->client->setAuthConfig('application/views/youtube2.json');
		//$this->client->setAuthConfig('application/views/youtube.json');
		//$this->client->setAuthConfig('application/views/youtube1.json');
		$this->client->setAuthConfig('application/views/youtube2.json');
		$this->client->setRedirectUri(base_url('popular')); // Set your redirect URI
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
				redirect(base_url('popular'));
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
			redirect(base_url('popular'));
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
                    redirect(base_url('popular'));
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
                }

                // Menggabungkan array videoIds menjadi string yang dipisahkan koma
                $videoIdsString = implode(',', $videoIds);

                //echo json_encode($videoIdsString); // Menampilkan string dari videoId

                $period = $this->input->post('period');

                // Convert datetime-local format to YYYY-MM-DD format
                $startDate = '';
                $endDate = '';
                
                // Mendapatkan tanggal hari ini
                $today = new DateTime();

                if ($period === 'week') {
                    /// Set start date ke 7 hari yang lalu dari hari ini dan end date ke hari ini
                    //$endDate = $today->modify('-2 days')->format('Y-m-d');
                    $endDate = date('Y-m-d', strtotime('-2 days'));
                    //$startDate = $today->modify('-6 days')->format('Y-m-d');
                    $startDate = date('Y-m-d', strtotime('-8 days'));
                } 

                else if ($period === 'month') {
                    // Set start date ke awal bulan dan end date ke akhir bulan
                    //$endDate = $today->modify('-2 days')->format('Y-m-d');
                    $endDate = date('Y-m-d', strtotime('-2 days'));
                    //$startDate = $today->modify('-27 days')->format('Y-m-d');
                    $startDate = date('Y-m-d', strtotime('-29 days'));
                } 

                else {
                    //$endDate = $today->modify('-2 days')->format('Y-m-d');
                    $endDate = date('Y-m-d', strtotime('-2 days'));
                    //$startDate = $today->modify('-27 days')->format('Y-m-d');
                    $startDate = date('Y-m-d', strtotime('-29 days'));
                }

                // Cek apakah $videoIdsString kosong
                if (empty($videoIdsString)) {
                    // Tampilkan pesan error atau tangani kesalahan
                    die("Error: Filter video tidak boleh kosong.");
                }

                $analyticsResponse = $analytics->reports->query([
                    'ids' => 'channel==MINE',
                    'startDate' => '2024-09-29',
                    'endDate' => '2024-09-29',
                    'metrics' => 'views',
                    'sort' => 'day',
                    'dimensions' => 'video,day',
                    'filters' => 'video==' . $videoIdsString,
                    //'filters' => 'video==', 
                    //'sort' => '-views',
                    'maxResults' => 200,

                ]);

                //echo($videoIdsString);

                $viewSources = [];
                foreach ($analyticsResponse->getRows() as $row) {
                    $videoId = $row[0]; // Video
                    $day = $row[1]; // Day
                    $views = $row[2]; // View count

                    // Memasukkan data ke array viewSources berdasarkan videoId dan day
                    if (!isset($viewSources[$day])) {
                        $viewSources[$day] = [];
                    }
                    
                    // Simpan berdasarkan hari
                    $viewSources[$day][] = [
                        'videoId' => $videoId,
                        'views' => $views,
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

                //echo json_encode($viewSources, JSON_PRETTY_PRINT);

                /*
                // Kirimkan response berupa tanggal yang di-set
                $response = [
                    'period' => $period,
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ];

                echo json_encode($response);
                */

                // Define the directory and file path
                $logDirectory = "log/";
                $logFilePath = $logDirectory . "popular" . date("dMY") . ".txt";

                // Check if the directory exists; if not, create it
                if (!is_dir($logDirectory)) {
                    mkdir($logDirectory, 0755, true); // Create the directory with read/write permissions
                }

                // Open the file for writing
                $uchwyt = fopen($logFilePath, "a");

                // Write to the file
                fwrite($uchwyt, "===Youtube API==\r\n");
                fwrite($uchwyt, "Period:");
                fwrite($uchwyt, "$period\r\n");
                fwrite($uchwyt, "Start Date:");
                fwrite($uchwyt, "$startDate\r\n");
                fwrite($uchwyt, "End Date :");
                fwrite($uchwyt, "$endDate\r\n");
                $analyticsResponseJSON = json_encode($analyticsResponse);
                fwrite($uchwyt, "Analytics Response :");
                fwrite($uchwyt, "$analyticsResponseJSON\r\n");
                $viewSourcesJSON = json_encode($viewSources);
                fwrite($uchwyt, "Popular :");
                fwrite($uchwyt, "$viewSourcesJSON\r\n");

                $data['viewSources'] = $viewSources;
            }
        }

		$this->load->view('popular', $data);
	}

	public function callback() {
        if (isset($_GET['code'])) {
            try {
                // Exchange the authorization code for an access token
                $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code'], $this->session->userdata('code_verifier'));
                $this->client->setAccessToken($token);
                $this->session->set_userdata('google_oauth_token', $token);
                // Redirect to the index method after connecting
                redirect(base_url('popular'));
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
        redirect(base_url('popular'));
    }
}