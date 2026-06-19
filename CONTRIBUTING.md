# Contribuer au projet Ina Zaoui

Merci de prendre du temps pour contribuer au projet Ina Zaoui. Ce document décrit les règles et bonnes pratiques à respecter pour garantir une collaboration efficace et un code de qualité.

## Conventions de nommage

1. **Branches**

    Nommez la branche en indiquant son type :
    - **feature** : ajoute une nouvelle fonctionnalité.
    - **bugfix** : corrige un bug.
    - **hotfix** : corrige un bug critique.
    - **chore** : nettoie le code.

    Ajoutez-y un slash et une description courte en utilisant la convention kebab-case :  
    `feature/<description-courte>`
    
    Exemples :
    - `feature/add-pagination-to-guests-page`
    - `bugfix/pagination-on-guests-page`
    - `chore/edit-guests-template`

2. **Commits**

    Rédigez des messages clairs et concis :
    - `Create setUp, testPage and pageProvider methods in HomeControllerTest`
    - `Generate code coverage report in tests/coverage folder`

    Conservez un style uniforme et évitez les messages trop vagues.

3. **Classes**

    Si vous créez de nouvelles classes, nommez-les en PascalCase :  
    `HomeControllerTest`

4. **Méthodes et variables**

    Les méthodes et variables sont nommées en camelCase :
    - `testNavigationForAuthenticatedUser`
    - `expectedGuestCount`

    De manière générale, l'anglais est privilégié.

## Procédure de contribution

1. **Proposition de fonctionnalités**

    - Créez une branche locale depuis *main* en respectant les conventions de nommage (cf. ci-dessus).
    - Développez la fonctionnalité en suivant les bonnes pratiques (cf. ci-dessous).
    - Exécutez les tests, lancez une analyse statique et vérifiez que tout passe avant de valider vos commits.
    - Poussez votre branche vers le dépôt.
    - Créez une pull request (PR) en décrivant de manière exhaustive les modifications apportées.

    Après revue, votre PR sera validée et fusionnée par un mainteneur du dépôt si elle répond aux exigences de la politique de validation (cf. ci-dessous).

2. **Soumission de problèmes**

    Créez une **issue** et décrivez de manière claire et complète le bug ou problème rencontré. Joignez-y des captures d'écran si nécessaire.

## Politique de validation

- Respecter les conventions de nommage et les bonnes pratiques
- Passer tous les tests unitaires et fonctionnels
- Ne pas réduire la couverture de code
- Ne pas produire d'erreurs d'analyse statique
- Proposer un code clair, bien structuré et documenté
- Fournir si nécessaire les tests automatisés de la nouvelle fonctionnalité

## Bonnes pratiques

1. **Style**

    - **Aborescence** : respectez l'arborescence établie si vous créez de nouvelles classes ou fichiers.
    - **DocBlocks** : décrivez le rôle et listez les paramètres et le type de retour des méthodes.
    - **Commentaires** : expliquez les parties de code complexes.

    Vous pouvez rédiger les DocBlocks et les commentaires en français :
    ```php
    /**
     * Affiche la liste des invité·es.
     *
     * @param Request $request
     * @return Response
     */

    // Extraction du nom de l'invité·e sans le nombre de médias
    ```

2. **Tests** : implémentez ou complétez les tests PHPUnit existants pour assurer la robustesse et la maintenabilité du code.
3. **Analyse statique** : lancez PHPStan pour détecter les éventuelles erreurs avant de push le code sur le dépôt.
4. **Documentation** : proposez une mise à jour du fichier *README.md* si vos modifications impactent l’usage ou la maintenance du dépôt.

    Pour éviter les régressions, validez régulièrement vos modifications localement avant de push.

---

Merci encore pour vos contributions qui participent activement à la qualité et à l’évolution du projet !  
Pour toute question ou difficulté, n’hésitez pas à nous contacter via les issues.