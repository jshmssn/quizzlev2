<?php

class quiz_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Function to get player scores for a specific question
    public function get_player_scores($question_id, $room_id) {
        // Select the player's name and score
        $this->db->select('p.name, pqs.score');
        $this->db->from('participant_question_scores pqs');
        $this->db->join('participants p', 'p.id = pqs.participant_id'); // Join the participants table
        $this->db->where('pqs.room_id', $room_id);
        $this->db->where('pqs.question_id', $question_id); // Filter by question_id
        $this->db->order_by('pqs.score', 'DESC'); // Order by score in descending order
        
        // Execute the query
        $query = $this->db->get();
    
        // Return the result as an associative array
        return $query->result_array();
    }    
    
    public function save_question($questionText, $answers, $correctAnswerIndex, $roomId, $time, $imagePath, $isFill) {
        // Insert the question
        $data = array(
            'question_text' => $questionText,
            'room_id' => $roomId,
            'time' => $time,
            'isFill' => $isFill,
            'image_path' => $imagePath // Add image_path to the data array
        );
        $this->db->insert('questions', $data);
    
        if ($this->db->affected_rows() > 0) {
            $questionId = $this->db->insert_id();
            
            // Insert answers
            foreach ($answers as $index => $answerText) {
                $isCorrect = ($index === $correctAnswerIndex) ? 1 : 0;
                $this->db->insert('answers', array(
                    'question_id' => $questionId,
                    'answer_text' => $answerText,
                    'is_correct' => $isCorrect
                ));
            }
            
            // Check if answers were inserted successfully
            return ($this->db->affected_rows() > 0);
        }
    
        return false;
    }

    // SCORING
    public function save_score($participantId, $roomId, $score) {
        $this->db->where('participant_id', $participantId);
        $this->db->where('room_id', $roomId);
        $query = $this->db->get('participant_scores');
        
        if ($query->num_rows() > 0) {
            // Update existing score record
            $this->db->where('participant_id', $participantId);
            $this->db->where('room_id', $roomId);
            $data = array(
                'score' => $score,
                'updated_at' => date('Y-m-d H:i:s')
            );
            $this->db->update('participant_scores', $data);
        } else {
            // Insert new score record
            $data = array(
                'participant_id' => $participantId,
                'room_id' => $roomId,
                'score' => $score,
                'created_at' => date('Y-m-d H:i:s')
            );
            $this->db->insert('participant_scores', $data);
        }
    }

    public function save_question_score($participantId, $roomId, $questionId, $score) {
        // Prepare the data
        $data = array(
            'participant_id' => $participantId,
            'room_id' => $roomId,
            'question_id' => $questionId,
            'score' =>  $score,
        );
    
        // Check if a score for this question already exists
        $this->db->where('participant_id', $participantId);
        $this->db->where('room_id', $roomId);
        $this->db->where('question_id', $questionId);
        $query = $this->db->get('participant_question_scores');
    
        if ($query->num_rows() > 0) {
            // Update the existing record
            $this->db->where('participant_id', $participantId);
            $this->db->where('room_id', $roomId);
            $this->db->where('question_id', $questionId);
            $this->db->update('participant_question_scores', $data);
        } else {
            // Insert a new record
            $data['created_at'] = date('Y-m-d H:i:s'); // Set created_at timestamp
            $this->db->insert('participant_question_scores', $data);
        }
    }

    /*
    public function save_question_score($participantId, $roomId, $questionId, $score) {
    // Prepare data to insert or update
    $data = array(
        'participant_id' => $participantId,
        'room_id'         => $roomId,
        'question_id'     => $questionId,
        'score'           => $score,
        'created_at'      => date('Y-m-d H:i:s') // Add timestamps if needed
    );

    // Check if a record already exists for this participant, room, and question
    $this->db->where('participant_id', $participantId);
    $this->db->where('room_id', $roomId);
    $this->db->where('question_id', $questionId);
    $query = $this->db->get('participant_question_scores'); // Replace 'question_scores' with your table name

    if ($query->num_rows() > 0) {
        // Update the existing record
        $this->db->where('participant_id', $participantId);
        $this->db->where('room_id', $roomId);
        $this->db->where('question_id', $questionId);
        $this->db->update('participant_question_scores', $data);
    } else {
        // Insert a new record
        $this->db->insert('participant_question_scores', $data);
    }
}


*/

    public function is_fill_in_the_blank($questionId) {
        // Query to check if the question is a fill-in-the-blank type
        $this->db->select('isFill');
        $this->db->from('questions');
        $this->db->where('id', $questionId);
        $query = $this->db->get();
        
        // Check if the question exists and retrieve the isFill value
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->isFill == 1; // Return TRUE if isFill is 1, otherwise FALSE
        }
        
        return FALSE; // Return FALSE if the question does not exist
    }

    /*
    public function save_participant_answer($participantId, $roomId, $questionId, $answerId, $answerText, $responseTime) {
        // Check if the answer is correct
        $isCorrect = $this->is_correct_answer($questionId, $answerId, $answerText);
        
        // Save the participant's answer with response time
        $data = [
            'user_id' => $participantId,
            'room_id' => $roomId,
            'question_id' => $questionId,
            'answer_id' => $answerId,
            'answer_text' => $answerText,
            'is_correct' => $isCorrect ? 1 : 0,
            'response_time' => $responseTime,
            'created_at' => date('Y-m-d H:i:s') // Optional: track when the answer was saved
        ];
        
        $result = $this->db->insert('participant_answers', $data);
        if (!$result) {
            $error = $this->db->error();
            echo json_encode(['status' => 'error', 'message' => 'Failed to save answer: ' . $error['message']]);
            return;
        }
        
        // Return success response if needed
        echo json_encode(['status' => 'success', 'message' => 'Answer saved successfully']);
    }
    
    */

    public function save_participant_answer($participantId, $roomId, $questionId, $answerId, $answerText, $responseTime) {


        // Check if the answer is correct
        $isCorrect = $this->is_correct_answer($questionId, $answerId, $answerText);

        $correctAnswers = $this->get_correct_answers($questionId);

         // Convert the array of correct answers to a comma-separated string
         $correctAnswerString = implode(', ', array_column($correctAnswers, 'answer_text'));

        // Save the participant's answer with response time
        $answerData = [
            'user_id' => $participantId,
            'room_id' => $roomId,
            'question_id' => $questionId,
            'answer_id' => $answerId,
            'answer_text' => $answerText,
            'is_correct' => $isCorrect ? 1 : 0,
            'response_time' => $responseTime,
            'created_at' => date('Y-m-d H:i:s') // Optional: track when the answer was saved
        ];
    
        // Insert into participant_answers table
        $result = $this->db->insert('participant_answers', $answerData);
        if (!$result) {
            $error = $this->db->error();
            echo json_encode(['status' => 'error', 'message' => 'Failed to save answer: ' . $error['message']]);
            return;
        }
    
        // Log the participant's answer into quiz_logs table
        $logData = [
            'participant_id' => $participantId,
            'question_id' => $questionId,
            'answer_text' => $answerText,
            'correct_answer' => $correctAnswerString,
            'is_correct' => $isCorrect ? 1 : 0,
            'response_time' => $responseTime,
            'created_at' => date('Y-m-d H:i:s')
        ];
    
        $logResult = $this->db->insert('quiz_logs', $logData);
        if (!$logResult) {
            $error = $this->db->error();
            echo json_encode(['status' => 'error', 'message' => 'Failed to log answer: ' . $error['message']]);
            return;
        }
    
        // Return success response if needed
        echo json_encode(['status' => 'success', 'message' => 'Answer and log saved successfully']);
    }
    
    
    public function calculate_score($participantId, $roomId) {
        // Fetch all answers submitted by the participant for this room
        $this->db->select('a.question_id, a.answer_id, a.response_time, a.is_correct, q.time');
        $this->db->from('participant_answers a');
        $this->db->join('questions q', 'a.question_id = q.id', 'left');
        $this->db->where('a.room_id', $roomId);
        $this->db->where('a.user_id', $participantId);
        $userAnswers = $this->db->get()->result();
        
        $totalScore = 0;
        
        // Calculate score for each question
        foreach ($userAnswers as $answer) {
            if ($answer->is_correct) {
                $timeLimit = $answer->time;
                $responseTime = $answer->response_time;
                
                // Calculate percentage based on the response time and time limit
                $percentage = 0;
    
                if ($responseTime <= 0.25 * $timeLimit) { // 25% of the time limit or less
                    $percentage = 1; // 100% of the base points
                } elseif ($responseTime <= 0.50 * $timeLimit) { // 50% of the time limit or less
                    $percentage = 0.75; // 75% of the base points
                } elseif ($responseTime <= 0.75 * $timeLimit) { // 75% of the time limit or less
                    $percentage = 0.50; // 50% of the base points
                } else {
                    $percentage = 0.25; // 25% of the base points
                }
                
                // Base points for a correct answer
                $basePoints = 100;
                $scoreAdded = $basePoints * $percentage;
    
                // Insert or update score in participant_question_scores table
                $this->db->where('participant_id', $participantId);
                $this->db->where('room_id', $roomId);
                $this->db->where('question_id', $answer->question_id);
                $query = $this->db->get('participant_question_scores');
    
                if ($query->num_rows() > 0) {
                    // Update existing record
                    $this->db->where('participant_id', $participantId);
                    $this->db->where('room_id', $roomId);
                    $this->db->where('question_id', $answer->question_id);
                    $this->db->update('participant_question_scores', array(
                        'score_added' => $scoreAdded,
                    ));
                } else {
                    // Insert new record
                    $this->db->insert('participant_question_scores', array(
                        'participant_id' => $participantId,
                        'room_id' => $roomId,
                        'question_id' => $answer->question_id,
                        'score_added' => $scoreAdded,
                        'created_at' => date('Y-m-d H:i:s')
                    ));
                }
    
                // Add to total score
                $totalScore += $scoreAdded;
            }
        }
        
        return $totalScore;
    }  

    public function get_logs_by_room($roomId, $participantId = null) {
        $this->db->select('quiz_logs.*, participants.name as participant_name, questions.question_text, 
                           answers.answer_text as correct_answer, quiz_logs.answer_text, quiz_logs.is_correct, 
                           quiz_logs.response_time');
        $this->db->from('quiz_logs');
        $this->db->join('participants', 'quiz_logs.participant_id = participants.id');
        $this->db->join('questions', 'quiz_logs.question_id = questions.id');
        $this->db->join('answers', 'questions.id = answers.question_id AND answers.is_correct = 1', 'left');
        $this->db->where('participants.room_id', $roomId); // Filter by room ID
        
        if ($participantId !== null) {
            $this->db->where('quiz_logs.participant_id', $participantId); // Filter by participant ID if provided
        }
        
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_logs_by_room_and_participant($roomId, $participantId) {
        $this->db->select('quiz_logs.*, participants.name as participant_name, questions.question_text');
        $this->db->from('quiz_logs');
        $this->db->join('participants', 'quiz_logs.participant_id = participants.id');
        $this->db->join('questions', 'quiz_logs.question_id = questions.id');
        $this->db->where('participants.room_id', $roomId);
        $this->db->where('quiz_logs.participant_id', $participantId); // Filter by participant ID
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    
    
    

    

    public function get_question_score($userId, $roomId, $questionId) {
        $this->db->select('score');
        $this->db->from('participant_question_scores');
        $this->db->where('user_id', $userId);
        $this->db->where('room_id', $roomId);
        $this->db->where('question_id', $questionId);
        $query = $this->db->get();
        $result = $query->row();
        return $result ? $result->score : 0;
    }

    /*
    public function get_user_score($participantId, $roomId) {
        // Log the parameters for debugging
        log_message('info', "Fetching score for participant: $participantId in room: $roomId");
        $this->db->select('score');
        $this->db->from('participant_scores');
        $this->db->where('participant_id', $participantId);
        $this->db->where('room_id', $roomId);
        $query = $this->db->get();
    
        // Log the number of rows found
        log_message('info', "Number of rows returned: " . $query->num_rows());
    
        if ($query->num_rows() > 0) {
            $score = $query->row()->score;
            // Log the score retrieved
            log_message('info', "Score found: $score");
            return $score;
        } else {
            log_message('info', "No score found for participant: $participantId in room: $roomId");
            return 0; // Return 0 if no score is found
        }
    }  
*/

    public function save_room($roomId, $pin) {
        // Insert room_id, PIN, and isValid into rooms table
        $data = array(
            'room_id' => $roomId,
            'pin' => $pin,
            'isValid' => 1
        );
    
        $this->db->insert('rooms', $data);
    
        return ($this->db->affected_rows() > 0);
    }    

    public function get_participants($room_pin) {
        // Check if room is valid
        if ($this->is_room_valid($room_pin)) {
            // Fetch participants from the database based on room_pin
            $this->db->where('room_pin', $room_pin);
            $query = $this->db->get('participants'); // Adjust table name and column names as needed
            
            return $query->result_array();
        } else {
            // Return an empty array or handle invalid room case
            return [];
        }
    }
    
    public function is_player_name_exists($name, $room_pin) {
        $this->db->where('name', $name);
        $this->db->where('room_pin', $room_pin);
        $query = $this->db->get('participants');
        
        return $query->num_rows() > 0;
    }

    public function getparticipantdata($name, $room_pin) {
        $this->db->select('a.id');
        $this->db->where('name', $name);
        $this->db->where('room_pin', $room_pin);
        $query = $this->db->get('participants as a');
        
        return $query->row_array();
    }

    public function validate_room_pin($room_pin) {
        // Ensure the room_pin is properly sanitized/validated if necessary
        $this->db->where('pin', $room_pin);
        $query = $this->db->get('rooms');
        
        if ($query->num_rows() > 0) {
            $result = $query->row_array(); // Get the row as an associative array
            return [
                'isValid' => $result['isValid'],
                'hasStarted' => $result['hasStarted'],
                'room_id' => $result['room_id'] // Get the room_id
            ];
        } else {
            return [
                'isValid' => 0,
                'hasStarted' => 0,
                'room_id' => null
            ];
        }
    }
    
    public function process_join($name, $room_pin, $room_id) {
        // Check if the name already exists for this room_pin
        $original_name = $name;
        $counter = 1;
        
        while ($this->is_player_name_exists($name, $room_pin)) {
            // Append the counter to the original name
            $name = $original_name . $counter;
            $counter++;
        }
        
        // Insert the unique player name into the 'participants' table along with room_id
        $data = array(
            'name' => $name,
            'room_pin' => $room_pin,
            'room_id' => $room_id // Store room_id along with the player
        );
        
        $this->db->insert('participants', $data);
        
        // Return the unique player name
        return $name;
    }    
    
    public function add_players_to_score_question($name, $room_pin){
        $this->db->select('name');
        $this->db->where('pin', $room_pin);
        $query = $this->db->get('participants');
        $result = $query->row_array();

        $data = array(
            'name' => $name,
            'room_pin' => $room_pin
        );
        
        $this->db->insert('participants', $data);
    }

    public function invalidate_room($roomId) {
        $data = array('isValid' => 0);
        $this->db->where('room_id', $roomId);
        $this->db->update('rooms', $data);
    
        return ($this->db->affected_rows() > 0);
    }
    
    public function left_participant($playerName, $roomPin) {
        $this->db->where('name', $playerName);
        $this->db->where('room_pin', $roomPin);
        $this->db->delete('participants');
    }

    public function exit_all_participants($roomPin){
        $this->db->where('room_pin', $roomPin);
        $this->db->delete('participants');
    }

    public function delete_room_questions($roomId) {
        $this->db->where('room_id', $roomId);
        $this->db->delete('questions');
    }

    public function is_room_valid($roomPin) {
        $this->db->select('isValid');
        $this->db->where('pin', $roomPin);
        $query = $this->db->get('rooms');
        $result = $query->row_array();
    
        return isset($result['isValid']) ? $result['isValid'] : null;
    }

    public function get_user_answers($roomId, $userId) {
        $this->db->select('question_id, id');
        $this->db->from('participant_answers'); // Replace with your actual table name
        $this->db->where('room_id', $roomId);
        $this->db->where('user_id', $userId);
        $query = $this->db->get();
        return $query->result();
    }

    // Fetch all Players with Final Scores
    public function get_all_players_final_scores($room_id) {
        $this->db->select('p.name, ps.score');
        $this->db->from('participant_scores ps');
        $this->db->join('participants p', 'p.id = ps.participant_id'); // Join the participants table
        $this->db->where('ps.room_id', $room_id);
        $this->db->order_by('ps.score', 'DESC');
        $query = $this->db->get();

        // Return the result as an array
        return $query->result_array();
    }

    // Fetch a players
    public function get_players() {
        $this->db->select('name');
        $query = $this->db->get('participants');
        return $query->row_array();
    }

    // Fetch Room id by Room Pin
    public function get_room_id_by_pin($roomPin) {
        $this->db->select('room_id');
        $this->db->from('rooms');
        $this->db->where('pin', $roomPin);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->room_id; // Return the room ID
        } else {
            return null; // No matching room pin found
        }
    }

    // Fetch all question based on room_id
    public function get_questions_by_room($room_id) {
        $this->db->select('id, question_text, time, isFill');
        $this->db->where('room_id', $room_id);
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('questions');
        return $query->result_array();
    }

    // Fetch answers for a given question
    public function get_answers_by_question($question_id) {
        $this->db->select('id, answer_text');
        $this->db->where('question_id', $question_id);
        $this->db->order_by('RAND()');
        $query = $this->db->get('answers');
        return $query->result_array();
    }    

    public function get_answer_text($answerId, $questionId) {
        // Select the answer text from the answers table
        $this->db->select('answer_text');
        $this->db->from('answers');
        $this->db->where('id', $answerId);
        $this->db->where('question_id', $questionId);
        
        $query = $this->db->get();
        
        // Check if the query returns any rows
        if ($query->num_rows() > 0) {
            return $query->row()->answer_text; // Return the answer text
        } else {
            return null; // Return null if no result found
        }
    }
    

    public function is_correct_answer($questionId, $answerId = null, $answerText = null) {
        // Check if the question is a fill-in-the-blank type
        $this->db->select('isFill');
        $this->db->from('questions');
        $this->db->where('id', $questionId);
        $query = $this->db->get();
        
        if ($query->num_rows() == 0) {
            return false; // Question not found
        }
        
        $question = $query->row();
        
        if ($question->isFill == 1) {
            // Handle fill-in-the-blank question
            $this->db->select('answer_text');
            $this->db->from('answers');
            $this->db->where('question_id', $questionId);
            $this->db->where('is_correct', 1);
            $query = $this->db->get();
            
            if ($query->num_rows() > 0) {
                $correctAnswer = $query->row();
                return strtolower(trim($answerText)) === strtolower(trim($correctAnswer->answer_text));
            }
            
            return false; // No correct answer found
        } else {
            // Handle multiple-choice question
            $this->db->where('question_id', $questionId);
            $this->db->where('id', $answerId);
            $this->db->where('is_correct', 1);
            $query = $this->db->get('answers');
            
            return $query->num_rows() > 0;
        }
    }
    
    public function get_correct_answers($question_id) {
        $this->db->select('answer_text');
        $this->db->select('id'); // Select the answer ID
        $this->db->where('question_id', $question_id);
        $this->db->where('is_correct', 1);
        $query = $this->db->get('answers');
        
        $results = $query->result_array(); // Get the results as an array
    
        // Encrypt the IDs and answer texts using AES
        foreach ($results as &$result) {
            $result['answer_text'] = $this->encrypt($result['answer_text']); // Encrypt the answer
            $result['id'] = $this->encrypt($result['id']); // Encrypt the ID
        }
        
        return $results; // Returns array of associative arrays with encrypted 'id' and 'answer_text'
    }

    public function get_correct_answer($question_id) {
        $this->db->select('id, answer_text'); // Select both id and answer_text
        $this->db->where('question_id', $question_id);
        $this->db->where('is_correct', 1);
        $query = $this->db->get('answers');
        
        return $query->row_array(); // Return a single correct answer as an associative array
    }
    
    
    private function encrypt($data) {
        $key = 'ifXaX/iMr+h+VwCbRNxaJQ=='; // Your encryption key
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-cbc')); // Generate a random IV
        $encryptedData = openssl_encrypt($data, 'aes-128-cbc', base64_decode($key), OPENSSL_RAW_DATA, $iv);
        
        // Return the IV and encrypted data, both base64-encoded
        return base64_encode($iv . $encryptedData); // Concatenate IV and encrypted data
    }
    
    private function decrypt($data) {
        $key = 'ifXaX/iMr+h+VwCbRNxaJQ=='; // Your encryption key
        $data = base64_decode($data); // Decode the base64 data
    
        // Extract the IV and encrypted data
        $ivLength = openssl_cipher_iv_length('aes-128-cbc');
        $iv = substr($data, 0, $ivLength); // Get the IV
        $encryptedData = substr($data, $ivLength); // Get the encrypted data
    
        return openssl_decrypt($encryptedData, 'aes-128-cbc', base64_decode($key), OPENSSL_RAW_DATA, $iv); // Decrypt using the extracted IV
    }       

    public function get_image_path($questId) {
        // Example query to get the image path
        $this->db->select('image_path');
        $this->db->from('questions');
        $this->db->where('id', $questId);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row()->image_path; // Return the image path
        }
        
        return ''; // Return empty if no path found
    }

    public function get_participant_id($playerName, $roomId) {
        $this->db->select('id');
        $this->db->from('participants');
        $this->db->where('name', $playerName);
        $this->db->where('room_id', $roomId);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->id;
        } else {
            return false;
        }
    }

    public function save_participant_question_score($participantId, $roomId, $questionId) {
    // Check if the entry already exists
    $this->db->where('participant_id', $participantId);
    $this->db->where('room_id', $roomId);
    $this->db->where('question_id', $questionId);
    $query = $this->db->get('participant_question_scores');

    // If the entry exists, do not insert a new row
    if ($query->num_rows() > 0) {
        return false; // Or return a message indicating that the record already exists
    }

    // If the entry does not exist, insert the new row
    $data = [
        'participant_id' => $participantId,
        'room_id' => $roomId,
        'question_id' => $questionId
    ];

    return $this->db->insert('participant_question_scores', $data);
}

}
?>
