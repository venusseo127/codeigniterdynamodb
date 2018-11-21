<?php
class Welcome extends CI_Controller {
	function __construct()
	{
		parent::__construct();
        include_once($this->config->item('dynamodb').'aws-autoloader.php');
		$this->load->helper('url');
		$this->load->helper('date');
		$this->load->model('Rooms_model','',TRUE);
	}
	function index()
	{
		$data = array(
			'members' => array()
		);
        $room2 = array();
        
		if (!$this->Rooms_model->table_exists()){
			$this->load->view('no_table', $data);
		} else {

			$rooms = $this->Rooms_model->getAll(); // Find all members, limit by 5

            usort($rooms, function ($a, $b) { return ($a['roomid'] > $b['roomid']); });

            $data['rooms'] = $rooms;
		 $this->load->view('welcome_message', $data);	
		}
       
	}
    function view($memberid)
	{
        
        //echo $memberid;
		$roomslist = $this->Rooms_model->getByID($memberid); // Find member details
        //var_dump($roomslist);
        $data2['room1']=$roomslist;

		$this->load->view('view', $data2);
	}
	
	function edit($roomid=0)
	{
        echo $roomid;
        //return $memberid;
		$roomslist = $this->Rooms_model->getByID($roomid); // Find member details
		$data['room1']=$roomslist;
		$data['inserted'] = FALSE;
		// If form submitted
		if($this->input->post('edit'))
		{ 
			// add new member into array
			$room = array(
				'roomid' => (string)$roomid,
                'title' => $this->input->post('title'),
				'owner' => $this->input->post('owner'),
				'background' => $this->input->post('background')
			);
            //print_r($room);
			$this->Rooms_model->save($room); // Insert the member
			
			$data2['inserted'] = TRUE;
            $roomslist2 = $this->Rooms_model->getByID($roomid); // Find member details
		    $data2['room1']=$roomslist2;
			$this->load->view('view', $data2);
		}else{
		
		    $this->load->view('edit', $data); // Load the form
        }
	}
	function add()
	{
		$data = array();
		$data['inserted'] = FALSE;
		
		// If form submitted
		if($this->input->post('add'))
		{
			// add new member into array
			$room = array(
                'title' => $this->input->post('title'),
				'owner' => $this->input->post('owner'),
				'background' => $this->input->post('background')
			);
			$addroom = $this->Rooms_model->save($room); // Insert the member

			$data2['inserted'] = TRUE;
            $roomslist2 = $this->Rooms_model->getByID($addroom); // Find member details
		    $data2['room1']=$roomslist2;
			$this->load->view('view', $data2);
		}else{
		
		    $this->load->view('add', $data); // Load the form
        }
	}
	
	
	function delete($memberid)
	{
		$members = $this->Rooms_model->deleteById($memberid); // Find member details
		//redirect('/', 'refresh');
        if($members){
            $rooms = $this->Rooms_model->getAll(); 
            usort($rooms, function ($a, $b) { return ($a['roomid'] > $b['roomid']); });
            $data['rooms'] = $rooms;
            $this->load->view('welcome_message', $data);
        }else{
            print_r($members);
        }
	}
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
