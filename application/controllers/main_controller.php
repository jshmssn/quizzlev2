<?php

class main_controller extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model("quiz_model");
        $this->load->library("session");
    }

    public function index() {
        $items = array('player_name', 'room_pin');
        $this->session->unset_userdata($items);
        $this->load->view('welcome');
    }

    public function create() {
        $this->load->view('../views/create/createquiz');
    }

    public function creator() {
        $this->load->view('../views/create/quiz_creator');
    }

    public function submit() {
        $items = array('player_name', 'room_pin');
        $this->session->unset_userdata($items);
    
        $questions = $this->input->post('questions');
    
        if (!empty($questions)) {
            $success = true;
    
            // Generate room_id and PIN
            $roomId = uniqid(); // You might want to use a different method to generate unique room IDs
            $pin = rand(10000, 99999); // Generate a 5-digit PIN
    
            // Create directory for images
            $uploadPath = './assets/images/quiz/Room-' . $roomId . '/';
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0777, true)) {
                    // Directory creation failed
                    $this->session->set_flashdata('status', 'error');
                    $this->session->set_flashdata('msg', 'Failed to create upload directory.');
                    redirect('room/host/' . $pin);
                    return;
                }
            }
         
            // Save the questions
            foreach ($questions as $index => $question) {
                $questionText = $this->security->xss_clean($question['text']);
                $answers = array_map([$this->security, 'xss_clean'], $question['answers']);
                $correctAnswerIndex = (int) $question['correct'];
                $isFill = $this->security->xss_clean($question['fillable']);
                $time = (int) $question['time']; // Get the time value
    
                // Upload image and get the image path
                $imagePath = $this->_upload_question_image($roomId, $index);
                
                if ($imagePath === false) {
                    $success = false;
                    break;
                }
    
                if (!$this->quiz_model->save_question($questionText, $answers, $correctAnswerIndex, $roomId, $time, $imagePath, $isFill)) {
                    $success = false;
                    break;
                }
            }
    
            if ($success) {
                // Save room_id and PIN to the rooms table
                if ($this->quiz_model->save_room($roomId, $pin)) {
                    $this->session->set_flashdata('status', 'success');
                    $this->session->set_flashdata('msg', 'Quiz questions submitted successfully and room created!');
    
                    // Store roomId and roomPin in regular session data
                    $this->session->set_userdata('roomId', $roomId);
                    $this->session->set_userdata('room_pin', $pin);
                } else {
                    $this->session->set_flashdata('status', 'error');
                    $this->session->set_flashdata('msg', 'Failed to save room details.');
                }
            } else {
                $this->session->set_flashdata('status', 'error');
                $this->session->set_flashdata('msg', 'Failed to submit quiz questions.');
            }
        } else {
            $this->session->set_flashdata('status', 'error');
            $this->session->set_flashdata('msg', 'No quiz questions provided.');
        }
    
        redirect('room/host/' . $pin);
    }

    private function _upload_question_image($roomId, $index) {
        $this->load->library('upload');
    
        // Set upload configurations
        $config['upload_path'] = './assets/images/quiz/Room-' . $roomId . '/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = 2048;
    
        $this->upload->initialize($config);
    
        if (isset($_FILES['questions']['name'][$index]['image']) && $_FILES['questions']['name'][$index]['image'] != '') {
            $_FILES['file']['name'] = $_FILES['questions']['name'][$index]['image'];
            $_FILES['file']['type'] = $_FILES['questions']['type'][$index]['image'];
            $_FILES['file']['tmp_name'] = $_FILES['questions']['tmp_name'][$index]['image'];
            $_FILES['file']['error'] = $_FILES['questions']['error'][$index]['image'];
            $_FILES['file']['size'] = $_FILES['questions']['size'][$index]['image'];
    
            if (!$this->upload->do_upload('file')) {
                // Capture and log the error
                $error = $this->upload->display_errors();
                log_message('error', 'Upload error: ' . $error);
                $this->session->set_flashdata('status', 'error');
                $this->session->set_flashdata('msg', 'Image upload failed: ' . $error);
                redirect('quiz_creator');
                return false;
            }
            
            $uploadData = $this->upload->data();
            return 'assets/images/quiz/Room-' . $roomId . '/' . $uploadData['file_name'];
        }
    
        return ''; // Return empty string if no image is uploaded
    }
    
    public function submit_answer() {
        $roomId = $this->input->post('room_id');
        $questionId = $this->input->post('question_id');
        $answerId = $this->input->post('answer_id');
        $responseTime = $this->input->post('response_time'); // Retrieve the time taken from the request
        $answerText = $this->input->post('answer_text'); // For fill-in-the-blank answers
    
        $player_name = $this->session->userdata('player_name');
        $room_pin = $this->session->userdata('room_pin');
        
        // Retrieve participant data
        $participantData = $this->quiz_model->getparticipantdata($player_name, $room_pin);
        
        if (isset($participantData['id'])) {
            $participantId = $participantData['id'];
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Participant ID is missing.']);
            return;
        }
        
        // Check if the question is fill-in-the-blank
        $isFill = $this->quiz_model->is_fill_in_the_blank($questionId);
        
        // Save participant's answer
        if ($isFill) {
            // Save fill-in-the-blank answer
            $this->quiz_model->save_participant_answer($participantId, $roomId, $questionId, null, $answerText, $responseTime);
        } else {
            // Save multiple-choice answer
            $this->quiz_model->save_participant_answer($participantId, $roomId, $questionId, $answerId, $answerText, $responseTime);
        }
        
        // Calculate the score based on the answer and response time
        $score = $this->quiz_model->calculate_score($participantId, $roomId);
        
        // Save the score
        $this->quiz_model->save_score($participantId, $roomId, $score);
        
        // Optionally, save question-specific scores if needed
        $this->quiz_model->save_question_score($participantId, $roomId, $questionId, $score);
        
        echo json_encode(['status' => 'success', 'score' => $score]);
    }
    

    /*
    public function submit_answer() {           
        $roomId = $this->input->post('room_id');
        $questionId = $this->input->post('question_id');
        $answerId = $this->input->post('answer_id'); // Could be null for fill-in-the-blank
        $answerText = $this->input->post('answer_text'); // For fill-in-the-blank
        $responseTime = $this->input->post('response_time');
        
        $player_name = $this->session->userdata('player_name');
        $room_pin = $this->session->userdata('room_pin');
        
        // Retrieve participant data
        $participantData = $this->quiz_model->getparticipantdata($player_name, $room_pin);
        
        if (isset($participantData['id'])) {
            $participantId = $participantData['id'];
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Participant ID is missing.']);
            return;
        }
        
        // Determine if the question is a fill-in-the-blank or multiple-choice
        $isFill = $this->quiz_model->get_is_fill($questionId); // Add this method to get the isFill value
        
        if ($isFill === 0) {
            // Save participant's multiple-choice answer
            $this->quiz_model->save_participant_answer($participantId, $roomId, $questionId, $answerId, $responseTime);
        } elseif ($isFill === 1) {
            // Save participant's fill-in-the-blank answer
            $this->quiz_model->save_participant_answer_text($participantId, $roomId, $questionId, $answerText, $responseTime);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid question type.']);
            return;
        }
        
        // Calculate the score based on the answer and response time
        $score = $this->quiz_model->calculate_score($participantId, $roomId);
        
        // Save the score
        $this->quiz_model->save_score($participantId, $roomId, $score);
        
        // Optionally, save question-specific scores if needed
        $this->quiz_model->save_question_score($participantId, $roomId, $questionId, $score);
        
        echo json_encode(['status' => 'success', 'score' => $score]);
    }
    */
    
        
    public function hostgame() {
        $roomPin = $this->session->userdata('room_pin');
    
        // Fetch participants using the updated model method
        $data['participants'] = $this->quiz_model->get_participants($roomPin);
    
        $this->load->view('host/host', $data);
    }
    
    public function fetch_players_score_per_q() {
        $question_id = $this->input->get('question_id');
        $room_id = $this->input->get('room_id');
    
        // Log the input parameters
        log_message('info', "Fetching scores for Question ID: $question_id, Room ID: $room_id");
    
        $players = $this->quiz_model->get_player_scores($question_id, $room_id);
    
        if ($players) {
            $response = [
                'status' => 'success',
                'players' => $players
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'No players found.'
            ];
        }
    
        echo json_encode($response);
    }  

    public function get_players() {
        $roomPin = $this->session->userdata('room_pin');
    
        if ($roomPin) {
            // Fetch participants using the model
            $participants = $this->quiz_model->get_participants($roomPin);
    
            // Return participants as JSON
            echo json_encode(['players' => $participants]);
        } else {
            echo json_encode(['players' => []]);
        }
    }

    
    public function view_score($participant_id, $room_id) {
        // Load the model
        $this->load->model('quiz_model');
    
        // Get total score
        $total_score = $this->quiz_model->get_total_score($participant_id, $room_id);
    
        // Pass the score data to the view
        $data['total_score'] = $total_score;
        $this->load->view('view_score', $data);
    }

  
/*
    public function save_score() {
        $this->load->quiz_model('quiz_model'); // Load your model
        $participant_id = $this->session->userdata('participant_id');
        $score = $this->input->post('score');
        $question_id = $this->input->post('question_id');
    
        // Save the score to the database
        $this->quiz_model->save_score($participant_id, $score, $question_id);
    }

  */  
    
    public function join() {
        // Get form data
        $name = $this->input->post('name');
        $room_pin = $this->input->post('room_pin');
        
        // Validate the input (e.g., check if the room_pin exists)
        $validation_result = $this->quiz_model->validate_room_pin($room_pin);

        // Check if the room is valid and has started
        if ($validation_result['isValid'] == '0' && $validation_result['hasStarted'] == '1') {
            // Set an error message in flashdata
            $this->session->set_flashdata("status", "error");
            $this->session->set_flashdata("msg", "The game has already started or the room is invalid.");
            
            // Redirect to an error page or previous page
            redirect('/error'); // Adjust the redirect URL as needed
        } elseif ($validation_result['isValid'] == '1' && $validation_result['hasStarted'] == '0') {
            // Process the join logic and get the unique player name
            $unique_name = $this->quiz_model->process_join($name, $room_pin);
            
            // Store the player's unique name and room_pin in session data
            $this->session->set_userdata('player_name', $unique_name);
            $this->session->set_userdata('room_pin', $room_pin);
            
            // Redirect to the room
            redirect('room/'. $room_pin);
        } else {
            // Set an error message in flashdata
            $this->session->set_flashdata("status", "error");
            $this->session->set_flashdata("msg", "Invalid PIN");
            
            // Redirect back to the join page or previous page
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function room() {
        // Check if player is logged in by checking session data
        if (!$this->session->userdata('player_name') || !$this->session->userdata('room_pin')) {
            // If not, redirect to a different page (e.g., main page)
            redirect(base_url());
        }
    
        // Load the confirmation view
        $this->load->view('player/room');
    }
    
    public function quitroom() {    
        // Get the room pin from session data
        $roomPin = $this->session->userdata('room_pin');
        $roomId = $this->session->userdata('roomId');
    
        // Check if the room pin exists in the session
        if ($roomPin && $roomId) {
            // Update the room's validity status
            $this->quiz_model->invalidate_room($roomId);
            $this->quiz_model->exit_all_participants($roomPin);
            $this->quiz_model->delete_room_questions($roomId);
    
            // Unset the roomPin session data
            $items = array('player_name', 'room_pin');
            $this->session->unset_userdata($items);
    
            // Set a flash message for success
            $this->session->set_flashdata('status', 'success');
            $this->session->set_flashdata('msg', 'You have left the room successfully.');
        } else {
            // Set a flash message for error
            $this->session->set_flashdata('status', 'error');
            $this->session->set_flashdata('msg', 'Room PIN could not be found.');
        }
    
        // Redirect to the index page
        redirect(base_url());
    }
    
    public function leftroom() {
        // Get the player name and room PIN from session data
        $playerName = $this->session->userdata('player_name');
        $roomPin = $this->session->userdata('room_pin');
    
        // Check if player name and room PIN exist in session
        if ($playerName && $roomPin) {
            // Call the model method to delete the participant
            $this->quiz_model->left_participant($playerName, $roomPin);
    
            // Unset the player name from session data
            $this->session->unset_userdata('player_name');
        }
    
        // Redirect to the welcome page
        redirect(base_url());
    }

    public function get_room_status() {
        // Retrieve the room PIN from the query parameters
        $roomPin = $this->input->get('pin');
    
        // Validate the room PIN
        if ($roomPin) {
            // Fetch the room status from the database
            $this->db->select('isValid, hasStarted');
            $this->db->where('pin', $roomPin);
            $query = $this->db->get('rooms');
    
            if ($query->num_rows() > 0) {
                $result = $query->row();
                $response = array(
                    'isValid' => $result->isValid,
                    'hasStarted' => $result->hasStarted
                );
            } else {
                $response = array(
                    'isValid' => 0,
                    'hasStarted' => 0
                );
            }
        } else {
            $response = array(
                'isValid' => 0,
                'hasStarted' => 0
            );
        }
    
        // Return the response in JSON format
        echo json_encode($response);
    }
  

    public function fetch_room_id() {
        $roomPin = $this->input->post('roomPin');

        if ($roomPin) {
            $roomId = $this->quiz_model->get_room_id_by_pin($roomPin);
            echo $roomId ? $roomId : 'Room ID not found'; // Send the response
        } else {
            echo 'No room pin provided';
        }
    }

    public function get_image_path() {
        // Example: Get the quest image path from a model or directly
        $questId = $this->input->post('questId');

        $this->load->model('quiz_model');
        $questImage = $this->quiz_model->get_image_path($questId); // Fetch the image path

        // Return the image path as JSON
        echo json_encode(['imagePath' => $questImage]);
    }

    public function fetch_questions() {
        $room_id = $this->input->post('room_id');
    
        if (!$room_id) {
            echo json_encode(['status' => 'error', 'message' => 'Room ID is required']);
            return;
        }
    
        $this->load->model('quiz_model');
        $questions = $this->quiz_model->get_questions_by_room($room_id);
    
        if (!empty($questions)) {
            echo json_encode(['status' => 'success', 'data' => $questions]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No questions found for this room']);
        }
    }


    public function fetch_user_score() {
        // Get participant ID from session
        $participantId = $this->session->userdata('participant_id'); // Ensure this session variable is set
        
        // Get room ID from POST request
        $roomId = $this->input->post('room_id');
        
        // Load the model
        $this->load->model('quiz_model');
        
        // Fetch the score from the model
        $score = $this->quiz_model->get_user_score($participantId, $roomId);
        
        // Check if the score was found and is non-negative
        if ($score !== false && $score >= 0) { // Adjust condition based on what your model returns
            // Return the score in JSON format
            echo json_encode(['status' => 'success', 'score' => $score]);
        } else {
            // Return an error message if the score was not found or is invalid
            echo json_encode(['status' => 'error', 'message' => 'Score not found.']);
        }
    }
    
    
   
    public function fetch_answers() {
        $question_id = $this->input->post('question_id');
    
        if (!$question_id) {
            echo json_encode(['status' => 'error', 'message' => 'Question ID is required']);
            return;
        }
    
        $this->load->model('quiz_model');
        $answers = $this->quiz_model->get_answers_by_question($question_id);
    
        if (!empty($answers)) {
            echo json_encode(['status' => 'success', 'data' => $answers]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No answers found for this question']);
        }
    }

    public function fetch_correct_answers() {
        $question_id = $this->input->post('question_id');
    
        $this->load->model('quiz_model');
        $answers = $this->quiz_model->get_correct_answers($question_id);
    
        if ($answers) {
            echo json_encode(['status' => 'success', 'data' => $answers]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No correct answers found.']);
        }
    }    
    
    public function start_game() {
        // Load the view with data
        $this->load->view('player/quiz_view');
    }

    public function start_game_host() {
        $this->load->view('host/quiz_view_host');
    }

    public function start(){
        // Example: Fetch the room PIN from the input
        $roomPin = $this->input->post('room_pin');
    
        if ($roomPin) {
            // Update the hasStarted field to 1 where the pin matches
            $this->db->where('pin', $roomPin);
            $this->db->set('hasStarted', 1);
            $this->db->set('isValid', 0);
            $updateSuccess = $this->db->update('rooms');
    
            if (!$updateSuccess) {
                // Handle the error if the update failed
                $this->session->set_flashdata('status', 'error');
                $this->session->set_flashdata('msg', 'Failed to update room status.');
            }
        } else {
            // Handle the case where roomPin is not set or is invalid
            $this->session->set_flashdata('status', 'error');
            $this->session->set_flashdata('msg', 'Room PIN is not set.');
        }
    }
}
?>
