# Novalia Devis - Plugin WordPress

Plugin complet d'estimation de devis de déménagement avec calcul automatique, génération PDF et envoi par email.

## 🚀 Installation

### Prérequis
- WordPress 6.0 ou supérieur
- PHP 8.0 ou supérieur
- Composer (pour installer TCPDF)

### Étapes d'installation

1. **Télécharger le plugin**
   ```bash
   cd wp-content/plugins/
   # Copier le dossier novalia-devis ici
   ```

2. **Installer les dépendances**
   ```bash
   cd novalia-devis
   composer install
   ```

3. **Activer le plugin**
   - Aller dans WordPress Admin → Extensions
   - Activer "Novalia Devis"

4. **Insérer le shortcode**
   - Créer une nouvelle page
   - Ajouter le shortcode : `[novalia_devis_form]`
   - Publier la page

## ⚙️ Configuration

### 1. Paramètres de l'entreprise
`Novalia Devis → Paramètres → Entreprise`

- Nom de l'entreprise
- Adresse complète
- Téléphone et email
- SIRET
- Logo (JPG, PNG, max 2 MB)

### 2. Tarification
`Novalia Devis → Tarification`

- Prix au kilomètre
- Prix au mètre cube
- Frais fixes
- Frais par étage
- Service d'emballage
- Assurance
- Montant minimum

### 3. Objets de déménagement
`Novalia Devis → Objets`

- 50 objets prédéfinis inclus
- Ajouter/modifier/supprimer des objets
- Import/Export CSV
- Catégorisation

### 4. Emails
`Novalia Devis → Paramètres → Emails`

- Personnaliser les sujets
- Email administrateur
- Activer les notifications

## 📖 Utilisation

### Shortcode
```
[novalia_devis_form]
```

**Attributs disponibles :**
```
[novalia_devis_form show_title="yes" title="Votre titre personnalisé"]
```

### Parcours utilisateur

1. **Étape 1 : Adresses**
   - Saisir l'adresse de départ (autocomplétion)
   - Saisir l'adresse d'arrivée (autocomplétion)
   - Indiquer les étages et présence d'ascenseur
   - Calcul automatique de la distance

2. **Étape 2 : Objets**
   - Sélectionner les objets à déménager
   - Ajuster les quantités
   - Ajouter des objets personnalisés
   - Options : emballage, assurance
   - Calcul du volume total en temps réel

3. **Étape 3 : Récapitulatif**
   - Vérifier les informations
   - Voir l'estimation de prix détaillée
   - Remplir ses coordonnées
   - Recevoir le devis PDF par email

## 🔌 API REST

Le plugin expose une API REST WordPress pour les développeurs :

### Endpoints disponibles

**Autocomplétion d'adresses**
```
GET /wp-json/novalia-devis/v1/autocomplete?query=paris
```

**Calcul de distance**
```
POST /wp-json/novalia-devis/v1/distance
{
  "address_from": "Paris, France",
  "address_to": "Lyon, France"
}
```

**Calcul de prix**
```
POST /wp-json/novalia-devis/v1/calculate
{
  "distance": 450,
  "volume": 15.5,
  "floors_from": 3,
  "has_elevator_from": false
}
```

**Créer un devis**
```
POST /wp-json/novalia-devis/v1/quote
{
  "customer_name": "Dupont",
  "customer_email": "dupont@example.com",
  "address_from": "...",
  "address_to": "...",
  "distance": 450,
  "total_volume": 15.5,
  "items": [...]
}
```

**Récupérer un devis**
```
GET /wp-json/novalia-devis/v1/quote/{id}
```

## 🗄️ Structure de la base de données

### Table : `wp_nd_items`
Objets de déménagement prédéfinis
```sql
- id (bigint)
- name (varchar)
- volume (decimal)
- category (varchar)
- is_active (tinyint)
- created_at (datetime)
```

### Table : `wp_nd_quotes`
Devis générés
```sql
- id (bigint)
- quote_number (varchar)
- customer_name (varchar)
- customer_email (varchar)
- customer_phone (varchar)
- address_from (text)
- address_to (text)
- distance (decimal)
- total_volume (decimal)
- total_price (decimal)
- status (varchar)
- pdf_path (varchar)
- created_at (datetime)
```

### Table : `wp_nd_quote_items`
Objets par devis
```sql
- id (bigint)
- quote_id (bigint)
- item_name (varchar)
- item_volume (decimal)
- quantity (int)
```

## 🎨 Personnalisation

### Styles CSS
Surcharger les styles dans votre thème :
```css
/* Personnaliser le wizard */
.nd-wizard-container {
    max-width: 1200px;
    /* Vos styles... */
}

/* Personnaliser les boutons */
.nd-btn-primary {
    background: votre-couleur;
}
```

### Filtres WordPress

**Modifier les tarifs**
```php
add_filter('nd_pricing_settings', function($pricing) {
    $pricing['price_per_km'] = 2.00;
    return $pricing;
});
```

**Modifier le contenu de l'email client**
```php
add_filter('nd_customer_email_subject', function($subject, $quote) {
    return 'Votre devis ' . $quote['quote_number'];
}, 10, 2);
```

**Ajouter des données au devis**
```php
add_filter('nd_quote_data', function($data) {
    $data['custom_field'] = 'valeur';
    return $data;
});
```

### Actions WordPress

**Après création d'un devis**
```php
add_action('nd_quote_created', function($quote_id, $quote_data) {
    // Votre code personnalisé
    // Ex : envoyer à un CRM, notifier Slack, etc.
}, 10, 2);
```

**Avant envoi de l'email**
```php
add_action('nd_before_send_email', function($quote) {
    // Votre code
}, 10, 1);
```

## 📊 Import/Export

### Export CSV des objets
`Novalia Devis → Objets → Exporter en CSV`

Format du CSV :
```
Nom,Volume (m³),Catégorie,Actif
Canapé 3 places,2.0,Salon,Oui
Table à manger,0.8,Salle à manger,Oui
```

### Import CSV des objets
`Novalia Devis → Objets → Importer depuis CSV`

### Export des devis
`Novalia Devis → Devis → Exporter (CSV)`

## 🔒 Sécurité

- Sanitization de toutes les données utilisateur
- Nonces WordPress pour les formulaires AJAX
- Échappement des sorties (esc_html, esc_attr)
- Vérification des permissions utilisateur
- Protection contre les injections SQL (prepared statements)
- Validation des emails et téléphones
- Protection CSRF

## 🌍 Traduction

Le plugin est prêt pour la traduction (i18n).

**Text domain :** `novalia-devis`

### Créer une traduction

1. Utiliser Poedit ou Loco Translate
2. Scanner le plugin pour les chaînes
3. Traduire dans votre langue
4. Placer les fichiers `.mo` dans `/languages/`

Fichiers de traduction :
```
/languages/novalia-devis-fr_FR.po
/languages/novalia-devis-fr_FR.mo
```

## 🐛 Dépannage

### Le devis n'est pas envoyé par email

1. Vérifier les paramètres email WordPress
2. Installer un plugin SMTP (WP Mail SMTP)
3. Tester avec `Novalia Devis → Paramètres → Emails → Envoyer un test`

### L'autocomplétion ne fonctionne pas

1. Vérifier la connexion internet
2. L'API Nominatim peut avoir des limites de requêtes
3. Attendre quelques secondes entre les recherches

### Erreur "TCPDF n'est pas installé"

```bash
cd wp-content/plugins/novalia-devis
composer install
```

### Les objets ne s'affichent pas

1. Vérifier que des objets sont actifs dans `Novalia Devis → Objets`
2. Vider le cache WordPress si vous utilisez un plugin de cache
3. Vérifier la console JavaScript pour les erreurs

### Problème de permissions

Ajouter les capacités à un rôle :
```php
$role = get_role('editor');
$role->add_cap('manage_novalia_devis');
$role->add_cap('view_novalia_quotes');
```

## 📈 Performance

### Optimisations incluses

- Mise en cache des coordonnées géographiques (30 jours)
- Requêtes SQL optimisées avec index
- Chargement conditionnel des assets (admin/frontend)
- Lazy loading des bibliothèques JavaScript
- Compression des images

### Recommandations

- Activer un plugin de cache (WP Rocket, W3 Total Cache)
- Utiliser un CDN pour les assets statiques
- Optimiser la base de données régulièrement
- Limiter le nombre d'objets actifs (< 200 recommandé)

## 🔄 Mises à jour

Le plugin vérifie automatiquement la version de la base de données et effectue les migrations nécessaires.

### Désinstallation propre

Le fichier `uninstall.php` supprime :
- Toutes les tables créées
- Toutes les options du plugin
- Les fichiers uploadés
- Les métadonnées utilisateurs
- Les capacités ajoutées

**Note :** Les données sont conservées lors de la désactivation, supprimées uniquement lors de la désinstallation complète.

## 🤝 Support

Pour toute question ou problème :

1. Vérifier la documentation ci-dessus
2. Consulter les logs d'erreur WordPress
3. Activer WP_DEBUG pour plus d'informations
4. Contacter le support

## 📝 Changelog

### Version 1.0.0
- Version initiale
- Wizard en 3 étapes
- 50 objets prédéfinis
- Autocomplétion OpenStreetMap
- Génération PDF avec TCPDF
- Envoi automatique par email
- Interface d'administration complète
- REST API
- Import/Export CSV
- Statistiques et graphiques

## 📄 Licence

GPL v2 or later

## 👨‍💻 Crédits

- **OpenStreetMap / Nominatim** : Géocodage et autocomplétion
- **Leaflet.js** : Carte interactive
- **TCPDF** : Génération de PDF
- **DataTables** : Tableaux interactifs
- **Chart.js** : Graphiques

---

**Développé avec ❤️ pour Novalia**