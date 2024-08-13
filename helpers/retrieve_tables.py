import pandas as pd
import csv
import chardet
from collections import Counter

"""
Checking the encoding to make sure the file is opened correctly.
"""
with open("imdb.csv", "rb") as f:
    result = chardet.detect(f.read())

df = pd.read_csv(
    "imdb.csv",
    on_bad_lines="skip",
    encoding=result["encoding"],
    usecols=[
        "Movie_Title",
        "Year",
        "Director",
        "Actors",
        "Rating",
        "Runtime(Mins)",
        "main_genre",
        "side_genre",
    ],
)

"""
Extracting all unique genres, sorting them, and saving to a csv.
"""
genres_set = set()

genres = df[["main_genre", "side_genre"]]
for i in genres.index:
    main = genres["main_genre"][i]
    genres_set.add(main)
    side = genres["side_genre"][i].split(",")
    for x in side:
        genres_set.add(x.strip())

genres_set = sorted(genres_set)

with open("genres.csv", "w", newline="", encoding="utf-8") as file:
    csv_writer = csv.writer(file)
    for genre in genres_set:
        csv_writer.writerow([genre])

"""
Extracting all unique personnel, sorting them, and saving to a csv.
The assumption is that only one name can be a surname, aka., the last
word of the name is set as surname, all preceding names as first names.
"""
people_list = []

people = df[["Director", "Actors"]]

for i in people.index:
    director = people["Director"][i].split()

    # Checking if the director only goes by one name
    if len(director) == 1:
        if director not in people_list:
            people_list.append(director)
    # Checking if there are multiple directors
    else:
        if "Directors:" in people["Director"][i]:
            temp = " ".join(director)
            temp = temp.replace("Directors:", "")
            directors = temp.split(",")
            for person in directors:
                names = person.split()
                if len(names) == 1:  # Checking if the director only goes by one name
                    if person not in people_list:
                        people_list.append(names)
                else:
                    surname = names[-1]
                    firstname = " ".join(names[:-1])
                    name = [surname, firstname]
                    if name not in people_list:
                        people_list.append(name)
        else:
            surname = director[-1]
            firstname = " ".join(director[:-1])
            name = [surname, firstname]
            if name not in people_list:
                people_list.append(name)

for i in people.index:
    actor = people["Actors"][i].split(",")

    for actor in actor:
        names = actor.split()

        # Checking if the actor only goes by one name
        if len(names) == 1:
            if names not in people_list:
                people_list.append(names)
        else:
            surname = names[-1]
            firstname = " ".join(names[:-1])
            name = [surname, firstname]
            if name not in people_list:
                people_list.append(name)

with open("people.csv", "w", newline="", encoding="utf-8") as file:
    csv_writer = csv.writer(file)
    for person in people_list:
        csv_writer.writerow(person)

for person in people_list:
    if len(person) == 1:
        print(person)

"""
Extracting the needed columns for the temporary movie table.
"""
columns = df[
    [
        "Movie_Title",
        "Year",
        "Director",
        "Actors",
        "Rating",
        "Runtime(Mins)",
        "main_genre",
        "side_genre",
    ]
]
columns.to_csv("temp.csv", index=False)

"""
Extracting the needed columns for movie table.
"""
movies = df[
    [
        "Movie_Title",
        "Year",
        "Rating",
        "Runtime(Mins)",
        "main_genre",
    ]
]
movies.to_csv("movies.csv", index=False)
