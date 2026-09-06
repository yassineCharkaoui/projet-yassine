# 💊 Système de Gestion de Pharmacie

## 📋 Description
Système complet de gestion de pharmacie développé en **PHP 8** avec architecture **MVC**. 

Ce système permet la gestion complète d'une pharmacie avec 3 types d'utilisateurs :
- 👨‍💼 **Responsable** : Gestion des stocks, validation des demandes
- 👨‍⚕️ **Pharmacien** : Validation des ordonnances, détection des interactions médicamenteuses
- 👤 **Client** : Consultation du catalogue, soumission d'ordonnances en ligne

## 🚀 Installation Rapide

### Prérequis
- **XAMPP** (Apache + MySQL + PHP 8+)
- Navigateur web moderne

### Étapes d'installation

1. **Copier le projet** dans le dossier servi par Apache, sous le nom de votre choix. Aucun chemin Windows ni nom de dossier particulier n'est requis.

2. **Démarrer XAMPP**
   - Lancer Apache
   - Lancer MySQL

3. **Configurer la connexion** dans `controller/config.php`. Conserver la base existante. Pour une installation neuve uniquement, préparer une base séparée à partir de `schema.sql`.

4. **Ouvrir le point d'entrée racine** : `<URL-du-projet>/index.php`. La connexion utilise `index.php?controller=auth&action=login`. Ne pas utiliser les fichiers de templates comme points d'entrée.

5. **Vérifier les chemins** avec `python tests/check_paths.py` (PHP et Python doivent être disponibles). Ces tests utilisent des données simulées et ne modifient pas la base.

### URLs portables

Le préfixe public est déduit de l'URL du script exécuté. Les dossiers renommés, les espaces et une installation à la racine du site sont pris en charge. Pour un alias Apache particulier, définir la variable d'environnement `APP_BASE_PATH` avec un chemin URL, par exemple `/pharmacie/`. Ne jamais y mettre un chemin disque ou un nom d'hôte.

`appUrl()` construit les destinations locales et `buildUrl()` ajoute les paramètres de contrôleur et d'action. `redirect()` utilise le même préfixe. Le routeur historique sous `view/` renvoie vers la racine avec un statut 307 pour conserver les données POST. Les accès GET directs aux templates de connexion/inscription sont redirigés ; leurs POST directs sont refusés avec un statut 405.

Le routeur, FPDF (avec ses polices et sa licence) et les ressources de validation ont été récupérés de l'ancien projet. Les cinq vues absentes des deux copies ont été ajoutées. Les anciens scripts d'installation, de diagnostic, de modification des mots de passe et de migration restent uniquement dans la copie de référence ; ils ne sont pas nécessaires à l'exécution et n'ont pas été lancés.

### Vérification sur le PC qui héberge la base

- Ouvrir le point d'entrée racine et vérifier connexion réussie/échouée, inscription et déconnexion avec des comptes de test pour chaque rôle.
- Vérifier les tableaux de bord, recherches et filtres, panier, soumission d'ordonnance, demandes de renouvellement et téléchargements PDF avec les données de test de cette installation.
- Dans l'onglet Réseau du navigateur, contrôler l'URL, la méthode POST/GET et les redirections. Toutes les actions doivent viser le `index.php` racine du projet, sans chemin disque ni `/view/auth/index.php`.
- En cas de réponse 500, consulter les journaux PHP. Les tests automatisés simulent PDO : ils vérifient les chemins et le comportement HTTP, pas la connexion ni le schéma de la base réelle.

## 👥 Comptes de Test

### Responsable
- **Email** : responsable@pharmacie.com
- **Mot de passe** : password123

### Pharmaciens
- **Email** : marie.dupont@pharmacie.com
- **Mot de passe** : password123

- **Email** : jean.martin@pharmacie.com
- **Mot de passe** : password123

### Clients
- **Email** : sophie.dubois@email.com
- **Mot de passe** : password123

- **Email** : pierre.bernard@email.com
- **Mot de passe** : password123

## 🏗️ Architecture du Projet

**Structure Réorganisée v2.0** - Pour plus de détails, voir [STRUCTURE_REORGANISEE.md](STRUCTURE_REORGANISEE.md)

```
projet yassine/
├── controller/                       # Contrôleurs (logique métier)
│   ├── config.php                    # Configuration BDD + fonctions utilitaires
│   ├── AuthController.php            # Authentification (login/register/logout)
│   ├── ResponsableController.php     # Gestion stocks et validation
│   ├── PharmacienController.php      # Validation ordonnances
│   ├── ClientController.php          # Interface client + panier
│   ├── PDFGenerator.php              # Génération PDF ordonnances
│   └── CurrencyHelper.php            # Conversion devises
├── model/                            # Modèles (accès aux données)
│   ├── Utilisateur.php               # Modèle utilisateurs
│   ├── Medicament.php                # Modèle médicaments
│   ├── Ordonnance.php                # Modèle ordonnances
│   ├── Transaction.php               # Modèle transactions
│   ├── Panier.php                    # Modèle panier d'achat
│   ├── Interaction.php               # Modèle interactions médicamenteuses
│   └── DemandeRenouvellement.php     # Modèle demandes renouvellement
├── view/                             # Vues et ressources front-end
│   ├── assets/                       # ✨ Assets CSS/JS
│   │   ├── css/
│   │   │   └── validation.css        # Styles validation formulaires
│   │   └── js/
│   │       └── validation.js         # Scripts validation dynamique
│   ├── uploads/                      # ✨ Uploads sécurisés
│   │   ├── medicaments/              # Images médicaments
│   │   │   └── .htaccess             # Protection (images uniquement)
│   │   ├── ordonnances/              # Scans ordonnances
│   │   │   └── .htaccess             # Protection (PDF + images)
│   │   └── pdf/                      # PDFs générés
│   │       └── .htaccess             # Protection (PDF uniquement)
│   ├── vendor/                       # ✨ Bibliothèques externes
│   │   └── fpdf/                     # Bibliothèque FPDF
│   │       └── fpdf.php
│   ├── layout/
│   │   ├── header.php                # En-tête commun avec indicateur panier
│   │   └── footer.php                # Pied de page commun
│   ├── auth/
│   │   ├── login.php                 # Page de connexion
│   │   └── register.php              # Page d'inscription
│   ├── responsable/
│   │   ├── dashboard.php             # Tableau de bord responsable
│   │   ├── medicaments/              # Gestion médicaments (CRUD)
│   │   ├── demandes/                 # Gestion demandes renouvellement
│   │   └── rapports/                 # Rapports stocks
│   ├── pharmacien/
│   │   ├── dashboard.php             # Tableau de bord pharmacien
│   │   ├── ordonnances/              # Validation ordonnances
│   │   ├── medicaments/              # Consultation catalogue
│   │   └── historique.php            # Historique ventes
│   └── client/
│       ├── dashboard.php             # Tableau de bord client
│       ├── medicaments/              # Catalogue avec panier
│       ├── ordonnances/              # Soumission ordonnances
│       ├── panier/                   # ✨ Panier d'achat
│       │   ├── cart.php              # Gestion panier
│       │   └── checkout.php          # Processus paiement
│       └── historique.php            # Historique achats détaillé
├── index.php                         # Point d'entrée (routeur MVC)
└── schema.sql                        # Schéma de la base de données
```

### ✨ Nouveautés v2.0
- **Structure MVC stricte** : Assets, uploads et vendor déplacés sous `view/`
- **Routage portable** : Point d'entrée racine et préfixe URL indépendant du nom du dossier
- **Sécurité renforcée** : Fichiers `.htaccess` automatiques pour tous les uploads
- **Panier d'achat** : Système complet pour médicaments sans ordonnance

## 🗄️ Base de Données

### Tables principales
- **utilisateur** : Gestion des utilisateurs (3 rôles)
- **medicament** : Catalogue des médicaments (avec champ `image`)
- **ordonnance** : Ordonnances des clients
- **ordonnance_medicament** : Détail des médicaments par ordonnance
- **transaction** : Historique des achats
- **transaction_detail** : ✨ Détails des achats (médicaments, quantités, prix)
- **interaction_medicamenteuse** : Base d'interactions connues
- **alerte_interaction** : Alertes détection automatique
- **demande_renouvellement** : Demandes de renouvellement
- **log_action** : Journalisation des actions critiques

### Vues
- **vue_ordonnances_completes** : Vue complète des ordonnances
- **vue_stock_critique** : Médicaments en stock critique

### Triggers
- Détection automatique des interactions médicamenteuses
- Mise à jour automatique des stocks

### Procédures stockées
- Recherche avancée de médicaments
- Statistiques par période

## ✨ Fonctionnalités

### 👨‍💼 Responsable
- ✅ Gestion complète des médicaments (CRUD)
- 📸 Upload et affichage d'images des médicaments
- 💱 Conversion de prix en 3 devises (EUR, USD, TND)
- ↕️ Tri par prix (croissant/décroissant)
- 📊 Tableau de bord avec statistiques
- ⚠️ Alertes stocks critiques
- 📋 Validation des demandes de renouvellement
- 👥 Gestion des utilisateurs
- 📈 Rapports détaillés

### 👨‍⚕️ Pharmacien
- 📝 Validation/Rejet des ordonnances
- 💊 Traitement avec vérification stock
- ⚠️ Détection automatique des interactions
- 📜 Historique des transactions
- 🔔 Alertes en temps réel

### 👤 Client
- 🔍 Consultation du catalogue avec images
- 📸 Visualisation des images des médicaments
- 💱 Conversion de prix en 3 devises (EUR, USD, TND)
- ↕️ Tri par prix (croissant/décroissant)
- 🛒 **Panier d'achat** pour médicaments sans ordonnance
- 💳 **Processus de paiement** (carte, espèces, chèque)
- 📤 Soumission d'ordonnances en ligne
- 📁 Upload de fichiers (images, PDF)
- 🔄 Demandes de renouvellement
- 📊 **Historique détaillé** avec liste des médicaments achetés
- ⚠️ Alertes d'interactions personnalisées
- ✅ Validation JavaScript dynamique des formulaires

## 🔒 Sécurité

- ✅ Protection CSRF sur tous les formulaires
- ✅ Requêtes préparées PDO (protection injection SQL)
- ✅ Validation côté serveur ET client (JavaScript dynamique)
- ✅ Hashage des mots de passe (password_hash)
- ✅ Gestion sécurisée des sessions
- ✅ Upload sécurisé (validation type/taille MIME réelle)
- ✅ Protection des dossiers sensibles (.htaccess)
- ✅ Journalisation des actions critiques

## 💱 Conversion de Devises & Tri

### Devises supportées
| Devise | Code | Symbole | Taux (base EUR) |
|--------|------|---------|-----------------|
| Euro | EUR | € | 1.00 |
| Dollar américain | USD | $ | 1.09 |
| Dinar tunisien | TND | د.ت | 3.38 |

### Fonctionnalités
- 💰 **Tri dynamique** : Croissant, Décroissant, ou Par défaut
- 💱 **Conversion temps réel** : Changement de devise avec affichage du taux
- 🔗 **Persistance URL** : Les paramètres restent lors de la navigation
- 📊 **Affichage double** : Prix converti + prix EUR en référence

### Disponible sur
- ✅ Catalogue client
- ✅ Page détails médicament
- ✅ Liste médicaments responsable

Pour plus de détails, voir [CURRENCY_AND_SORTING.md](CURRENCY_AND_SORTING.md)

## 📸 Gestion des Images

### Upload des images
- 📤 **Drag & Drop** moderne avec prévisualisation
- 🎨 **Interface intuitive** : Zone de dépôt stylisée
- ✅ **Validation sécurisée** : Vérification MIME réelle (finfo)
- 📏 **Tailles limitées** : Max 5 MB par image
- 🖼️ **Formats** : JPG, JPEG, PNG, GIF, WebP

### Affichage des images
- 🖼️ **Miniatures** 60x60px dans liste responsable
- 📦 **Cartes** 200px dans catalogue client
- 🔍 **Grande vue** 400px dans page détails
- 🎯 **Placeholder** : Icône 📦 si pas d'image
- 🛡️ **Fallback** : SVG de remplacement si image manquante

### Stockage
- 📁 Dossier : `view/uploads/medicaments/`
- 🔒 Protection : `.htaccess` automatique pour sécurité
- 📝 Base de données : Chemin stocké dans colonne `image`

Pour plus de détails, voir [MIGRATION_VALIDATION_IMAGES.md](MIGRATION_VALIDATION_IMAGES.md)

## 📱 Design Responsive

- Design moderne avec gradients
- Interface adaptative (desktop/tablet/mobile)
- CSS inline dans toutes les vues
- Icônes et badges colorés
- Tableaux interactifs
- Modals et animations

## 🔧 Configuration

### Fichier de configuration : `controller/config.php`

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'projetyassine');
```

### Timeout de session : 1 heure (3600 secondes)

## 📝 Validation JavaScript

- Validation en temps réel
- Calculs automatiques (prix total)
- Vérification force mot de passe
- Prévention des doublons
- Feedback utilisateur immédiat

## 🚨 Dépannage

### Erreur "Erreur de connexion à la base de données"

Consulter les journaux Apache/PHP et vérifier la connexion dans `controller/config.php`. Les anciens scripts de diagnostic ne sont pas distribués dans cette copie.

**Solutions selon le message d'erreur** :

#### 1. "La base de données 'projetyassine' n'existe pas"
```
Vérifier le nom de la base et restaurer la base existante. Pour une installation neuve uniquement, utiliser schema.sql dans une base séparée.
```

#### 2. "Impossible de se connecter au serveur MySQL"
```
✅ Solution : Démarrer MySQL dans XAMPP
1. Ouvrir XAMPP Control Panel
2. Cliquer "Start" à côté de MySQL
3. Attendre que le statut devienne vert
```

#### 3. "Identifiants de connexion MySQL incorrects"
```
✅ Solution : Vérifier controller/config.php
Paramètres par défaut XAMPP :
- Host: localhost
- User: root
- Password: (vide)
```

### La base de données n'existe pas
→ Vérifier le nom configuré et restaurer la base existante.

### Erreur de connexion MySQL
→ Vérifier que MySQL est démarré dans XAMPP
→ Vérifier les identifiants dans `controller/config.php`

### Upload d'ordonnances ne fonctionne pas
→ Vérifier que le dossier `view/uploads/ordonnances/` existe
→ Vérifier les permissions d'écriture (755)
→ Créer le dossier manquant et donner au serveur PHP les droits d'écriture nécessaires.

### Page blanche / Erreur 500
→ Activer l'affichage des erreurs PHP
→ Consulter les logs Apache/PHP

## 📞 Support

Pour toute question ou problème :
1. Vérifier ce README
2. Exécuter `python tests/check_paths.py` pour vérifier les chemins sans modifier la base
3. Consulter les logs dans `log_action` (table BDD)

## 🔄 Après Installation

Les outils d'installation de l'ancienne copie restent des références séparées de cette application.

## 📄 Licence

Projet académique - PHP 8 - Architecture MVC

---

**Développé avec** ❤️ **en PHP 8**
