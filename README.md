# sortir.com de Guillaume

**Projet ENI Symfony 5 :: février 2020**  

Peut être intéressant à explorer : 
- Les fixtures maisons, (dossier `Command`)
- Le système d'oubli de mot de passe sécuritaire (`SecurityController`)
- Le service pour MapBox (dossier `Geolocation`)
- Requête AJAX dans `templates/event/create.html.twig`
- L'authenticator dans `Security/` (nouvelle manière de gérer l'authentification)
- Les tests fonctionnels dans `/tests/`

### Installation

1. Dans cmder, naviguer vers votre dossier web : 
    ```bash
    cd /wamp64/www/
    ```
2. Cloner ce projet : 
    ```bash
    git clone https://github.com/gsylvestre/sortir.git sortir-guillaume
    ```
3. Naviguer _dans_ le répertoire de ce projet : 
    ```bash
    cd sortir-guillaume/
    ```
4. Installer les dépendances de Composer
    ```bash
    composper install
    ```
5. Dans PHPMyAdmin, importer la base de données `sortir-guillaume.sql` (à la racine du dossier)

6. Configurer la base de données dans le fichier `.env`  

7. Mettre à jour l'état de sortie dans cmder : 
    ```bash
    php bin/console app:update-event-states
    ```
8. Dans un navigateur, se rendre sur :  
http://localhost/sortir-guillaume/public/  

### Comptes utilisateur
**Simple participant :**   
mail : yo@yo.com  
mdp  : yoyoyo

**Administrateur :**   
mail : admin@admin.com  
mdp  : admin

**Tous les autres users**  
mdp : ryanryan

### Tests
Pour lancer les tests fonctionnels (définis dans le répertoire `/tests/`), dans cmder :  
```bash
php bin/phpunit
```