<!DOCTYPE html>
<html>
<head>

    <title>Fixture Search</title>
    <style>
        table {
            border-collapse: collapse;
            width: 150%;
        }

        th, td {
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination-link, .read-more-btn {
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
            cursor: pointer;
        }

        .pagination-link.active {
            background-color: #4CAF50;
            color: white;
        }

        .pagination-link:hover:not(.active) {
            background-color: #ddd;
        }

        @media screen and (min-width: 768px) {
            table {
                width: 90%;
                padding: 20px;
            }
        }
    </style>
     
</head>
<body>
<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fixtures";
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("could not connect:" . mysqli_connect_error());
}

$searchValue = isset($_POST['searchValue']) ? $_POST['searchValue'] : '';
$searchColumn = isset($_POST['searchColumn']) ? $_POST['searchColumn'] : '';

// Prevent SQL injection
$searchValue = mysqli_real_escape_string($conn, $searchValue);
$searchColumn = mysqli_real_escape_string($conn, $searchColumn);

// Construct the search query with OR condition to search in both HomeTeam and AwayTeam
$sql = "SELECT `Date`, `Time`, `HomeTeam`, `AwayTeam`, `FTHG`, `FTAG`, `FTR` FROM p2223 
        WHERE `$searchColumn` = '$searchValue' OR `AwayTeam` = '$searchValue' 
        UNION
        SELECT `Date`, `Time`, `HomeTeam`, `AwayTeam`, `FTHG`, `FTAG`, `FTR` FROM p2122 
        WHERE `$searchColumn` = '$searchValue' OR `AwayTeam` = '$searchValue' 
        UNION
        SELECT `Date`, `Time`, `HomeTeam`, `AwayTeam`, `FTHG`, `FTAG`, `FTR` FROM p2021 
        WHERE `$searchColumn` = '$searchValue' OR `AwayTeam` = '$searchValue' 
        ORDER BY STR_TO_DATE(`Date`, '%d/%m/%Y') DESC, STR_TO_DATE(`Time`, '%H:%i') DESC"; // Order by Date and Time in descending order
$retval = mysqli_query($conn, $sql);

if (!$retval) {
    die("Error in search query: " . mysqli_error($conn));
}

// Count the total number of games found
$totalGames = mysqli_num_rows($retval);

// Set the number of matches to display per table
$matchesPerPage = 15;

// Calculate the number of tables required to display all matches
$numTables = ceil($totalGames / $matchesPerPage);

// Get the current table number from the URL parameter
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Calculate the starting index and ending index of matches to display in the current table
$startIndex = ($currentPage - 1) * $matchesPerPage;
$endIndex = min($startIndex + $matchesPerPage - 1, $totalGames - 1);

// Count the number of games where Over 1.5 condition is met
$over15Count = 0;

// Count the number of games where both teams scored (GG)
$ggCount = 0;

// Count the number of games where Over 2.5 condition is met
$over25Count = 0;

// Count the number of games where NO GG (Both teams did not score)
$noGGCount = 0;

// Check if Over 1.5 condition is met for each match and if both teams scored (GG)
while ($row = mysqli_fetch_assoc($retval)) {
    // Check if Over 1.5 condition is met for either FTHG or FTAG
    if ($row['FTHG'] + $row['FTAG'] >= 2) {
        $over15Count++;
    }

    // Check if both teams scored (GG)
    if ($row['FTHG'] >= 1 && $row['FTAG'] >= 1) {
        $ggCount++;
    }

    // Check if Over 2.5 condition is met (FTHG + FTAG >= 3)
    if ($row['FTHG'] + $row['FTAG'] >= 3) {
        $over25Count++;
    }

    // Check if NO GG (Both teams did not score)
    if ($row['FTHG'] == 0 || $row['FTAG'] == 0) {
        $noGGCount++;
    }
}

// Calculate the percentage of Over 1.5 matches, GG, Over 2.5 matches, and NO GG with one decimal point
$over15Percentage = number_format(($totalGames > 0) ? ($over15Count / $totalGames) * 100 : 0, 1);
$ggPercentage = number_format(($totalGames > 0) ? ($ggCount / $totalGames) * 100 : 0, 1);
$over25Percentage = number_format(($totalGames > 0) ? ($over25Count / $totalGames) * 100 : 0, 1);
$noGGPercentage = number_format(($totalGames > 0) ? ($noGGCount / $totalGames) * 100 : 0, 1);

// Calculate the Under 1.5 and Under 2.5 percentages
$under15Percentage = number_format(100 - $over15Percentage, 1);
$under25Percentage = number_format(100 - $over25Percentage, 1);

// Display the name of the team clicked on the center top of the table
echo "<h2>Team : $searchValue</h2>";

// Display the total number of games found and the percentages of Over 1.5, GG, Over 2.5, and NO GG matches with gray background
echo "<table><tr><td align='center' bgcolor='#CC6666'><h2>Total Games: $totalGames</h2></td></tr></table>";
echo "<table><tr><td bgcolor='#99FF00'><h2>Over 1.5: $over15Percentage%</h2></td>" .
    "<td bgcolor='#FF9900'><h2>Under 1.5: $under15Percentage%</h2></td></tr></table>";
echo "<table><tr><td bgcolor='#99FF00'><h2>Over 2.5: $over25Percentage%</h2></td>" .
    "<td bgcolor='#FF9900'><h2>Under 2.5: $under25Percentage%</h2></td></tr></table>";
echo "<table><td bgcolor='#99FF00'><h2>GG: $ggPercentage%</h2></td>" .
    "<td bgcolor='#FF9900'><h2>NO GG: $noGGPercentage%</h2></td></table>";

// Display the search results in a table
echo "<table border='1'> " .
    "<tr>" .
    "<th>Date</th>" .
    "<th>Time</th>" .
    "<th>Home Team</th>" .
    "<th>Away Team</th>" .
    "<th>Full Time Home Goals</th>" .
    "<th>Full Time Away Goals</th>" .
    "<th>Full Time Results</th>" .
    "</tr>";

// Reset the result set to display the search results again
mysqli_data_seek($retval, 0);

// Loop through the matches to display in the current table
for ($i = 0; $i <= $endIndex; $i++) {
    $row = mysqli_fetch_assoc($retval);
    if (!$row) {
        break; // No more matches to display
    }

    $homeTeam = $row['HomeTeam'];
    $awayTeam = $row['AwayTeam'];
    $ftr = $row['FTR'];

    // Determine the background color based on the conditions
    $backgroundColor = '';
    if ($homeTeam === $searchValue && $ftr === 'H') {
        $backgroundColor = 'bgcolor="#00ff00"'; // Green background for HomeTeam and FTR = H
    } elseif ($awayTeam === $searchValue && $ftr === 'A') {
        $backgroundColor = 'bgcolor="#00ff00"'; // Green background for AwayTeam and FTR = A
    } elseif ($ftr === 'D') {
        $backgroundColor = 'bgcolor="#b4b4b4"'; // Grey background for FTR = D
    } else {
        $backgroundColor = 'bgcolor="#ff0000"'; // Red background for other cases
    }

    echo "<tr>" .
        "<td>{$row['Date']}</td>" .
        "<td>{$row['Time']}</td>" .
        "<td bgcolor='#dbaf97'>{$row['HomeTeam']}</td>" .
        "<td bgcolor='#b4b4b4'>{$row['AwayTeam']}</td>" .
        "<td>{$row['FTHG']}</td>" .
        "<td>{$row['FTAG']}</td>" .
        "<td $backgroundColor>{$row['FTR']}</td>" .
        "</tr>";
}

echo "</table>";

// Display the "Read More" button to load additional data
if ($endIndex < $totalGames - 1) {
    echo "<div class='pagination-container'>";
    echo "<a class='read-more-btn' onclick='loadMoreData()'>Read More</a>";
    echo "</div>";
}

mysqli_close($conn);
?>

<!-- JavaScript to handle "Read More" button functionality -->
<script>
    function loadMoreData() {
        let currentPage = <?php echo $currentPage; ?>;
        window.location.href = "?page=" + (currentPage + 1);
    }
</script>

</body>
</html>
