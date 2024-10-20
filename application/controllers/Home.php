<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

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
	public function index()
	{
		$this->load->view('home');
	}

	public function view($id,$title)
	{
		$data['id'] = $id;
		$data['title'] = $title;
		$this->load->view('view', $data);
	}

	public function playlists()
	{
		$this->load->view('playlists');
	}

	public function activities()
	{
		$this->load->view('activities');
	}

	public function videos()
	{
		$this->load->view('videos');
	}

	public function monetary()
	{
		$this->load->view('monetary');
	}

	public function editvideo()
	{
		$this->load->view('editvideo');
	}
}
