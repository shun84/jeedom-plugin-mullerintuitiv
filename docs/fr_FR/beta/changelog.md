# Changelog plugin Muller Intuitiv

>**IMPORTANT**
>
>Pour rappel s'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

# 09/02/2024
- Amelioration du refresh token

# 30/07/2023
- Version minimum **4.3.9**
- Rajout du planning dans la gestion du plugin de celui utiliser sur le widget **home**
- Rajout du planning **en cours** sur le widget **home**
- Rajout de la consommation pour chaque **Radiateur**

# 29/04/2023
- Obligation d'utilisation du plugin en v4.2
- Optimisation PHP + JS pour la nouvelle v4.4
- Suppression du composer GuzzleHttp car déjà intégré dans Jeedom

# 28/11/2022
- Déplacer le cron de 10 à 15 mins pour pas chevaucher avec Netatmo
- Pouvoir avoir toutes les maisons
- Rajout : panel pour voir la consommation des radiateurs via l'API
- Fix bug : si erreur de token des codes 502,503,504,404, les ignorés

# 19/04/2022
- Rajout : en fonction du mode utilisé la couleur du widget change
- Rajout : quand on passe en mode manuel, j'ai rajouté le temps qu'il sera dans ce mode là
- Fix bug : affichage avec le théme Legacy
- Fix bug : affichage coté mobile

# 08/04/2022
- Optimisation : utilisation du refresh token pour supprimer les erreur 500
- Fix bug : pour le thermostat + ou -

# 28/02/2022
- Rajout : Récupération des plannings

# 17/02/2022
- Mise en place de l'application Muller Intuitiv
