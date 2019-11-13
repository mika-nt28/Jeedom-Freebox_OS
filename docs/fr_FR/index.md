Description
==========
Ce plugin permet de récupérer les informations de votre freeboxOS (Serveur Freebox Revolution ou 4K).

Les informations disponibles de votre Freebox Serveur sur Jeedom sont:

 * Les informations système
 * Le nombre d'appels en absences
 * Le nombre d'appels passés
 * Le nombre d'appels reçus
 * Les débits internet
 * L'état de votre connexion
 * La place disponible dans vos disques connectés à la Freebox Serveur. 
 * L’état de chaque équipement DHCP 
 * Couper le wifi
 * Redémarrer votre Freebox


Installation et Configuration
=============================
Une fois le plugin installé et activé, nous devons procéder à un appairage du serveur Jeedom sur la Freebox.

![introduction01](../images/Freebox_OS_screenshot_configuration.jpg)

Sur la page de configuration, vous avez la possibilité de personnaliser les options de connexion, mais seules celles par défaut ont été validées.

- Pause dans la boucle du demon (s): Permet de determiner la récurence de mise a jours des informations remonté depuis la Freebox
- IP Freebox: Adresse de connexion de la freebox (par defaut : mafreebox.free.fr)
- Id de l'application Freebox serveur: Identifiant utilisé par la freebox (par defaut : fr.freebox.jeedom)
- Nom de l'application Freebox serveur: Nom utilisé par la freebox (par defaut : Freebox OS For Jeedom)
- Version de l'application Freebox serveur: Version de l'application utilisé par la freebox  (par defaut : v1.0.0)
- Nom de l'équipement connecté: Nom de l'équipement utilisé par la freebox  (par defaut : Jeedom Core)
L'appairage doit etre lancé après une sauvegarde des parametres pour leurs prises en compte.

Appairage
=========
Pour cela, il suffit de cliquer sur le bouton "Appairer" dans votre interface de configuration.
Vous allez à ce moment avoir un message comme ceci.
Ne validez surtout pas maintenant, attendez les étapes suivantes.

![introduction01](../images/MessageValidation.jpg)

Validation sur la Freebox
-------------------------
Vous avez donc demandé a votre Freebox une nouvelle connexion par l'api, et il faut l'autoriser.
Pour cela, rien de plus simple, il vous faut donc aller valider cette connexion directement sur votre Freebox en appuyant sur la flèche de droite pour répondre "oui"

![introduction01](../images/EcranFreebox.jpeg)

Validation Jeedom
-----------------
Vous pouvez donc maintenant retourner sur votre pc pour valider le message laissé en attente précédement.
L'état de fonctionnement de la liaison va alors être testé.

Droit d'acces
=============

Certain droit d'acces sont necessaire pour l'utilisation de se plugin mais qui ne peuvent etre donné par les API
Il est donc necessaire de faire un operation supplémentaire

* Connecter vous sur votre freebox http://mafreebox.free.fr
* Ouvrer le parametre freebox
![Parametre de la freebox](../images/ParametreFreebox.jpg)
* Ouvrer la gestion des acces a la  freebox
![Parametre de gestion des acces de la freebox](../images/GestionAccesFreebox.jpg)
* Dans la liste choissisez l'acces a jeedom
![Liste des autorisation d'acces a la freebox](../images/ListeAccesFreebox.jpg)
* Autorisez tout
![Autorisation de l'acces a la freebox](../images/AutorisationAccesFreebox.jpg)

Equipements
===========

Le plugin vas automatiquement cree tous les equipements et les commandes dont il est capable d'executer ou de recupérer des informations
* ADSL
    * Freebox rate down
    * Freebox rate up
    * Freebox bandwidth up
    * Freebox bandwidth down
    * Freebox medi
    * Freebox state
* Système
    * Update
    * Reboot
    * Status du wifi
    * Active/Désactive le wifi
    * Wifi On
    * Wifi Off
    * Freebox firmware version
    * Mac
    * Vitesse ventilateur
    * temp sw','temp_sw
    * Allumée depuis
    * board name
    * temp cpub
    * temp cpum
    * serial
    * Redirection de ports
* Téléphone
    * Nombre Appels Manqués
    * Nombre Appels Reçus
    * Nombre Appels Passés
    * Liste Appels Manqués
    * Liste Appels Reçus
    * Liste Appels Passés
    * Faire sonner les téléphones DECT
    * Arrêter les sonneries des téléphones DECT
* Téléchargements
    * Nombre de tâche(s)
    * Nombre de tâche(s) active
    * Nombre de tâche(s) en extraction
    * Nombre de tâche(s) en réparation
    * Nombre de tâche(s) en vérification
    * Nombre de tâche(s) en attente
    * Nombre de tâche(s) en erreur
    * Nombre de tâche(s) stoppée(s)
    * Nombre de tâche(s) terminée(s)
    * Téléchargement en cours
    * Vitesse réception
    * Vitesse émission
    * Start DL
    * Stop DL
* AirPlay
    * Player actuel AirMedia
    * AirMedia Start
    * AirMedia Stop
    
Specificité de Home Adapters, Réseau et Disque Dur
--------------------------------------------------

Ces 3 equipement ont pour spécificité d'etre vide a la creation.
Cela vient du fait qu'il faut se connecter a la box pour recuperer les commande associé.
Vous y trouverez dans chaqu'un d'entre eu un bouton "Rechercher" qui crera les commande associé

![Recherche des equipements spécifique](../images/RechercheCommandes.jpg)

Freebox Delta
=============

La freebox delta permet d'avoir un pack de sécurité ainsi que la connexion avec certain equipement.
Pour qu'il soit remonté et fonctionnel dans le plugin il suffit de cliquer sur "Rechercher les tiles"

> les camera sont cree, avec votre accord, dans le plugin camera, s'il existe.


![Recherche des equipements spécifique freebox delta](../images/RechercheTiles.jpg)
