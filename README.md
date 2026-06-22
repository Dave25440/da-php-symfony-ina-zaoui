# Ina Zaoui - Site portfolio et back-office pour photographes

## Description

Ina Zaoui est une photographe spécialisée dans les photos de paysages du monde entier. Son site est un portfolio qui inclut un back-office permettant aux jeunes photographes de promouvoir leur travail.  
Ce projet est une refonte optimisée et mise à jour du site vers Symfony 7.4.

## Prérequis

- PHP 8.4 ou ultérieur
- Composer (gestionnaire de dépendances PHP) installé
- Symfony CLI installé
- Serveur web (exemple : Apache avec MAMP)
- Base de données MySQL ou équivalente

## Installation

1. **Clonage du dépôt**

    ```bash
    git clone <url-du-projet>
    cd <dossier-du-projet>
    ```

2. **Décompression des archives ZIP**

    Décompressez manuellement les archives **uploads-part\*.zip** avec votre explorateur de fichiers ou directement en ligne de commande :
    ```bash
    for zip in uploads-part*.zip; do unzip -o "$zip"; done
    ```

3. **Installation des dépendances**

    ```bash
    composer install
    ```

4. **Configuration**

    Dupliquez le fichier **.env** et renommez-le **.env.local**.  
    Modifiez les identifiants de connexion à la base de données si nécessaire.

5. **Base de données**

    **Méthode A : Génération des données**

    Créez la base de données :
    ```bash
    symfony console doctrine:database:create --if-not-exists
    ```

    Créez les tables :
    ```bash
    symfony console make:migration
    symfony console doctrine:migrations:migrate
    ```

    Chargez les fixtures :
    ```bash
    symfony console doctrine:fixtures:load
    ```

    Répétez les opérations suivantes pour créer la base de test :
    ```bash
    symfony console doctrine:database:create --if-not-exists --env=test
    symfony console doctrine:migrations:migrate --env=test
    symfony console doctrine:fixtures:load --env=test
    ```

    **Méthode B : Utilisation de la sauvegarde**

    Importez le fichier **ina_zaoui.sql** dans votre base de données avec phpMyAdmin, Adminer ou directement en ligne de commande :
    ```bash
    mysql -u <utilisateur> -p <nom_de_la_base> < ina_zaoui.sql
    ```

    Répétez l'opération pour créer la base **ina_zaoui_test**.

## Utilisation

1. **Démarrage de l'application**

    ```bash
    symfony server:start -d
    ```

2. **Connexion au site**

    Pour tester manuellement l'application, vous pouvez vous connecter avec l'un des comptes suivants et le mot de passe **password** :
    - Admin : ina@zaoui.com
    - Invité : invite+0@example.com
    - Invité bloqué : invite+1@example.com

3. **Exécution des tests**

    ```bash
    symfony php bin/phpunit
    ```

4. **Lancement de l'analyse statique**

    Symfony CLI :
    ```bash
    symfony php vendor/bin/phpstan --memory-limit=512M
    ```

    Composer :
    ```bash
    composer phpstan
    ```

## Notes

- Les images du site sont optimisées au format WebP.
- Un rapport de couverture de code est disponible dans le dossier *tests/coverage*.
- Un workflow GitHub Actions d’intégration continue exécute automatiquement les tests et l’analyse statique à chaque push ou pull request.