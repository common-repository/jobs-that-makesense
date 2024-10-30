=== jobs_that_makesense ===
Description: Afficher les annonces de votre espace recruteur jobs.makesense.org
Version: 1.3.2
Contributors: jcmakesense
Stable Tag: 1.3.2
Tested up to: 6.2
tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ce plugin permet aux recruteurs de la plateforme jobs_that_makesense d'afficher leurs offres d'emploi et de permettre aux visiteurs de leur site de candidater directement sans passer par la plateforme [jobs_that_makesense](https://jobs.makesense.org/).

== Description ==

Ce plugin permet aux recruteurs de la plateforme jobs_that_makesense d'afficher leurs offres d'emploi et de permettre aux visiteurs de leur site de candidater directement sans passer par la plateforme [jobs_that_makesense](https://jobs.makesense.org/).

= 💥 Afficher les annonces sur une page de votre site =

Vous devez inclure le shortcode suivant sur n'importe quelle page où vous souhaitez afficher la liste de vos offres d'emploi actives :

**`[jobs_that_makesense key="API_KEY" id="PROJECT_NUMBER"]`**

_Exemple avec toutes les options :_
_`[jobs_that_makesense key="API_KEY" id="PROJECT_NUMBER" paginate="24" columns="3" fields="logotype,name" text-color="auto" border-color="auto" over-background-color="auto" ]`_

* **id** : Identifiant du projet que vous pouvez trouver dans votre url d'espace ou [ici](https://jobs.makesense.org/workspace/main/integrations)
* **key** : Clé api que vous pouvez trouver dans votre espace recruteur [ici](https://jobs.makesense.org/workspace/main/integrations)
* **paginate** : Nombre d'annonces à afficher par page (par défaut 24), false pour les afficher toutes
* **columns** : Nombre de colonnes lors de l'affichage des annonces sur votre site (par défaut 2), peut être 1, 2 ou 3
* **fields** : Affiche des informations complémentaires. Les différentes options doivent être séparées par des virgules et peuvent être : "logotype" (logotype du recruteur), "name" (Nom du recruteur)
* **text-color** : Modifier la couleur du texte par défaut. Peut être : "auto", "inherit", "#XXXXXX" (une couleur au format hexadecimal)
* **border-color** : Modifier la couleur de la bordure. Peut être : "auto", "inherit", "#XXXXXX" (une couleur au format hexadecimal)
* **over-background-color** : Modifier la couleur du fond au survol sur l'annonce. Peut être : "auto", "inherit", "#XXXXXX" (une couleur au format hexadecimal)
* **child-jobs** : Afficher en plus des annonces de votre espace, celles des espaces associés (labels ou enfants). Par défaut cette option est désactivée. Peut être : true ou false.

Vous devez être Pro+ sur jobs.makesense.org pour activer le dépot de candidature sur votre site.
Sans cela les annonces sont bien affichées mais au clic les visiteurs intéressés de votre site seront redirigés vers l'annonce sur la plateforme jobs_that_makesense.

= 💼 Afficher la liste des recruteurs associés à votre espace =

Vous êtes un label de confiance ou un recruteur avec l'option réseau activées ? vous pouvez lister le nom de logotype des structures qui vous sont associées avec ce shortcode à inclure sur n'importe quelle page où vous souhaitez afficher la liste :

**`[jobs_that_makesense_children key="API_KEY" id="PROJECT_NUMBER"]`**

En ajoutant child-jobs au shortcode de liste d'annonce, vous pouvez lister les annonces de toutes les structures associées à votre label ou à votre réseau :

**`[jobs_that_makesense key="API_KEY" id="PROJECT_NUMBER" child-jobs="true"]`**

Pour information vous pouvez ajouter toutes les options du shortcode de la section 1.

= 🔗 Liens =

* [La plateforme jobs_that_makesense](https://jobs.makesense.org)
* [Créer un espace recruteur](https://jobs.makesense.org/workspace/new)
* [Votre espace recruteur](https://jobs.makesense.org/workspace/main)
* [Trouver vos clés API](https://jobs.makesense.org/workspace/main/integrations)

= 📨 Question ? =

Vous avez une question sur l'usage du plugin ? contactez-nous sur jobs@makesense.org !

== Frequently Asked Questions ==

= Où se trouve la clé ? =

Dans la section réglages de votre espace recruteur, [ici](https://jobs.makesense.org/workspace/main/integrations).

== Upgrade Notice ==

== Screenshots ==

== Changelog ==

= 1.3.2 =
* Fix pagination
* Update icon and banner

= 1.3.1 =
* Add utm parameters

= 1.3.0 =
* Add pagination option
* Refactor code

= 1.2.1 =
* Add featured option

= 1.2.0 =
* Improve documentation

= 1.1.0 =
* Add color options
* Add a new shortcode for labels & network
* Add extra fields


= 1.0.12 =
* Fix styles clearfix

= 1.0.11 =
* Fix styles

= 1.0.10 =
* Add version number for JS and CSS
* Fix loading page

= 1.0.9 =
* Fix broken url

= 1.0.8 =
* Add plugin version number in API call

= 1.0.7 =
* Fix Modal Close Button Background style
* Fix Div before tag telescoping

= 1.0.6 =
* Fix Plugin name

= 1.0.5 =
* Fix modal z-index

= 1.0.4 =
* Mise à jour du Readme

= 1.0.3 =
* Mise à jour du Readme

= 1.0.2 =
* Mise à jour du Readme

= 1.0.1 =
* Correction de l'url de candidature

= 1.0.0 =
_La première version du plugin !_
* Afficher où vous le souhaitez les annonces de votre espace
* Activer la fonctionnalité de candidature sur votre site internet (vous devez être pro)
