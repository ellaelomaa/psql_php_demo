<?php
include "db.php";
  $movieCount = 10;
  $sortDir = "ASC";
  $sortBy = "title";
  $userQuery = ''; # Where all user-chosen options/queries are put
  $selectedGenres = [];
  $title = "";

  if (empty($_POST["search"])) {
    $movieCount = $_POST["movieCount"];
    $sortDir = $_POST["sortDir"];
    $lowerSort = strtolower($_POST["sortBy"]);

    if ($lowerSort == "director") {
      $sortBy = "director.directors"; # to account for group by
    }
    else {
      $sortBy = $lowerSort;
    }

    if (!empty($_POST["selectedGenres"])) {
      $selectedGenres = $_POST["selectedGenres"];
      $genreString = "'".implode("','", $selectedGenres)."'";
      $userQuery = "LEFT JOIN(
	SELECT movie_id, string_to_array(string_agg(genres.name, ','), ',') as genres
	FROM movie_genres
	LEFT JOIN genres ON (genres.id = movie_genres.genre_id) 
	GROUP BY movie_id	
) all_genres on movies.id = all_genres.movie_id
WHERE ARRAY[$genreString] <@ (all_genres.genres)";
      if (!empty($_POST["title"])) {
        $title = $_POST["title"];
        $userQuery = $userQuery . " AND position(LOWER('$title') in LOWER(title)) > 0";
      }
    }
    else {
      if (!empty($_POST["title"])) {
        $title = $_POST["title"];
        $userQuery = "WHERE position(LOWER('$title') in LOWER(title)) > 0";
      }
    }
  }

  $query = "SELECT title, year, rating, runtime, mg.name as main_genre,
	sg.sidegenres as side_genres, 
	director.directors as directors,
    TRIM(string_agg(COALESCE(actors.first_name, '') || ' ' || actors.surname, ', ')) as actors
	FROM movies
LEFT JOIN (
	SELECT mg.movie_id, name
	FROM genres
	LEFT JOIN movie_genres as mg on genres.id = mg.genre_id
	WHERE main = true
) mg
	on movies.id = mg.movie_id
LEFT JOIN(
	SELECT sg.movie_id, string_agg(genres.name, ', ') as sidegenres
	FROM genres
	LEFT JOIN movie_genres as sg on genres.id = sg.genre_id
	WHERE main = false
	GROUP BY 1) sg
	on movies.id = sg.movie_id
LEFT JOIN(
	SELECT movie_id, TRIM(string_agg(COALESCE(people.first_name, '') || ' ' || people.surname, ', ')) as directors
	FROM movie_personnel mp
	LEFT JOIN people on mp.person_id = people.id
	WHERE role = 'director'
	GROUP BY mp.movie_id
	ORDER BY mp.movie_id
) director on movies.id = director.movie_id
LEFT JOIN (
	SELECT movie_id, surname, first_name
	FROM movie_personnel mp
	LEFT JOIN people on mp.person_id = people.id
	WHERE role = 'actor'
	GROUP BY mp.movie_id, surname, first_name
	ORDER BY mp.movie_id, surname
) actors on movies.id = actors.movie_id
$userQuery
GROUP BY movies.id, mg.name, sg.sidegenres, director.directors
ORDER BY $sortBy $sortDir ,year, runtime LIMIT $movieCount;";
        $result = pg_query($conn, $query);
        if (!$result) {
          echo "Error in query.<br>";
          exit;
        }
        if (pg_num_rows($result) == 0) {
echo "No rows.";

        } else {
          echo "    <table>
        <tr>
            <th>Title
               </th>
            <th>Year</th>
            <th>Rating</th>
            <th>Runtime</th>
            <th>Main genre</th>
            <th>Side genres</th>
            <th>Director(s)</th>
            <th>Actors</th>
        </tr>";
        while($row = pg_fetch_assoc($result)) {
          echo "
          <tr>
              <td>$row[title]</td>
              <td>$row[year]</td>
              <td>$row[rating]</td>
              <td>$row[runtime]</td>
              <td>$row[main_genre]</td>
              <td>$row[side_genres]</td>
              <td>$row[directors]</td>
              <td>$row[actors]</td>
          </tr>
          ";
      }
          echo "</table>";
        }

      ?>