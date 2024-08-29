<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Use Google Client and YouTube Service
use Google\Client;
use Google\Service\YouTube;

// Load Composer's autoloader
require_once 'vendor/autoload.php';

class Editvideo extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */

	public function index() {
		$data['test'] = "test";
		// Start session
        session_start();

        // Edit details
        //$videoId = '2hDQp6M42hg'; // Must be a video that belongs to the currently auth’d user
        $videoId = 'gH-mfKXr7nY&t=7s';
        $newTitle = 'New Laravel application with Herd and DBngin...';

        // Set up client and service
        $client = new Client();
        $service = new YouTube($client);

        // Authorize client
        //if (!empty($_SESSION['google_oauth_token'])) {
            //$client->setAccessToken($_SESSION['google_oauth_token']);
        //} else {
            // If not authorized, redirect back to index
            //redirect('monetary');
        //}

        // Get the existing snippet details for this video pre-edit
        $response = $service->videos->listVideos(
            'snippet',
            ['id' => $videoId]
        );
        $video = $response[0];
        $snippet_before = $video->snippet;

        // Set the edits
        $snippet_before->setTitle($newTitle);

        // Set the snippet
        $video->setSnippet($snippet_before);

        // Do the update
        $response = $service->videos->update('snippet', $video);
        $snippet_after = $response->snippet;

        // Pass the response to the view
        $data['snippet_before'] = $snippet_before;
        $data['snippet_after'] = $snippet_after;

        // Load the view
		$this->load->view('editvideo', $data);
	}
}