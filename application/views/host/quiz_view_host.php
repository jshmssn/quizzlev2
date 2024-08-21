<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiple Choice</title>
    
<!-- Bootstrap JS and dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js" integrity="sha384-rOA1PnstxnOBLzCLMcre8ybwbTmemjzdNlILg8O7z1lUkLXozs4DHonlDtnE7fpc" crossorigin="anonymous"></script>
    <!-- Sweetalert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url('assets/scripts/preventInspect.js')?>"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css')?>">
    <style>
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #cc0000;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            font-weight: bold;
            z-index: 9999;
            transition: opacity 1s ease-out;
        }
        .overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .countdown-timer {
            font-size: 3rem;
            font-weight: bold;
            transition: opacity 0.5s ease;
        }
        .overlay-text {
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        #question-text {
            font-size: 1.5rem;
            line-height: 1.4;
            text-align: left; /* Align text to the left */
            margin: 0; /* Remove margin to align with button */
        }
        .question-container {
            display: flex;
            align-items: center; /* Vertically center align items */
            gap: 10px; /* Space between question text and button */
        }
        #answers {
            display: flex;
            gap: 10px;
        }
        .btn.answer-btn {
            font-size: 1.1rem;
            width: 40%;
            padding: 16px;
        }
        .btn.selected {
            background-color: #28a745;
            color: #fff;
        }
        .btn.disabled {
            background-color: #cc0000;
            color: #fff;
            pointer-events: none;
        }
        .question-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #cc0000;
            border: 2px solid #cc0000;
            padding: 10px;
            border-radius: 8px;
            background-color: #fff;
            text-align: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }
        .image-container {
            display: flex;
            justify-content: center; /* Center horizontally */
            margin: 20px 0; /* Add vertical spacing */
        }
        .image-container img {
            max-width: 100%; /* Ensure responsiveness */
            height: auto; /* Maintain aspect ratio */
            border-radius: 10px;
        }
        .countdown-bar {
            width: 100%;
            height: 5px;
            background-color: #28a745;
            border-radius: 5px;
            margin-bottom: 10px;
            transition: width 1s linear;
        }
        .hidden {
            display: none;
        }
        .blue{
            background-color: #28a745 !important;
            opacity: 1 !important;
            cursor: not-allowed;
        }
        .player-list {
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }
        .player-list .card-header {
            background-color: #cc0000;
            color: #fff;
            text-align: center;
            font-size: 1.25rem;
            font-weight: bold;
        }
        .player-list .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .player-list .player-name {
            font-size: 1.1rem;
            font-weight: bold;
        }
        .player-list .player-score {
            font-size: 1.1rem;
            color: #28a745;
            font-weight: bold;
        }
        .player-list .list-group-item:last-child {
            border-bottom: none;
        }
        .player-list .host-item {
            background-color: #cc0000;
            color: #fff;
            border-radius: 8px;
        }
        #skip-button{
            position: absolute;
            right: 0;
        }
        @media (max-width: 768px) {
            .player-list {
                margin-bottom: 20px;
            }
            #question-text {
                font-size: 1.25rem;
            }
            #answers {
                flex-direction: column; /* Stack answers vertically on mobile devices */
            }
            .btn.answer-btn {
                font-size: 1rem;
                width: 100%; /* Make buttons full width on mobile devices */
            }
            .swal2-html-container .ranking-table th, 
            .swal2-html-container .ranking-table td {
                font-size: 12px;
                padding: 8px;
            }
        }
        @media (max-width: 576px) {
            #question-text {
                font-size: 1rem;
            }
            .btn.answer-btn {
                font-size: 0.9rem;
            }
        }
        @media (max-width: 480px) {
            .swal2-html-container .ranking-table th, 
            .swal2-html-container .ranking-table td {
                font-size: 10px;
                padding: 5px;
            }
        }
        .hidden {
            display: none;
        }
        .swal2-html-container .ranking-table {
            margin: 20px auto;
            max-width: 100%;
        }

        .swal2-html-container .ranking-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .swal2-html-container .ranking-table th, 
        .swal2-html-container .ranking-table td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .swal2-html-container .ranking-table th {
            background-color: #f8f9fa;
        }

        .swal2-html-container .ranking-table tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<div class="overlay" id="overlay">
    <div class="overlay-text">Starting in:</div>
    <div class="countdown-timer" id="countdown-timer"></div>
</div>

<div class="container-fluid mt-3">
    <div class="row">
        <!-- Sidebar Container -->
        <div class="col-12 col-md-3">
            <div class="player-list card">
                <div class="card-header">
                    <h5 class="card-title">Overall Ranking</h5>
                </div>
                <ul class="list-group list-group-flush" id="player-list">
                    <!-- Host item -->
                    <!-- <li class="list-group-item host-item">
                        <span class="player-name">Host: 

                        </span>
                        <span class="player-score"></span>
                    </li> -->
                    <!-- Player items will be dynamically inserted here -->
                </ul>
            </div>
        </div>

        <!-- Main Content Container -->
        <div class="col-12 col-md-9">
            <div class="question-number">
                <div class="countdown-bar" id="countdown-bar"></div>
                <span id="question-number">Loading...</span>
            </div>
            <div class="card-body mt-3">
                <div class="question-container">
                    <h1 id="question-text" class="fit-text">Loading question...</h1>
                    <a id="speak-button" class="btn">
                        <i class="fa fa-volume-up"></i>
                    </a>
                    <a id="skip-button" class="btn">
                        <i class="fas fa-forward"></i>
                    </a>
                </div>
            </div>
            <div class="image-container">
                <!-- Image will be fetched from the database -->
                <img src="" class="img-fluid" alt="Question Image" />
            </div>
            <div id="answers" class="mt-3">
                <!-- Answer buttons will be dynamically inserted here -->
            </div>
            <div id="fill-in-the-blank" hidden>
                <input type="text" id="answer-input" class="form-control" placeholder="Type your answer here...">
                <button id="submit-answer" class="btn btn-light btn-block mt-2">Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function getRoomId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('roomId');
    }

    let roomId = getRoomId();

    fetchPlayers(roomId);
    setInterval(() => fetchPlayers(roomId), 5000); // Fetch players every 5 seconds

    function fetchPlayers(roomId) {
        $.ajax({
            url: '<?= site_url('main_controller/get_all_ranking') ?>',
            method: 'POST',  // Change to POST
            data: { roomId: roomId },
            dataType: 'json',
            success: function(data) {
                console.log("Room ID is: " + roomId);
                console.log("Response data:", data);
                updatePlayerList(data.players);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching players:", error);
            }
        });
    }

    function updatePlayerList(players) {
        const playerList = $('#player-list');
        // Clear previous player items except for the host
        playerList.find('li:not(.host-item)').remove();

        players.forEach((player, index) => {
            const listItem = $('<li class="list-group-item"></li>');
            const playerRank = $('<span class="player-rank"></span>').text(index + 1 + '. '); // Add ranking number
            const playerName = $('<span class="player-name"></span>').text(player.name);
            const playerScore = $('<span class="player-score"></span>').text(player.score);

            listItem.append(playerRank).append(playerName).append(playerScore);
            playerList.append(listItem);
        });
    }
});
</script>

<!-- WebSocket and AJAX Script -->
<script>
    const socketUrl = `${window.location.protocol === 'https:' ? 'wss' : 'ws'}://${window.location.hostname}:3000`;
    let socket = null;
    let isSocketOpen = false;
    let reconnectAttempts = 0;
    const maxReconnectAttempts = 5; // Adjust as needed

    function getRoomId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('roomId');
    }

    let roomId = getRoomId();

    function connectWebSocket() {
        socket = new WebSocket(socketUrl);

        socket.onopen = function() {
            console.log('WebSocket connection established.');
            isSocketOpen = true;
            reconnectAttempts = 0; // Reset attempts on successful connection
        };

        socket.onclose = function() {
            console.log('WebSocket connection closed.');
            isSocketOpen = false;
            if (reconnectAttempts < maxReconnectAttempts) {
                reconnectAttempts++;
                setTimeout(connectWebSocket, 3000); // Attempt to reconnect after 3 seconds
            } else {
                console.error('Max reconnect attempts reached. Please refresh the page.');
            }
        };

        socket.onerror = function(error) {
            console.error('WebSocket Error:', error);
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        connectWebSocket();

        const overlay = document.getElementById('overlay');
        const countdownTimer = document.getElementById('countdown-timer');
        const questionTextElement = document.getElementById('question-text');
        const answersElement = document.getElementById('answers');
        const speakButton = document.getElementById('speak-button');
        const questionNumberElement = document.getElementById('question-number');
        const countdownBar = document.getElementById('countdown-bar');
        const fillInTheBlankContainer = document.getElementById('fill-in-the-blank');
        const answerInput = document.getElementById('answer-input');
        const submitAnswerButton = document.getElementById('submit-answer');

        if (!roomId) {
            console.error('Room id is required.');
            return;
        }

        let countdown = 5;
        const countdownInterval = setInterval(() => {
            if (countdown > 0) {
                countdownTimer.textContent = countdown;
            }

            countdown--;

            if (countdown < 0) {
                clearInterval(countdownInterval);

                overlay.style.transition = "opacity 1s ease-out";
                overlay.style.opacity = 0;

                setTimeout(() => {
                    overlay.classList.add('hidden');
                    fetchQuestions();
                }, 1000);
            }
        }, 1000);


        let questions = [];
        let currentQuestionIndex = 0;

        async function fetchQuestions() {
            try {
                const response = await $.ajax({
                    url: '<?= site_url('main_controller/fetch_questions')?>',
                    type: 'POST',
                    data: { room_id: roomId },
                    dataType: 'json'
                });
                if (response.status === 'success') {
                    questions = response.data;
                    if (questions.length > 0) {
                        displayQuestion(questions[currentQuestionIndex]);
                    } else {
                        console.error('No questions found.');
                    }
                } else {
                    console.error('Error:', response.message);
                }
            } catch (error) {
                console.error('AJAX error:', error);
            }
        }

        let questionId = null;
        // Function to display a question
        function displayQuestion(question) {
            if (!question) return;
            questionId = question.id;

            // console.log('Question Data:', question); // Debugging line

            // Display question text and number
            questionTextElement.textContent = question.question_text || 'Loading question...';
            questionNumberElement.textContent = `Question ${currentQuestionIndex + 1} out of ${questions.length}`;
            
            // Show image and fetch answers if necessary
            loadImage(question.id);
            fetchAnswers(question.id);
            fetchCorrectAnswers(question.id);
            startCountdown(question.time);

            // Ensure submitAnswerButton is enabled for the new question
            submitAnswerButton.removeAttribute('disabled');

            // Handle display based on question type
            if (question.isFill === '1') {
                answersElement.style.display = 'none';
                fillInTheBlankContainer.removeAttribute('hidden');
                fillInTheBlankContainer.style.display = 'block'; // Ensure it is visible
                submitAnswerButton.removeEventListener('click', handleFillInTheBlankAnswer); // Remove previous handlers if any
                submitAnswerButton.addEventListener('click', () => handleFillInTheBlankAnswer(answerInput.value, question.id));
            } else {
                fillInTheBlankContainer.style.display = 'none'; // Hide fill-in-the-blank section
                answersElement.style.display = 'flex';
            }
        }

        function handleFillInTheBlankAnswer(answerText, questionId) {
            const waitingMessage = document.getElementById('waitingMessage');
            waitingMessage.removeAttribute('hidden');
            waitingMessage.style.display = 'block';

            if (isSocketOpen) {
                const data = {
                    type: 'answer_selected',
                    answerText: answerText,
                    questionId: questionId,
                    roomId: roomId
                };
                socket.send(JSON.stringify(data));
                console.log('Selected answer data sent via WebSocket:', data);
            } else {
                console.error('Socket connection is not open.');
            }

            answerInput.value = '';
            fillInTheBlankContainer.setAttribute('hidden', 'true');
            waitingMessage.style.display = 'block';
        }

        async function fetchAnswers(questionId) {
            try {
                const response = await $.ajax({
                    url: '<?= site_url('main_controller/fetch_answers')?>',
                    type: 'POST',
                    data: { question_id: questionId },
                    dataType: 'json'
                });
                if (response.status === 'success') {
                    // console.log('Answers:', response.data);

                    answersElement.innerHTML = '';

                    response.data.forEach(answer => {
                        const answerButton = document.createElement('button');
                        answerButton.classList.add('btn', 'answer-btn', 'red', 'btn-block');
                        answerButton.textContent = answer.answer_text;

                        answerButton.addEventListener('click', function() {
                            handleAnswerSelection(answerButton, answer.id, questionId);
                        });

                        answersElement.appendChild(answerButton);
                    });
                } else {
                    console.error('Error:', response.message);
                }
            } catch (error) {
                console.error('AJAX error:', error);
            }
        }

        async function fetchCorrectAnswers(questionId) {
            try {
                const response = await $.ajax({
                    url: '<?= site_url('main_controller/fetch_correct_answers')?>',
                    type: 'POST',
                    data: { question_id: questionId },
                    dataType: 'json'
                });

                if (response.status === 'success') {
                    // console.log('Correct Answer:', response.data);                    

                } else {
                    console.error('Error:', response.message);
                }
            } catch (error) {
                console.error('AJAX error:', error);
            }
        }

        function startCountdown(duration, questionId, roomId) {
            let timeLeft = duration;

            // Retrieve the remaining time from localStorage if it exists
            const storedTime = localStorage.getItem('countdownTime');
            if (storedTime) {
                timeLeft = parseInt(storedTime, 10);
                localStorage.removeItem('countdownTime'); // Remove the stored time after use
            }

            const countdownBar = document.querySelector('.countdown-bar');
            const submitAnswerButton = document.getElementById('submit-answer'); // Ensure you have this element

            // Function to update the countdown bar width and remaining time display
            function updateCountdown() {
                const width = (timeLeft / duration) * 100 + '%';
                countdownBar.style.width = width;
                // Optionally update a display element with timeLeft here
            }

            // Start the countdown
            const countdownInterval = setInterval(() => {
                updateCountdown();

                // Save the remaining time to localStorage
                localStorage.setItem('countdownTime', timeLeft);

                // Decrease the time left
                timeLeft--;

                // If time is up
                if (timeLeft < -1) {
                    clearInterval(countdownInterval);
                    submitAnswerButton.setAttribute('disabled', 'true');
                    showAnswer();

                    // Set a 3-second delay before fetching player data
                    setTimeout(() => {
                        // Fetch player data via AJAX
                        fetchPlayerData(questionId, roomId) // Pass the questionId and roomId
                            .then(players => {
                                // Generate the ranking table HTML with fetched players
                                const rankingTableHtml = generateRankingTable(players);

                                // Show the ranking table in SweetAlert2
                                Swal.fire({
                                    title: "Ranking per Question",
                                    html: rankingTableHtml, // Insert the ranking table here
                                    icon: "info",
                                    showConfirmButton: false, // Disable the confirm button
                                    timer: 10000, // The alert will close automatically after 10 seconds
                                    timerProgressBar: true, // Display a progress bar showing the countdown
                                    allowOutsideClick: false, // Disable closing the alert by clicking outside
                                    allowEscapeKey: false // Disable escape key
                                });

                                localStorage.removeItem('countdownTime'); // Clear the stored time
                            })
                            .catch(error => {
                                console.error('Error fetching player data:', error);
                                // Optionally, handle the error or show an error message
                                Swal.fire({
                                    title: "Error",
                                    text: "No player data on this question.",
                                    icon: "error",
                                    showConfirmButton: false, // Disable the confirm button
                                    timer: 10000, // The alert will close automatically after 10 seconds
                                    timerProgressBar: true, // Display a progress bar showing the countdown
                                    allowOutsideClick: false, // Disable closing the alert by clicking outside
                                    allowEscapeKey: false // Disable escape key
                                });
                            });
                    }, 3000); // 3000 milliseconds delay
                }

            }, 1000);

            // Initial update of the countdown bar
            updateCountdown();
        }

        // Function to generate the ranking table HTML
        function generateRankingTable(players) {
            let tableHtml = `
                <div class="ranking-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Name</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>`;

            // Sort players by score in descending order
            players.sort((a, b) => b.score - a.score);

            // Generate table rows
            players.forEach((player, index) => {
                tableHtml += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${player.name}</td>
                        <td>${player.score}</td>
                    </tr>`;
            });

            tableHtml += `
                        </tbody>
                    </table>
                </div>`;

            return tableHtml;
        }

        // AJAX function to fetch player data
        function fetchPlayerData($question_id = questionId, $room_id = roomId) {
            console.log('Fetching player data for question ID:', questionId); // Add this line
            console.log('Fetching player data for Room ID:', roomId);
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '<?= site_url('main_controller/fetch_players_score_per_q') ?>',
                    method: 'GET',
                    data: {
                        question_id: questionId,
                        room_id: roomId
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Raw response:', response);
                        if (response.status === 'success' && Array.isArray(response.players)) {
                            resolve(response.players);
                        } else {
                            console.error('Invalid response format or no players found:', response);
                            reject('Failed to fetch player data or invalid response format.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', xhr, status, error);
                        reject('AJAX request failed: ' + error);
                    }
                });
            });
        }

        function showAnswer() {
            const answerButtons = document.querySelectorAll('.btn.answer-btn');

            // Disable answer buttons
            answerButtons.forEach(btn => {
                btn.classList.add('disabled');
                btn.disabled = true;
            });

            // Move to the next question after a delay
            setTimeout(() => {                
                currentQuestionIndex++;
                if (currentQuestionIndex < questions.length) {
                    displayQuestion(questions[currentQuestionIndex]);
                } else {
                    // Handle the end of the quiz
                    // console.log('Quiz completed!');
                    // Swal.fire({
                    //     title: "Good job!",
                    //     text: "The quiz is now done.",
                    //     icon: "success",
                    //     confirmButtonText: "See overall ranking"
                    // }).then((result) => {
                    //     /* Read more about isConfirmed */
                    //     if (result.isConfirmed) {
                            
                    //     }
                    // });
                    // Refresh the page when OK is clicked
                    // if (confirm('Click OK to debug again')) {
                    //     location.reload();
                    // }
                }
            }, 13000); // Delay to show the correct answer before moving to the next question
        }

        
        function loadImage(questId) {
            $.ajax({
                url: '<?= site_url('main_controller/get_image_path') ?>',
                type: 'POST',
                dataType: 'json',
                data: { questId: questId },
                success: function(response) {
                    if (response.imagePath) {
                        // Set the image source and show the image container
                        $('.image-container img').attr('src', '<?= base_url() ?>' + response.imagePath);
                        $('.image-container').removeClass('hidden');
                    } else {
                        // Clear the image source and hide the image container
                        $('.image-container img').attr('src', '');
                        $('.image-container').addClass('hidden');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }

        function handleAnswerSelection(button, answerId, questionId) {
            const answerButtons = document.querySelectorAll('.btn.answer-btn');
            const waitingMessage = document.getElementById('waitingMessage');

            answerButtons.forEach(btn => {
                btn.classList.add('disabled');
                btn.disabled = true;
                waitingMessage.removeAttribute('hidden');
                waitingMessage.style.display = 'block';
            });

            button.classList.remove('disabled');
            button.classList.add('selected');

            if (isSocketOpen) {
                const data = {
                    type: 'answer_selected',
                    answerId: answerId,
                    questionId: questionId,
                    roomId: roomId
                };
                socket.send(JSON.stringify(data));
                console.log('Selected answer data sent via WebSocket:', data);
            } else {
                console.error('Socket connection is not open.');
            }
        }

        speakButton.addEventListener('click', () => {
            const questionText = questionTextElement.textContent;
            const speech = new SpeechSynthesisUtterance(questionText);

            // Set additional properties for better mobile compatibility
            speech.rate = 1; // Adjust the speech rate if needed
            speech.pitch = 1; // Adjust the pitch if needed

            // Ensure the synthesis stops any ongoing speech before starting
            speechSynthesis.cancel();
            
            speechSynthesis.speak(speech);
        });

        // Check for Speech Synthesis support
        if (!('speechSynthesis' in window)) {
            alert('Your browser does not support speech synthesis.');
        }

    });
</script>
</body>
</html>
