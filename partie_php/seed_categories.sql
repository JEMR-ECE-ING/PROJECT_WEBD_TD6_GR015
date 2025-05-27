
/*& "C:\wamp64\bin\mysql\mysql9.1.0\bin\mysql.exe" -u root -p sportify*/

INSERT INTO categories_sport (nom_categorie, description) VALUES
    ('activites_sportives','Toutes les activités disponibles pour les athlètes de tout niveau.'),
    ('sports_competition','Sports de compétition pour les athlètes plus habiles.'),

INSERT INTO activites_sportives (
    nom_activite,
    id_categorie,
    description,
    prix,
    duree_seance,
    actif
) VALUES
    ('Musculation',1, 'Toutes les activités de musculation',14.99, 60, 1),
    ('Fitness',1, 'Séances de fitness pour tous niveaux',14.99, 60, 1),
    ('Biking',1, 'Cours de vélo en salle',14.99, 60, 1),
    ('Cardio-Training',1, 'Exercices cardio intenses',14.99, 60, 1),
    ('Cours Collectifs',1, 'Yoga, dance et autres cours de groupe en salle', 14.99, 60, 1);

INSERT INTO activites_sportives (
    nom_activite,
    id_categorie,
    description,
    prix,
    duree_seance,
    actif
) VALUES
    ('Basketball',2, 'Entraînement collectif et individuel',24.99, 120, 1),
    ('Football',2, 'Techniques et tactiques de football',24.99, 120, 1),
    ('Rugby',2, 'Préparation physique et jeu en équipe',24.99, 120, 1),
    ('Tennis',2, 'Exercices cardio intenses',24.99, 120, 1),
    ('Natation',2, 'Perfectionnement en nage libre, dos, etc.',24.99, 120, 1),
    ('Plongeon',2, 'Techniques de plongeon et de saut', 24.99, 120, 1);
