# sortir.com de Guillaume

**Projet ENI Symfony 139A**  

Peut être intéressant à explorer : 
- Les fixtures maisons (dossier `Command`)
- Le système d'oubli de mot de passe fait maison (`SecurityController`)
- Le service pour MapBox (dossier `Geolocation`) et la carte (`detail.html.twig`)
- Requête AJAX dans `templates/event/create.html.twig`
- Les tests fonctionnels dans `/tests/`
- Le chargement des utilisateurs par .csv (`Admin/UserController`)

### Installation

1. Dans cmder, naviguer vers votre dossier web : 
    ```bash
    cd /Wamp64/www/
    ```
2. Cloner ce projet : 
    ```bash
    git clone https://github.com/gsylvestre/sortir.git sortir-guillaume
    ```
3. Naviguer dans le répertoire de ce projet : 
    ```bash
    cd sortir-guillaume/
    ```
4. Installer les dépendances de Composer
    ```bash
    composer install
    ```
5. Configurer la base de données dans le fichier `.env`  

6. Créer la base de données : 
    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:schema:update --force
    ```
7. Charger les données de test : 
    ```bash
    php bin/console app:fixtures:load
    ```

8. Mettre à jour l'état des sorties : 
    ```bash
    php bin/console app:update-event-states
    ```
   
9. Lancer le serveur : 
   ```bash
   symfony server:start
   ```

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