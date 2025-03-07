# TeckTime

## AssetMapper Bundle

- [Documentation AssetMapper](https://symfony.com/doc/6.4/frontend/asset_mapper.html)
- [Documentation Sass pour AssetMapper](https://symfony.com/bundles/SassBundle/current/index.html)
- [Documentation Typescript pour AssetMapper](https://github.com/sensiolabs/AssetMapperTypeScriptBundle/blob/main/doc/index.rst)
- [Exemple d'utilisation par le SymfonyCast](https://symfonycasts.com/screencast/asset-mapper)

## Calendrier

- [Documentation FullCallendar](https://fullcalendar.io/)
- [Documentation Utilisation Controller Callendar](/.doc/calendar.md)

:bangbang: Attention :bangbang:  
on utilise la version 5 car les versions plus récentes sont dependantes d'un package qui pose problème (preacte)

## Outils

- Un script bash se lance lors du lancement du serveur Symfony. L'installation d'une dependance pour ce script est necessaire:

```bash
sudo apt install inotify-tools
```

## Bonnes pratiques

### Fonts

Pour importer des fonts, plutot que de mettre un lien CDN dans le head, on va les declarer dans le fichier app.scss

#### exemple du SymfonyCast:

On importe une font-face:

```css
/* inter-latin-ext-wght-normal */
@font-face {
  font-family: "Inter Variable";
  font-style: normal;
  font-display: swap;
  font-weight: 100 900;
  src: url(https://cdn.jsdelivr.net/npm/@fontsource-variable/inter@5.0.3/files/inter-latin-ext-wght-normal.woff2)
    format("woff2-variations");
  unicode-range: U+0100-02AF, U+0300-0301, U+0303-0304, U+0308-0309, U+0323,
    U+0329, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F,
    U+A720-A7FF;
}
```

À la place de:

```html
<head>
  <meta charset="UTF-8" />
  <title>{% block title %}Mixed Vinyl{% endblock %}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  {% block stylesheets %} {{ ux_controller_link_tags() }}
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fontsource-variable/inter@5.0.3/index.min.css"
  />
  <link rel="stylesheet" href="{{ asset('styles/app.tailwind.css') }}" />
  {% endblock %} {% block javascripts %} {{ importmap() }}
  <script
    src="https://kit.fontawesome.com/5a377fab5b.js"
    crossorigin="anonymous"
  ></script>
  {% endblock %}
</head>
```

Lorsque votre site voit une balise <link rel="stylesheet">, il la télécharge avant d'afficher la page. Cela gèle donc le rendu de la page jusqu'à la fin du téléchargement.

à ce stade, notre navigateur voit juste un fichier CSS et pense :
"Je dois télécharger ce fichier CSS maintenant et je ne peux pas afficher la page avant la fin !"

Maintenant, si nous intégrons les font-faces, voici comment cela va fonctionner : notre navigateur télécharge le fichier app.css immédiatement...
mais les fichiers de polices eux-mêmes ne seront téléchargés que lorsque nous utiliserons cette police.
De plus, 'font-display: swap' indique au navigateur :

"Hé, vous pouvez restituer du texte censé utiliser cette police, même si la police n'est pas encore téléchargée.
Vous pouvez d'abord utiliser la police système par défaut, afficher le texte, terminer le téléchargement de ce fichier de police, puis l'utiliser. "
