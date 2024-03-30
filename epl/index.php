<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fixtures";
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("could not connect:" . mysqli_connect_error());
}
echo "@www.daytips.xyz  //the data with 0% states that the team was not in the league from 2019<br/>";

// Modified SQL query to fetch data from eplfix table for this week
$firstDayOfWeek = date('d/m/Y', strtotime('monday this week'));
$lastDayOfWeek = date('d/m/Y', strtotime('sunday this week'));

$sql = "SELECT *,
        (SELECT COUNT(*) FROM p1920 WHERE HomeTeam = eplfix.HomeTeam OR AwayTeam = eplfix.HomeTeam) as `Home games`,
        (SELECT COUNT(*) FROM p2021 WHERE HomeTeam = eplfix.HomeTeam OR AwayTeam = eplfix.HomeTeam) as `p2021_HomeGames`,
        (SELECT COUNT(*) FROM p2122 WHERE HomeTeam = eplfix.HomeTeam OR AwayTeam = eplfix.HomeTeam) as `p2122_HomeGames`,
        (SELECT COUNT(*) FROM p2223 WHERE HomeTeam = eplfix.HomeTeam OR AwayTeam = eplfix.HomeTeam) as `p2223_HomeGames`,
        (SELECT COUNT(*) FROM p1920 WHERE HomeTeam = eplfix.HomeTeam AND FTHG > FTAG) +
        (SELECT COUNT(*) FROM p2021 WHERE HomeTeam = eplfix.HomeTeam AND FTHG > FTAG) +
        (SELECT COUNT(*) FROM p2122 WHERE HomeTeam = eplfix.HomeTeam AND FTHG > FTAG) +
        (SELECT COUNT(*) FROM p2223 WHERE HomeTeam = eplfix.HomeTeam AND FTHG > FTAG) as `HomeWin`,
        (SELECT COUNT(*) FROM p1920 WHERE (HomeTeam = eplfix.HomeTeam AND FTR = 'D') OR (AwayTeam = eplfix.HomeTeam AND FTR = 'D')) +
        (SELECT COUNT(*) FROM p2021 WHERE (HomeTeam = eplfix.HomeTeam AND FTR = 'D') OR (AwayTeam = eplfix.HomeTeam AND FTR = 'D')) +
        (SELECT COUNT(*) FROM p2122 WHERE (HomeTeam = eplfix.HomeTeam AND FTR = 'D') OR (AwayTeam = eplfix.HomeTeam AND FTR = 'D')) +
        (SELECT COUNT(*) FROM p2223 WHERE (HomeTeam = eplfix.HomeTeam AND FTR = 'D') OR (AwayTeam = eplfix.HomeTeam AND FTR = 'D')) as `Hdraw`,
        (SELECT COUNT(*) FROM p1920 WHERE (HomeTeam = eplfix.AwayTeam AND FTR = 'D') OR (AwayTeam = eplfix.AwayTeam AND FTR = 'D')) +
        (SELECT COUNT(*) FROM p2021 WHERE (HomeTeam = eplfix.AwayTeam AND FTR = 'D') OR (AwayTeam = eplfix.AwayTeam AND FTR = 'D')) +
        (SELECT COUNT(*) FROM p2122 WHERE (HomeTeam = eplfix.AwayTeam AND FTR = 'D') OR (AwayTeam = eplfix.AwayTeam AND FTR = 'D')) +
        (SELECT COUNT(*) FROM p2223 WHERE (HomeTeam = eplfix.AwayTeam AND FTR = 'D') OR (AwayTeam = eplfix.AwayTeam AND FTR = 'D')) as `Adraw`,
        (SELECT COUNT(*) FROM p1920 WHERE AwayTeam = eplfix.HomeTeam AND FTR = 'A') +
        (SELECT COUNT(*) FROM p2021 WHERE AwayTeam = eplfix.HomeTeam AND FTR = 'A') +
        (SELECT COUNT(*) FROM p2122 WHERE AwayTeam = eplfix.HomeTeam AND FTR = 'A') +
        (SELECT COUNT(*) FROM p2223 WHERE AwayTeam = eplfix.HomeTeam AND FTR = 'A') as `AwayWin`,
        (SELECT COUNT(*) FROM p1920 WHERE AwayTeam = eplfix.AwayTeam AND FTR = 'A') +
        (SELECT COUNT(*) FROM p2021 WHERE AwayTeam = eplfix.AwayTeam AND FTR = 'A') +
        (SELECT COUNT(*) FROM p2122 WHERE AwayTeam = eplfix.AwayTeam AND FTR = 'A') +
        (SELECT COUNT(*) FROM p2223 WHERE AwayTeam = eplfix.AwayTeam AND FTR = 'A') as `AWdraw`
        FROM eplfix 
        WHERE STR_TO_DATE(Date, '%d/%m/%Y') BETWEEN STR_TO_DATE('$firstDayOfWeek', '%d/%m/%Y') AND STR_TO_DATE('$lastDayOfWeek', '%d/%m/%Y')
        ORDER BY STR_TO_DATE(Date, '%d/%m/%Y') DESC";

$retval = mysqli_query($conn, $sql);

$groupedMatches = array();

// Group matches by month and year
while ($column = mysqli_fetch_assoc($retval)) {
    $dateParts = explode('/', $column['Date']);
    $month = $dateParts[1];
    $year = $dateParts[2];

    // Create a new array entry for the month and year if it doesn't exist
    if (!isset($groupedMatches[$year][$month])) {
        $groupedMatches[$year][$month] = array();
    }

    // Add the match data to the corresponding month and year entry
    $groupedMatches[$year][$month][] = $column;
}

// Display the grouped matches in separate tables
foreach ($groupedMatches as $year => $months) {
    foreach ($months as $month => $matches) {
        echo "<h1 style='font-size: 24px; font-weight: bold;'>EPL- English Premier League</h1><br />";
        echo "<h2>$month/$year</h2>";
        echo "<style>
table {
  border-collapse: collapse;
  width: 50%;
}

th, td {
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {background-color: #f2f2f2;}
</style>";

        echo "<table border='1'> " .
            "<tr>" .
            "<th bgcolor='#f68009'>Date</th>" .
            "<th bgcolor='#f68009''>Home Team</th>" .
            "<th bgcolor='#f68009'>Away Team ....</th>" .
            "<th bgcolor='#f68009'>Home win</th>" . // New table header for Home win column
            "<th bgcolor='#f68009'>Away win</th>" . // New table header for Away win column
            "<th bgcolor='#f68009'>Draw</th>" . // New table header for Draw column
            "<th bgcolor='#f68009'>GG-yes</th>" . // New table header for GG-yes column
            "<th bgcolor='#f68009'>NO-gg</th>" .  // New table header for NO-gg column
            "<th bgcolor='#f68009'>Over2.5</th>" . // New table header for Over2.5 column
             "<th bgcolor='#f68009'>Prediction</th>" . // New table header for Prediction column
    "</tr>";

        foreach ($matches as $match) {
            // Get the team names from HomeTeam and AwayTeam
            $homeTeam = $match['HomeTeam'];
            $awayTeam = $match['AwayTeam'];

            // Count the total games for the home team
            $sqlHomeGames = "SELECT 
                (SELECT COUNT(*) FROM p1920 WHERE HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') +
                (SELECT COUNT(*) FROM p2021 WHERE HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') +
                (SELECT COUNT(*) FROM p2122 WHERE HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') +
                (SELECT COUNT(*) FROM p2223 WHERE HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') as `HomeGames`";
            $resultHomeGames = mysqli_query($conn, $sqlHomeGames);
            $rowHomeGames = mysqli_fetch_assoc($resultHomeGames);
            $homeGames = $rowHomeGames['HomeGames'];

            // Count the total games for the away team
            $sqlAwayGames = "SELECT 
                (SELECT COUNT(*) FROM p1920 WHERE HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') +
                (SELECT COUNT(*) FROM p2021 WHERE HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') +
                (SELECT COUNT(*) FROM p2122 WHERE HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') +
                (SELECT COUNT(*) FROM p2223 WHERE HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') as `AwayGames`";
            $resultAwayGames = mysqli_query($conn, $sqlAwayGames);
            $rowAwayGames = mysqli_fetch_assoc($resultAwayGames);
            $awayGames = $rowAwayGames['AwayGames'];

            // Replace the value of homegames and awaygames with 100 if they are 0 or null
            $homeGames = ($homeGames === null || $homeGames === 0) ? 100 : $homeGames;
            $awayGames = ($awayGames === null || $awayGames === 0) ? 100 : $awayGames;

            // Count the total home wins for the team and multiply by 2
            $sqlHomeWin = "SELECT 
                ((SELECT COUNT(*) FROM p1920 WHERE HomeTeam = '$homeTeam' AND FTHG > FTAG) +
                (SELECT COUNT(*) FROM p2021 WHERE HomeTeam = '$homeTeam' AND FTHG > FTAG) +
                (SELECT COUNT(*) FROM p2122 WHERE HomeTeam = '$homeTeam' AND FTHG > FTAG) +
                (SELECT COUNT(*) FROM p2223 WHERE HomeTeam = '$homeTeam' AND FTHG > FTAG)) * 2 as `HomeWin`";
            $resultHomeWin = mysqli_query($conn, $sqlHomeWin);
            $rowHomeWin = mysqli_fetch_assoc($resultHomeWin);
            $homeWin = $rowHomeWin['HomeWin'];

            // Count the total away wins for the team and multiply by 2
            $sqlAwayWin = "SELECT 
                ((SELECT COUNT(*) FROM p1920 WHERE AwayTeam = '$awayTeam' AND FTAG > FTHG) +
                (SELECT COUNT(*) FROM p2021 WHERE AwayTeam = '$awayTeam' AND FTAG > FTHG) +
                (SELECT COUNT(*) FROM p2122 WHERE AwayTeam = '$awayTeam' AND FTAG > FTHG) +
                (SELECT COUNT(*) FROM p2223 WHERE AwayTeam = '$awayTeam' AND FTAG > FTHG)) * 2 as `AwayWin`";
            $resultAwayWin = mysqli_query($conn, $sqlAwayWin);
            $rowAwayWin = mysqli_fetch_assoc($resultAwayWin);
            $awayWin = $rowAwayWin['AwayWin'];

            // Count the total draws for the home team
            $sqlHDraw = "SELECT 
                (SELECT COUNT(*) FROM p1920 WHERE (HomeTeam = '$homeTeam' AND FTR = 'D') OR (AwayTeam = '$homeTeam' AND FTR = 'D')) +
                (SELECT COUNT(*) FROM p2021 WHERE (HomeTeam = '$homeTeam' AND FTR = 'D') OR (AwayTeam = '$homeTeam' AND FTR = 'D')) +
                (SELECT COUNT(*) FROM p2122 WHERE (HomeTeam = '$homeTeam' AND FTR = 'D') OR (AwayTeam = '$homeTeam' AND FTR = 'D')) +
                (SELECT COUNT(*) FROM p2223 WHERE (HomeTeam = '$homeTeam' AND FTR = 'D') OR (AwayTeam = '$homeTeam' AND FTR = 'D')) as `Hdraw`";
            $resultHDraw = mysqli_query($conn, $sqlHDraw);
            $rowHDraw = mysqli_fetch_assoc($resultHDraw);
            $hDraw = $rowHDraw['Hdraw'];

            // Count the total draws for the away team
            $sqlADraw = "SELECT 
                (SELECT COUNT(*) FROM p1920 WHERE (HomeTeam = '$awayTeam' AND FTR = 'D') OR (AwayTeam = '$awayTeam' AND FTR = 'D')) +
                (SELECT COUNT(*) FROM p2021 WHERE (HomeTeam = '$awayTeam' AND FTR = 'D') OR (AwayTeam = '$awayTeam' AND FTR = 'D')) +
                (SELECT COUNT(*) FROM p2122 WHERE (HomeTeam = '$awayTeam' AND FTR = 'D') OR (AwayTeam = '$awayTeam' AND FTR = 'D')) +
                (SELECT COUNT(*) FROM p2223 WHERE (HomeTeam = '$awayTeam' AND FTR = 'D') OR (AwayTeam = '$awayTeam' AND FTR = 'D')) as `Adraw`";
            $resultADraw = mysqli_query($conn, $sqlADraw);
            $rowADraw = mysqli_fetch_assoc($resultADraw);
            $aDraw = $rowADraw['Adraw'];

                        // Calculate GG1 (Both teams scoring at home)
            $sqlGG1 = "SELECT 
                (SELECT COUNT(*) FROM p1920 WHERE ((HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND FTHG > 0 AND FTAG > 0) OR
                                                ((HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND FTHG > 0 AND FTAG > 0)) +
                (SELECT COUNT(*) FROM p2021 WHERE ((HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND FTHG > 0 AND FTAG > 0) OR
                                                ((HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND FTHG > 0 AND FTAG > 0)) +
                (SELECT COUNT(*) FROM p2122 WHERE ((HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND FTHG > 0 AND FTAG > 0) OR
                                                ((HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND FTHG > 0 AND FTAG > 0)) +
                (SELECT COUNT(*) FROM p2223 WHERE ((HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND FTHG > 0 AND FTAG > 0) OR
                                                ((HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND FTHG > 0 AND FTAG > 0)) as `GG1`";
            $resultGG1 = mysqli_query($conn, $sqlGG1);
            $rowGG1 = mysqli_fetch_assoc($resultGG1);
            $gg1 = $rowGG1['GG1'];

            // Calculate GG2 (Both teams scoring away)
            $sqlGG2 = "SELECT 
                (SELECT COUNT(*) FROM p1920 WHERE ((HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND FTHG > 0 AND FTAG > 0) OR
                                                ((HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND FTHG > 0 AND FTAG > 0)) +
                (SELECT COUNT(*) FROM p2021 WHERE ((HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND FTHG > 0 AND FTAG > 0) OR
                                                ((HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND FTHG > 0 AND FTAG > 0)) +
                (SELECT COUNT(*) FROM p2122 WHERE ((HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND FTHG > 0 AND FTAG > 0) OR
                                                ((HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND FTHG > 0 AND FTAG > 0)) +
                (SELECT COUNT(*) FROM p2223 WHERE ((HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND FTHG > 0 AND FTAG > 0) OR
                                                ((HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND FTHG > 0 AND FTAG > 0)) as `GG2`";
            $resultGG2 = mysqli_query($conn, $sqlGG2);
            $rowGG2 = mysqli_fetch_assoc($resultGG2);
            $gg2 = $rowGG2['GG2'];

            // Calculate GG1% and GG2%
            $gg1Percentage = number_format(($homeGames > 0) ? ($gg1 / $homeGames) * 100 : 0, 1);
            $gg2Percentage = number_format(($awayGames > 0) ? ($gg2 / $awayGames) * 100 : 0, 1);

            // Calculate sum of GG1% and GG2%
            $sumGG = number_format($gg1Percentage + $gg2Percentage, 1);

            // Check if the match was counted in GG1 and exclude it from GG2 calculation
            if ($rowGG1['GG1'] > 0) {
                $gg2Percentage = 0;
                $sumGG = $gg1Percentage;
            }


            // Calculate NO-gg (100% - GG-yes value)
            $noGGValue = 100 - $sumGG;
            
            // Calculate Over2.5a percentage
    $sqlOver2_5a = "SELECT 
        ((SELECT COUNT(*) FROM p1920 WHERE (HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND (FTHG + FTAG) >= 3) +
        (SELECT COUNT(*) FROM p2021 WHERE (HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND (FTHG + FTAG) >= 3) +
        (SELECT COUNT(*) FROM p2122 WHERE (HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND (FTHG + FTAG) >= 3) +
        (SELECT COUNT(*) FROM p2223 WHERE (HomeTeam = '$homeTeam' OR AwayTeam = '$homeTeam') AND (FTHG + FTAG) >= 3)) as `Over2_5a`";
    $resultOver2_5a = mysqli_query($conn, $sqlOver2_5a);
    $rowOver2_5a = mysqli_fetch_assoc($resultOver2_5a);
    $over2_5a = $rowOver2_5a['Over2_5a'];
    $over2_5aPercentage = number_format(($homeGames > 0) ? ($over2_5a / $homeGames) * 100 : 0, 1);

    // Calculate Over2.5b percentage if not already counted in Over2.5a
    if ($over2_5a === 0) {
        $sqlOver2_5b = "SELECT 
            ((SELECT COUNT(*) FROM p1920 WHERE (HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND (FTHG + FTAG) >= 3) +
            (SELECT COUNT(*) FROM p2021 WHERE (HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND (FTHG + FTAG) >= 3) +
            (SELECT COUNT(*) FROM p2122 WHERE (HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND (FTHG + FTAG) >= 3) +
            (SELECT COUNT(*) FROM p2223 WHERE (HomeTeam = '$awayTeam' OR AwayTeam = '$awayTeam') AND (FTHG + FTAG) >= 3)) as `Over2_5b`";
        $resultOver2_5b = mysqli_query($conn, $sqlOver2_5b);
        $rowOver2_5b = mysqli_fetch_assoc($resultOver2_5b);
        $over2_5b = $rowOver2_5b['Over2_5b'];
        $over2_5bPercentage = number_format(($awayGames > 0) ? ($over2_5b / $awayGames) * 100 : 0, 1);
    } else {
        $over2_5b = 0;
        $over2_5bPercentage = 0;
    }

    // Calculate the sum of Over2.5a% and Over2.5b%
    $sumOver2_5 = number_format($over2_5aPercentage + $over2_5bPercentage, 1);

            // Calculate the percentage of home wins, away wins, and draws
            $homeWinPercentage = number_format(($homeGames > 0) ? ($homeWin / $homeGames) * 100 : 0, 1);
            $awayWinPercentage = number_format(($awayGames > 0) ? ($awayWin / $awayGames) * 100 : 0, 1);
            $drawPercentage = number_format((($hDraw + $aDraw) > 0) ? (($hDraw + $aDraw) / ($homeGames + $awayGames)) * 100 : 0, 1);

            // Determine the prediction based on the highest percentage
           $prediction = "";

if ($homeWinPercentage >= $awayWinPercentage && $homeWinPercentage >= $drawPercentage && $homeWinPercentage >= $gg1Percentage && $homeWinPercentage >= $gg2Percentage && $homeWinPercentage >= $over25Percentage) {
    $prediction = "Home Win";
} elseif ($awayWinPercentage >= $homeWinPercentage && $awayWinPercentage >= $drawPercentage && $awayWinPercentage >= $gg1Percentage && $awayWinPercentage >= $gg2Percentage && $awayWinPercentage >= $over25Percentage) {
    $prediction = "Away Win";
} elseif ($drawPercentage >= $homeWinPercentage && $drawPercentage >= $awayWinPercentage && $drawPercentage >= $gg1Percentage && $drawPercentage >= $gg2Percentage && $drawPercentage >= $over25Percentage) {
    $prediction = "Draw";
} elseif ($gg1Percentage >= $homeWinPercentage && $gg1Percentage >= $awayWinPercentage && $gg1Percentage >= $drawPercentage && $gg1Percentage >= $gg2Percentage && $gg1Percentage >= $over25Percentage) {
    $prediction = "GG-yes";
} elseif ($gg2Percentage >= $homeWinPercentage && $gg2Percentage >= $awayWinPercentage && $gg2Percentage >= $drawPercentage && $gg2Percentage >= $gg1Percentage && $gg2Percentage >= $over25Percentage) {
    $prediction = "NO-gg";
} elseif ($over25Percentage >= $homeWinPercentage && $over25Percentage >= $awayWinPercentage && $over25Percentage >= $drawPercentage && $over25Percentage >= $gg1Percentage && $over25Percentage >= $gg2Percentage) {
    $prediction = "Over2.5";
}
            echo "<tr>" .
                "<td>{$match['Date']}</td>" .
                "<td bgcolor='#dbaf97' class='col3-clickable'>{$match['HomeTeam']}</td>" .
                "<td bgcolor='#b4b4b4' class='col5-clickable'>{$match['AwayTeam']}</td>" .
                 "<td>$homeWinPercentage%</td>" . // Display the Home win percentage in the table
                "<td>$awayWinPercentage%</td>" . // Display the Away win percentage in the table
                "<td>$drawPercentage%</td>" . // Display the draw percentage in the table
                "<td bgcolor='#b4b4b4'>$sumGG%</td>" . // Display the average of GG1% and GG2% in the table
               "<td>$noGGValue%</td>" .  // Display the NO-gg value in the table
                "<td>$sumOver2_5%</td>" .  // Display the sum of Over2.5a% and Over2.5b% in the table
                 "<td bgcolor='#BB9C10'>$prediction</td>" . // Display the prediction in the table
            "</tr>";
        }

        echo "</table>";
    }
}

mysqli_close($conn);
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Function to handle the click event on COL 3, COL 4, and COL 5 cells
        $(".col3-clickable, .col4-clickable, .col5-clickable").click(function() {
            var value = $(this).text();
            
            // Determine which column to search based on the clicked cell
            var columnToSearch = "";
            if ($(this).hasClass("col3-clickable")) {
                columnToSearch = "HomeTeam";
            } else if ($(this).hasClass("col4-clickable")) {
                columnToSearch = "HomeTeam";
            } else if ($(this).hasClass("col5-clickable")) {
                columnToSearch = "AwayTeam";
            }
            
            // Send separate AJAX requests for HomeTeam and AwayTeam
            $.ajax({
                type: "POST",
                url: "epl/search_database.php",
                data: {
                    searchValue: value,
                    searchColumn: "HomeTeam"
                },
                success: function(data1) {
                    $.ajax({
                        type: "POST",
                        url: "epl/search_database.php",
                        data: {
                            searchValue: value,
                            searchColumn: "AwayTeam"
                        },
                        success: function(data2) {
                            // Combine the results from both queries
                            var combinedData = data1 + data2;

                            // Open a new window and display the combined search results
                            var newWindow = window.open('', '_blank');
                            newWindow.document.write(combinedData);
                        },
                        error: function() {
                            alert("Error occurred while fetching data from the server.");
                        }
                    });
                },
                error: function() {
                    alert("Error occurred while fetching data from the server.");
                }
            });
        });
    });
</script>
