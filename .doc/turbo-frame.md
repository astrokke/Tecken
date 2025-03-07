# Intégration de Symfony-UX

## Turbo-frame

Turbo-Frames est une fonctionnalité de Hotwire qui permet de mettre à jour des portions spécifiques d'une page sans recharger l'intégralité de celle-ci,
améliorant ainsi l'expérience utilisateur en rendant les interactions plus fluides.

## Prérequis

    Symfony: Version 5.3 ou supérieure.
    Symfony UX: Installé et configuré dans votre projet.
    Stimulus: Utilisé en conjonction avec Symfony UX pour manipuler les comportements sur la page.
    Turbo: Utilisé pour les Turbo-Frames et Turbo-Streams.

## Installation et Configuration

```bash
composer require symfony/ux-turbo
```

## Utilisation de Turbo-Frames

Les Turbo-Frames vous permettent de spécifier des sections de votre page qui peuvent être mises à jour de manière asynchrone sans recharger l'intégralité de la page.
Voici un exemple simple pour illustrer leur utilisation.

### Exemple: Formulaire de Commentaire avec Turbo-Frames

```html
<!-- templates/comment/_form.html.twig -->
<turbo-frame id="new_comment">
  <form action="{{ path('comment_new') }}" method="POST">
    <input type="text" name="comment" placeholder="Your comment" />
    <button type="submit">Submit</button>
  </form>
</turbo-frame>
```

```php
// src/Controller/CommentController.php

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class CommentController extends AbstractController
{
    #[Route('/comment/new', name: 'comment_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $comment = $request->request->get('comment');

        // Logique d'ajout du commentaire (ex. en base de données)

        if ($request->isXmlHttpRequest()) {
            return $this->render('comment/_form.html.twig', [
                'success' => true,
            ]);
        }

        return $this->redirectToRoute('home');
    }
}
```

Dans cet exemple, lorsque l'utilisateur soumet le formulaire, seul le contenu du `turbo-frame` avec l'ID `new_comment` est mis à jour, sans recharger la page complète.

## Explication: Ce que fait Turbo-Frames

Turbo-Frame permet de spécifier une partie de la page HTML (délimitée par une balise <turbo-frame>) qui peut être mise à jour de manière asynchrone lors d'une requête HTTP. Cela se traduit par une meilleure performance et une expérience utilisateur plus fluide, car seule une portion de la page est rechargée, et non l'intégralité.

## Que fait Turbo-Frame exactement ?

    1 - Définition du Contrôleur Stimulus: Un contrôleur Stimulus est défini pour gérer l'envoi du formulaire. Il intercepte l'événement de soumission du formulaire.
    2 - Requête AJAX: Le contrôleur utilise fetch pour envoyer la requête AJAX et mettre à jour le contenu du DOM.
    3 - Mise à Jour du DOM: Le contenu du div avec l'ID new_comment est mis à jour avec la réponse HTML du serveur.

## Reproduction de Turbo-Frame Sans Turbo-Frame

Pour comprendre ce que fait exactement Turbo-Frame, reproduisons son comportement avec du code standard en utilisant Symfony et JavaScript.

### Exemple: Formulaire de Commentaire sans Turbo-Frames

```html
<!-- templates/comment/_form.html.twig -->
<div id="new_comment" data-controller="comment">
  <form data-action="submit->comment#handleSubmit">
    <input type="text" name="comment" placeholder="Your comment" />
    <button type="submit">Submit</button>
  </form>
</div>
```

```ts
// assets/controllers/comment_controller.ts
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["form"];

  async handleSubmit(event: Event) {
    event.preventDefault();

    const form = event.currentTarget as HTMLFormElement;
    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (response.ok) {
        const html = await response.text();
        const container = document.getElementById("new_comment");
        if (container) {
          container.innerHTML = html;
        }
      } else {
        console.error("Error submitting form");
      }
    } catch (error) {
      console.error("Network error:", error);
    }
  }
}
```

```php
// src/Controller/CommentController.php
// (identique à l'exemple précédent)
```

## Explication

Dans cet exemple sans Turbo-Frames :

    1 - Interception du Submit: Un écouteur d'événements JavaScript est utilisé pour intercepter la soumission du formulaire.

    2 - Requête AJAX: La requête est envoyée via fetch, et la réponse est reçue au format HTML.

    3 - Mise à Jour Manuelle: La partie du DOM correspondant au formulaire (#new_comment) est manuellement mise à jour avec la réponse HTML du serveur.

#Conclusion
Turbo-Frame simplifie grandement ce processus en encapsulant toute cette logique. Avec Stimulus et TypeScript, vous pouvez gérer les interactions et les mises à jour de manière déclarative et maintenable. Turbo-Frame vous évite de devoir gérer manuellement les requêtes AJAX et les mises à jour du DOM, en vous offrant une solution élégante et efficace.
Turbo-Frames est un outil puissant pour les développeurs Symfony, simplifiant la création d'applications web réactives. En utilisant Symfony UX avec Turbo, vous pouvez améliorer l'expérience utilisateur de manière significative tout en maintenant votre code clair et maintenable. Stimulus et TypeScript fournissent un moyen moderne et structuré pour manipuler les interactions utilisateur et les mises à jour dynamiques de votre page.
