<?php
    include "db.php";
    include "queries.php";    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link href="styles.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css" />

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="virtual-select/virtual-select.min.css" />
    <script src="virtual-select/virtual-select.min.js"></script>

    <script>
    var movieCount = 10;
    var sortDir = "ASC";
    var sortBy = "title";
    var selectedGenres = [];

    $(document).ready(function() {
        

        $("#showMore").click(function() {
            movieCount = movieCount + 10;
            $("#movies").load("load-data.php", {
                movieCount: movieCount,
                sortDir: sortDir,
                sortBy: sortBy,
                selectedGenres: document.querySelector("#genreSelect").value,
                title: document.querySelector("#title-search").value,
            });

        });

        $("#sortDirForm").click(function() {
            sortDir = $("input[name=sortDir]:checked").val();
        });

        $("#wipeText").click(function() {
            document.querySelector("#title-search").value = "";
        })

        $("#sort-by").click(function() {
            sortBy = $("#sort-by option:selected").text();
        });

        $("#search").click(function() {
            $("#movies").load("load-data.php", {
                movieCount: 10,
                sortDir: sortDir,
                sortBy: sortBy,
                selectedGenres: document.querySelector("#genreSelect").value,
                title: document.querySelector("#title-search").value,
            });
        });


    });
    </script>

</head>

<body>
    <div class="grid-wrapper">
        <div class="sort-options">
            Sort by<br>
            <select name="sort-by" id="sort-by">
                <option value="dog">Title</option>
                <option value="cat">Year</option>
                <option value="hamster">Rating</option>
                <option value="parrot">Runtime</option>
                <option value="spider">Main genre</option>
                <option value="goldfish">Director</option>
            </select>
            <br>

            <form id="sortDirForm">
                <label><input type="radio" name="sortDir" id="ASC" value="ASC" selected>ASC</label>
                <label><input type="radio" name="sortDir" id="DESC" value="DESC">DESC</label>
            </form>

        </div>
        <div class="search-bar">
            <search>
                <input name="title-search" id="title-search" placeholder="Search for a movie title">
            </search>
            <button id="wipeText">&#x2715</button>
        </div>

        <div class="genres">
            <?php
          $result = pg_query($conn, $genres);
          if (!$result) {
            echo "Error in query.<br>";
            exit;
          }
          if (pg_num_rows($result) == 0) {
            echo "No rows.";
  
          }
          else {
            ?>
            <select id="genreSelect" multiple name="native-select" data-search="false"
                data-silent-initial-value-set="true">
                <?php
                while($row = pg_fetch_assoc($result)) {
                  echo "<option value=$row[name]>$row[name]</option>";
                };
              ?>
            </select>
            <?php
                  }
        ?>

        </div>

        <div class="search-btn">
            <button id="search">Search</button>
        </div>

    </div>
    <p id="test"></p>
    <div id="movies">
        <?php
        $result = pg_query($conn, $initData);
        if (!$result) {
          echo "Error in query.<br>";
          exit;
        }
        if (pg_num_rows($result) == 0) {
          echo "No rows.";

        } else {
          ?>
        <table>

            <tr>
                <th>Title</th>
                <th>Year</th>
                <th>Rating</th>
                <th>Runtime</th>
                <th>Main genre</th>
                <th>Side genres</th>
                <th>Director(s)</th>
                <th>Actors</th>
            </tr>
            <?php
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


    </div>
    <button id="showMore">Show more results</button>
    <script>
    VirtualSelect.init({
        ele: '#genreSelect',
        multiple: true,
        search: false,
        placeholder: "Search by genres (max. 3)",
        maxValues: 3
    });
    </script>
</body>

</html>