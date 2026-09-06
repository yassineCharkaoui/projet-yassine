# Contrôle de saisie — pharmacie

Analyse du code de `D:/web/projet yassine`, le 6 septembre 2026.

## PHP ou JavaScript ?

Les deux. HTML définit les champs ; JavaScript contrôle la saisie dans le navigateur ; PHP doit vérifier les données reçues avant enregistrement ; SQL impose les contraintes de stockage. Un fichier `.php` peut contenir du HTML et du JavaScript : l'extension ne détermine pas où chaque contrôle s'exécute.

Les contrôles navigateur peuvent être contournés. La validation PHP doit rester l'autorité pour accepter une valeur.

## Où chercher dans les fichiers ?

Chemins relatifs au dossier de ce README. Rechercher les fonctions indiquées pour retrouver leur emplacement après modification.

| Fichier | Responsabilité |
|---|---|
| `view/responsable/medicaments/add.php` | Formulaire commun ajout/modification, règles `data-*`, initialisation de `FormValidator`, compteur et aperçu image |
| `view/responsable/medicaments/edit.php` | Inclut `add.php` ; pas de validation séparée |
| `view/assets/js/validation.js` | Moteur JavaScript `FormValidator` |
| `view/assets/css/validation.css` | Présentation des erreurs, aucune règle métier |
| `controller/ResponsableController.php` | `processAddMedicament()` et `processEditMedicament()` : POST, CSRF, upload, conversions et appel modèle |
| `controller/config.php` | `escape()`, `verifyCSRFToken()`, session et helpers d'accès |
| `model/Medicament.php` | `create()` et `update()` : SQL préparé avec PDO |
| `schema.sql` | Types et contraintes SQL |
| `view/auth/login.php`, `view/auth/register.php` | HTML et JavaScript propres à l'authentification |
| `controller/AuthController.php` | Vérifications PHP de connexion et inscription |
| `view/client/ordonnances/soumettre.php` | Champs requis, dates, quantités et JavaScript ordonnance |
| `controller/ClientController.php` | Vérifications serveur des ordonnances et du panier |
| `model/Panier.php` | `valider()` : disponibilité, prescription et stock |
| `view/index.php` | Capture des exceptions, journalisation `[Router Error]`, page 500 |

`add_v2.php` est une variante ; le contrôleur courant charge `add.php`. `add_OLD.php` est une ancienne version. Modifier une copie ne change pas forcément le formulaire utilisé.

## Parcours d'une modification

1. `editMedicament()` lit le médicament dans le modèle.
2. `edit.php` inclut `add.php`, qui génère le HTML avec les valeurs enregistrées.
3. Le navigateur charge `validation.js`, puis crée `new FormValidator('medicamentForm', ...)`.
4. `init()` ajoute `novalidate`, ce qui désactive la validation native HTML de ce formulaire.
5. `blur` et `input` déclenchent des contrôles ; `handleSubmit()` vérifie tous les champs et appelle `form.submit()` si tout passe.
6. PHP reçoit le POST dans `processEditMedicament()`, vérifie méthode et CSRF, traite les valeurs puis appelle le modèle.
7. Le modèle exécute SQL ; le contrôleur redirige avec un message flash.

## Règles effectives du formulaire médicaments

| Champ | Navigateur | PHP actuel |
|---|---|---|
| Nom commercial | Obligatoire, 2 à 200 caractères | `trim()`, sans règle métier équivalente |
| Nom générique | Facultatif, maximum 200 caractères | `trim()` |
| DCI, laboratoire | Facultatifs, sans limite déclarée ici | `trim()` |
| Forme, catégorie | Sélection obligatoire | Valeur POST reprise sans liste autorisée serveur |
| Dosage | Obligatoire | `trim()` |
| Prix | Obligatoire, minimum 0, au plus 2 décimales avec point | `floatval()` |
| Stocks disponible/minimum | Obligatoires, numériques, minimum 0 | `intval()` |
| Date de péremption | `type=date`, intention de minimum aujourd'hui avec `data-min` | Chaîne ou NULL, sans contrôle explicite du calendrier |
| Description | Maximum 500 caractères ; compteur et troncature à la saisie | `trim()`, sans limite équivalente |
| Posologie, contre-indications, effets secondaires | Facultatifs, sans règle de longueur déclarée | `trim()` |
| Prescription obligatoire | Oui/Non | La chaîne `'1'` donne 1 ; sinon 0 |
| Image | jpg/jpeg/png/gif/webp, maximum 5 Mio | Taille, type déclaré et MIME réel avec `finfo`, déplacement du fichier |

`trim()` nettoie les espaces ; `intval()` et `floatval()` convertissent. Ces fonctions ne prouvent pas que la saisie initiale est valide. Les textes médicaux sont libres : aucune vérification clinique de posologie n'est implémentée.

## Comment fonctionne validation.js ?

- `getValidationRules()` lit `data-required`, `data-minlength`, `data-maxlength`, `data-min`, `data-max`, `data-pattern`, `data-match` et certains types HTML.
- `validateField()` applique les règles et s'arrête à la première erreur du champ.
- `showError()` ajoute `.is-invalid` et un message avec `textContent`.
- `validateFile()` contrôle le premier fichier : taille et extension du nom.
- `isValidEmail()` utilise une expression régulière simple ; `isValidPhone()` attend un numéro français de 10 chiffres ; `isValidDecimal()` accepte un point et au plus deux décimales.

Exemple : `data-required="true" data-minlength="2"` est interprété par ce JavaScript. Ces attributs seuls n'imposent aucune règle PHP ou HTML native.

## Autres formulaires

### Authentification

`AuthController.php` vérifie les champs vides à la connexion. À l'inscription, il contrôle nom et prénom, email avec `FILTER_VALIDATE_EMAIL`, mot de passe d'au moins 6 caractères avec `strlen()` et égalité de la confirmation. Les vues ajoutent leurs propres contrôles HTML et JavaScript. `strlen()` compte les octets : les caractères accentués peuvent entraîner une différence avec la longueur JavaScript.

### Ordonnances

`soumettre.php` impose notamment médecin, date de prescription, médicament et quantité minimale de 1. Il définit des bornes HTML de dates et adapte certains champs par JavaScript. `ClientController.php` vérifie notamment médecin et date non vides ; cela ne constitue pas une validation complète du calendrier.

### Panier

Les vues fixent des bornes de quantité. `ClientController.php` appelle `Panier::valider()` avant paiement. Ce modèle relit les médicaments et vérifie disponibilité, prescription et stock : ce sont des contrôles métier côté serveur.

## Limites constatées — améliorations à prévoir

Ces limites décrivent le code existant ; elles ne sont pas toutes corrigées par la réparation de l'affichage.

1. Les médicaments n'ont pas de validation PHP complète des champs obligatoires, longueurs, nombres, entiers, dates et listes autorisées. Il faudrait une fonction commune aux deux traitements, exécutée avant upload et SQL, avec erreurs par champ et conservation de la saisie.
2. Le minimum de date utilise `parseFloat()` : `2026-09-06` est principalement comparé comme 2026. Il faut une vraie comparaison de dates et une décision métier pour les médicaments déjà périmés.
3. Les stocks n'ont pas de règle JavaScript explicite d'entier ; `novalidate` désactive le contrôle natif du pas. PHP tronque les fractions avec `intval()`.
4. Sans JavaScript, les attributs `data-*` ne bloquent pas la soumission. Des attributs HTML natifs aideraient, sans remplacer PHP.
5. `data-required="false"` reste interprété comme obligatoire car le moteur vérifie aussi la présence de l'attribut.
6. Les messages date et URL existent dans `ERROR_MESSAGES`, mais sans branche correspondante dans `validateField()`.
7. L'upload vérifie le MIME, mais l'extension finale provient du nom fourni. Elle devrait être déduite du MIME autorisé ; toutes les erreurs d'upload devraient être traitées explicitement.
8. `Medicament::update()` utilise `isset()` : une valeur NULL est ignorée. Effacer une date existante ne la remet donc pas nécessairement à NULL.
9. Les types SQL ne couvrent pas toutes les règles : un entier signé n'interdit pas un stock négatif. Ne pas relancer `schema.sql` pour réparer ce problème : il contient `DROP DATABASE`.

## Posologie, erreur 500 et boutons absents

Une interruption PHP pendant la génération du textarea peut empêcher sa fermeture et l'affichage des boutons suivants. Le HTML 500 fourni ne révèle pas l'exception exacte.

Le helper local `escape(?string $string)` acceptait déjà NULL et le convertissait en chaîne vide. Les 13 scénarios initiaux NULL/échappement passaient avant cette intervention : l'erreur signalée n'a donc pas été reproduite telle quelle dans cette version locale.

La protection a été complétée dans `add.php` et `add_v2.php` : `escape($medicament['posologie'] ?? '')`, ainsi que sur les autres textes facultatifs, la date et les sélections. Une clé absente ou NULL s'affiche vide ; le HTML reste échappé. Les données médicales en base ne sont pas modifiées.

`escape()` protège la sortie HTML, CSRF protège une soumission de session et PDO paramétré sépare SQL et données. Aucun de ces mécanismes ne remplace une règle métier de validation.

Si l'erreur persiste sur le site servi, vérifier la copie utilisée et la ligne `[Router Error]` du journal PHP configuré : le routeur y inscrit message, fichier et ligne. La configuration Apache principale locale pointe vers `C:/xampp/htdocs`, alors que ce projet est dans `D:/web` ; cela ne prouve pas que le navigateur utilise cette copie.

## Tests reproductibles

Depuis le dossier du projet :

```powershell
python tests/check_optional_medicine_fields.py
python tests/check_medicine.py
php -l view/responsable/medicaments/add.php
php -l view/responsable/medicaments/add_v2.php
```

Le premier test rend le formulaire pour 24 scénarios : valeurs NULL, clés absentes et texte contenant une fermeture de textarea et un script. Il exige absence d'avertissements PHP, présence des deux boutons et échappement du texte. Le second utilise SQLite temporaire et le contrôleur réel pour sept scénarios de création/modification et prescription. Ces tests ne modifient pas la base de production ; PHP CLI doit pouvoir écrire dans son dossier de sessions. Ils ne remplacent pas une vérification du site réellement servi.
