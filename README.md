# sortir.com de Guillaume

**Projet ENI Symfony :: février 2020**

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
5. Configurer la base de données dans .env  

6. Créer la bdd avec : 
    ```bash
    php bin/console doctrine:database:create
    ```
7. Créer les tables : 
    ```bash
    php bin/console doctrine:schema:update --force
    ```
8. Importer les données de test : 
    ```bash
    php bin/console app:fixtures:load
    ```
9. Mettre à jour l'état de sortie : 
    ```bash
    php bin/console app:update-event-states
    ```
10. Dans un navigateur, se rendre sur :  
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