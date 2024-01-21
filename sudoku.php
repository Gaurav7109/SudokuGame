<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku Game</title>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column; 
            height: 100vh;
            margin: 0;
            background: url('https://i.postimg.cc/hv3Mz5sf/output-onlinepngtools-1.png') no-repeat;
            background-size: cover; 
        }

        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 450px;
            margin: 10px;
            padding: 10px;
        }

        td {
            width: 45px;
            height: 45px;
            text-align: center;
            border: 2px solid black;
            font-size: 18px;
            font-weight: bold;
        }

        input {
            width: 100%;
            height: 100%;
            font-size: 18px;
            text-align: center;
            border: none;
            outline: none;
            background-color: #ddd;
            font-weight: bold;
        }

        input:empty {
            background-color: transparent;
        }

        #incorrectCounter {
            font-size: 14px;
            display: block;
            margin: 10px auto;
            color: skyblue;
            padding: 10px 20px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            color: #3498db;
        }

        #heading {
            text-align: center;
            font-size: 36px;
            font-weight: bold;
        }

        .button {
            display: block;
            margin: 20px auto;
            padding: 15px 30px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border: 2px solid #3498db;
            color: #3498db;
            background-color: #fff;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        }

        .button:hover {
            background-color: #3498db;
            color: #fff;
            border-color: #fff;
        }

        .reset-button {
            display: block;
            margin: 20px auto;
            padding: 15px 30px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border: 2px solid #e74c3c;
            color: #e74c3c;
            background-color: #fff;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        }

        .reset-button:hover {
            background-color: #e74c3c;
            color: #fff;
            border-color: #fff;
        }

        @media only screen and (max-width: 600px) {
            table {
                width: 100%;
                max-width: none;
            }

            td {
                width: 30px;
                height: 30px;
                font-size: 14px;
            }

            input {
                font-size: 14px;
            }

            #incorrectCounter {
                font-size: 12px;
                padding: 8px 16px;
            }

            .button, .reset-button {
                font-size: 18px;
                padding: 12px 24px;
            }
        }
    </style>
</head>
<body>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['startGame']) || isset($_POST['resetGame'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "sudoku_db";

    // $servername = "	sql107.infinityfree.com";
    // $username = "if0_35829092";
    // $password = "lWXQnNAxexGF";
    // $dbname = "if0_35829092_sudokuByGaurav";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (isset($_POST['resetGame'])) {
        $random = $_POST['resetGame'];
    } else {
        $random = rand(1, 11);
    }

    $sql = "SELECT * FROM sudoku_table WHERE id = $random";

    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $board = json_decode($row['board'], true);
            $solution = json_decode($row['solution'], true);

            echo '<input type="hidden" id="solution" value="' . htmlentities(json_encode($solution)) . '">';
            echo '<form method="post" action=""><table>';
            
            for ($i = 0; $i < 9; $i++) {
                echo '<tr>';
                for ($j = 0; $j < 9; $j++) {
                    $isInitialValue = $board[$i][$j] !== 0;
                    $cellValue = $isInitialValue ? $board[$i][$j] : '';
                    $readonlyAttribute = $isInitialValue ? 'readonly' : '';
                    echo '<td><input type="text" maxlength="1" value="' . $cellValue . '" ' . $readonlyAttribute . ' oninput="validateInput(this, ' . $solution[$i][$j] . ')" /></td>';
                }
                echo '</tr>';
            }
            
            echo '<div id="incorrectCounter">Mistakes Made: <span id="counter">0</span> /5</div>';
            echo '<button class="reset-button" type="submit" name="resetGame" value="' . $random . '">Reset Board</button>';
            echo '</table>';
            echo '<button class="reset-button" type="submit" name="startGame">New Board</button></form>';
        } else {
            echo "No rows found in the result set.";
        }
    } else {
        echo "Query failed with error: " . $conn->error;
    }

    $conn->close();
} else {
    echo '<div id="heading">Welcome to the World of SUDOKU...</div>';
    echo '<form method="post" action=""><button class="button" type="submit" name="startGame">Start Conquering Sudoku</button></form>';
}
?>
<script>
    let incorrectCount = 0;
    let solution;

    function validateInput(input, correctValue) {
        input.value = input.value.replace(/[^1-9]/g, '');

        if (input.value !== '' && parseInt(input.value) !== correctValue) {
            incorrectCount++;
            document.getElementById('counter').innerText = incorrectCount;

            var audio = new Audio('https://www.myinstants.com/media/sounds/wrong-answer-sound-effect.mp3');
            audio.play();

            input.parentNode.style.backgroundColor = 'rgba(255, 0, 0, 0.5)';
            if (incorrectCount >= 5) {
                alert('You Lose! You made 5 mistakes! Try with a new sudoku.');
                location.reload();
            }
        } else if (input.value !== '') {
            input.parentNode.style.backgroundColor = 'rgba(0, 255, 0, 0.5)';
        } else {
            input.parentNode.style.backgroundColor = '';
        }

        if ( isBoardComplete() && isBoardCorrect()) {
            var audio = new Audio('https://www.soundjay.com/human/sounds/applause-01.mp3');
            audio.play();

            function delayOneSec() {
                timeout = setTimeout(alertFunc, 1000);
            }

            function alertFunc() {
                alert('Congratulations! You won! Explore other boards');
            }
            delayOneSec();
        }
    }

    function isBoardComplete() {
        const inputs = document.querySelectorAll('input[type="text"]');
        for (const input of inputs) {
            if (input.value === '') {
                return false;
            }
        }
        return true;
    }

    function getSolution() {
        const solutionInput = document.getElementById('solution');
        return solutionInput ? JSON.parse(solutionInput.value) : null;
    } 

    function isBoardCorrect() {
        const userBoard = getFilledBoard();
        const solution = getSolution();
        return JSON.stringify(userBoard) === JSON.stringify(solution);
    }

    function getFilledBoard() {
        const inputs = document.querySelectorAll('input[type="text"]');
        const userBoard = [];

        let row = [];
        inputs.forEach((input, index) => {
            row.push(parseInt(input.value) || 0);
            if ((index + 1) % 9 === 0) {
                userBoard.push(row);
                row = [];
            }
        });
        return userBoard;
    }
</script>
</body>
</html>
