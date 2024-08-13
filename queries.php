<?php
    $initData = "SELECT title, year, rating, runtime, mg.name as main_genre,
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
GROUP BY movies.id, mg.name, sg.sidegenres, director.directors
ORDER BY movies.title LIMIT 10;";

$genres = "SELECT name FROM genres ORDER BY name;"
?>