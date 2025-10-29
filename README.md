# Novalia Déménagement - Plugin WordPress

Plugin professionnel de devis de déménagement pour Novalia Group

## Description

Ce plugin permet aux visiteurs de votre site de :
- Calculer un trajet avec carte interactive (OpenStreetMap)
- Sélectionner les objets à déménager avec volumes calculés
- Recevoir automatiquement 2 devis par email (standard et complet)

Interface d'administration complète pour :
- Gérer les devis (statuts, historique)
- Gérer les objets et leurs volumes
- Configurer les tarifs
- Consulter les statistiques

## Installation

### 1. Structure des fichiers

Créez la structure suivante dans `wp-content/plugins/novalia-demenagement/` :

```
novalia-demenagement/
├── novalia-demenagement.php (fichier principal)
├── includes/
│   ├── class-novalia-database.php
│   ├── class-novalia-items.php
│   ├── class-novalia-tarifs.php
│   ├── class-novalia-devis.php
│   ├── class-novalia-pdf.php
│   ├── class-novalia-email.php
│   ├── class-novalia-admin.php
│   ├── class-novalia-shortcode.php
│   └── class-novalia-ajax.php
├── assets/
│   ├── css/
│   │   ├── novalia-style.css
│   │   └── novalia-admin.css
│   └── js/
│       ├── novalia-script.js
│       └── novalia-admin.js
├── lib/
│   └── tcpdf/
│       └── (télécharger TCPDF depuis https://github.com/tecnickcom/tcpdf)
└── languages/
```

### 2. Installation de TCPDF

Le plugin nécessite la librairie TCPDF pour générer les PDFs :

1. Téléchargez TCPDF : https://github.com/tecnickcom/TCPDF/releases
2. Extrayez le contenu dans `wp-content/plugins/novalia-demenagement/lib/tcpdf/`
3. Vérifiez que le fichier `tcpdf.php` est accessible à : `lib/tcpdf/tcpdf.php`

### 3. Activation du plugin

1. Connectez-vous à l'administration WordPress
2. Allez dans Extensions > Extensions installées
3. Activez "Novalia Déménagement"

L'activation créera automatiquement :
- Les tables de base de données
- Les objets par défaut
- Les tarifs par défaut

## Configuration

### 1. Configuration des tarifs

1. Dans l'admin WordPress, allez dans **Novalia > Tarifs**
2. Ajustez les tarifs selon vos besoins :
   - Prix de base (CHF)
   - Prix par kilomètre (CHF/km)
   - Prix par m³ (CHF/m³)
   - Prix étage sans ascenseur (CHF/étage)
   - Prix emballage carton (CHF/carton)
   - Prix fourniture carton (CHF/carton)
   - Majoration weekend (%)
   - Réduction volume >50m³ (%)
3. Cliquez sur "Enregistrer les tarifs"

### 2. Gestion des objets

1. Allez dans **Novalia > Objets**
2. Vous pouvez :
   - Ajouter de nouveaux objets
   - Modifier les objets existants
   - Supprimer des objets
   - Organiser par catégories

Les objets par défaut incluent toutes les pièces d'une maison :
- Salon, Salle à manger, Cuisine
- Chambres (principale, enfant)
- Bureau, Salle de bain, Entrée
- Cave/Garage, Extérieur, Cartons

### 3. Configuration de l'email

Le plugin utilise la fonction `wp_mail()` de WordPress.

**Pour un envoi fiable, configurez un plugin SMTP :**

Plugins recommandés :
- WP Mail SMTP
- Easy WP SMTP
- Post SMTP

Configuration minimale :
- Serveur SMTP de votre hébergeur
- Email : info@novaliagroup.ch
- Activer l'authentification

### 4. Ajout du formulaire sur votre site

Utilisez le shortcode suivant dans n'importe quelle page :

```
[novalia_devis]
```

Exemple :
1. Créez une nouvelle page "Devis déménagement"
2. Ajoutez le shortcode `[novalia_devis]`
3. Publiez la page

## Utilisation

### Frontend (Visiteurs)

#### Étape 1 : Trajet
- Entrer l'adresse de départ (autocomplétion)
- Entrer l'adresse d'arrivée (autocomplétion)
- Choisir la date du déménagement
- Indiquer les étages sans ascenseur
- La carte affiche le trajet et calcule la distance

#### Étape 2 : Inventaire
- Sélectionner les objets par catégorie
- Ajuster les quantités
- Ajouter des objets personnalisés
- Le volume total s'affiche en temps réel

#### Étape 3 : Récapitulatif
- Vérifier toutes les informations
- Choisir entre déménagement standard ou complet
- Si complet : indiquer le nombre de cartons
- Entrer ses coordonnées
- Recevoir les 2 devis par email

### Backend (Administration)

#### Gestion des devis

**Novalia > Devis**
- Voir tous les devis
- Filtrer par statut (En attente, Accepté, Refusé, Annulé)
- Voir les détails d'un devis
- Changer le statut
- Supprimer un devis
- Statistiques en temps réel

#### Gestion des objets

**Novalia > Objets**
- Ajouter un nouvel objet
- Modifier nom et volume
- Supprimer des objets
- Organiser par catégories

#### Gestion des tarifs

**Novalia > Tarifs**
- Ajuster tous les prix
- Modifier les pourcentages
- Sauvegarde instantanée

#### Statistiques

**Novalia > Statistiques**
- Total des devis
- Devis en attente
- Devis acceptés
- Volume total
- Montant total accepté
- Derniers devis

## Personnalisation

### Couleurs

Les couleurs sont définies dans les fichiers CSS :

**Frontend** (`assets/css/novalia-style.css`) :
```css
:root {
    --novalia-blue: #1A2332;
    --novalia-turquoise: #2BBBAD;
    --novalia-gray: #F5F5F5;
    --novalia-white: #FFFFFF;
    --novalia-orange: #FF7A00;
}
```

**Admin** (`assets/css/novalia-admin.css`) :
Mêmes variables CSS

### Typographie

- **Titres** : Montserrat Bold
- **Sous-titres** : Poppins Medium
- **Texte** : Open Sans

Chargées automatiquement via Google Fonts (ou ajoutez-les à votre thème).

### Template Email

Modifiez le template email dans `includes/class-novalia-email.php` :
- Fonction `get_email_template()`
- HTML complet personnalisable

### Template PDF

Modifiez le template PDF dans `includes/class-novalia-pdf.php` :
- Fonctions `add_page_one()` et `add_page_two()`
- Personnalisation complète des pages

## Fonctionnalités principales

### Calcul automatique
- Distance via OSRM (Open Source Routing Machine)
- Volume total en m³
- Prix standard et complet
- Majorations (weekend, étages)
- Réductions (volume important)

### Génération PDF
- 2 PDFs générés automatiquement
- PDF Standard : sans emballage
- PDF Complet : avec emballage cartons
- Design professionnel aux couleurs Novalia

### Email automatique
- Envoi automatique au client
- Copie à info@novaliagroup.ch
- 2 PDFs attachés
- Template HTML responsive

### Carte interactive
- OpenStreetMap + Leaflet
- Marqueurs personnalisés
- Tracé de la route
- Calcul précis de distance

### Interface responsive
- Mobile-friendly
- Tablette optimisée
- Desktop complet

## Sécurité

- Validation des données (sanitize, escape)
- Nonces WordPress pour AJAX
- Vérification des permissions admin
- Protection SQL injection
- XSS protection

## Performance

- CSS/JS minifiés en production (recommandé)
- Cache des requêtes de base de données
- Optimisation des images
- Lazy loading des cartes

## Support et maintenance

### Base de données

Tables créées :
- `wp_novalia_items` : Objets de déménagement
- `wp_novalia_tarifs` : Tarifs et prix
- `wp_novalia_devis` : Devis clients
- `wp_novalia_devis_items` : Items par devis

### Logs

Les erreurs sont enregistrées dans :
- Debug WordPress (`wp-content/debug.log`)
- Logs serveur

Activer le mode debug dans `wp-config.php` :
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## Dépannage

### Les emails ne partent pas
1. Vérifier la configuration SMTP
2. Tester avec un plugin SMTP
3. Vérifier les logs serveur
4. Tester la fonction `wp_mail()`

### La carte ne s'affiche pas
1. Vérifier que Leaflet est chargé
2. Vérifier la console JavaScript
3. Tester la connexion à OpenStreetMap

### Les PDFs ne se génèrent pas
1. Vérifier que TCPDF est installé
2. Vérifier les permissions du dossier uploads
3. Vérifier les logs PHP

### Distance non calculée
1. Vérifier la connexion à OSRM
2. Tester avec des adresses suisses valides
3. Vérifier la console réseau

## Mise à jour

Pour mettre à jour le plugin :
1. Sauvegarder la base de données
2. Remplacer les fichiers
3. Vérifier les modifications en staging
4. Déployer en production

## Désinstallation

Pour désinstaller complètement :
1. Désactiver le plugin
2. Si vous souhaitez supprimer les données :
   - Supprimez les tables `wp_novalia_*`
   - Supprimez le dossier du plugin

## Contact

**Novalia Group**
- Email : info@novaliagroup.ch
- Site : https://novaliagroup.ch

## Licence

Ce plugin est propriétaire et développé pour Novalia Group.
Tous droits réservés © 2024 Novalia Group

## Version

**Version 1.0.0**
- Date de sortie : 2024
- WordPress requis : 5.8+
- PHP requis : 7.4+
- Testé jusqu'à : WordPress 6.4