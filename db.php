<?php
    $servername = "localhost";
    $port = 5433;
    $username = "postgres";
    $password = "postgres";
    $dbname = "imdb";
    $sort = "ASC";

    $conn = pg_connect("host=$servername port=$port dbname=$dbname user=$username password=$password");
?>