![icon du site muller intuitiv](./docs/images/logo-muller-intuitiv.svg)

Il n'y a aucune affiliation avec l'entreprise Muller Intuitiv (Legrand).

# jeedom-plugin-MullerIntuitiv

Plugin qui permet de récupérer les radiateurs de l'application mobile [Muller Intuitiv](https://www.muller-intuitiv.com/).

# Configuration du plugin

Après avoir **Activer** le plugin dans la partie configuration renseigner les informations suivantes :
* Le login
* Le mot de passe

Qui correspondent aux identifiants pour vous connecter à l'application **Muller Intuitiv** et cliquer sur **Sauvegarder**
dans la partie **Configuration**

![Configuration-plugin](./docs/images/configuration-plugin.png)

# Synchronisation des radiateurs
Dans la gestion du plugin, cliquer sur **Synchroniser l'installation** vous allez avoir tout vos radiateurs + une partie Home.

![Synchronisation-installation](./docs/images/synchroniser-installation.png)

Ca active et rend visible les équipements automatiquement

Après la synchronisation vous devez voir apparaitre ceci.

![Apparition-radiateurs](./docs/images/apparition-radiateurs.png)

# Utilisation du widget Home
![Widget-home](./docs/images/widget-home.png)

Vous avez 3 modes qui **s'applique à tous les radiateurs de la maison** :
* Home -> Utilisation du planning appliquer
* Hors Gel -> Met en mode Hors Gel à 7°c par défaut de l'application
* Absent -> Met en mode Absent à 16°c par défaut de l'application

Vous pouvez appliquer le planning qui vous convient par exemple un pour le boulot et un autre pour les vacances, créer
via l'application.

# Utilisation du widget Radiateur
![Widget-radiateur](./docs/images/widget-radiateur.png)

Vous avez 3 modes qui s'applique au radiateur lui même :
* Home -> Utilisation du planning appliquer
* Hors Gel -> Met en mode Hors Gel à 7°c par défaut de l'application
* Manuel -> En cliquant sur le + ou - du Thermostat (temps du mode manuel indiqué dans l'application mobile
  dans **Paramètres de chauffe** -> **Durée par défaut d'un dérogation**)

Vous avez la détection de la fenêtre si c'est ouvert ou pas et de pouvoir lui dire que c'est ouvert ou fermer.
Par exemple en le combinant avec un détecteur d'ouverture et fermeture de fenêtre par scénario.
