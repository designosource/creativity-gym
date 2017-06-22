# Creativity Gym Bolt Theme
Deze theme werd ontworpen door [Robert Vaida](http://www.code-master.be) en ontwikkeld door [Designosource](http://www.designosource.be). Voor vragen na het lezen van deze readme kan je best contact opnemen met [Diederik Craps](http://www.diederikcraps.be).

## Aanpassingen doen?
Deze theme werd ontwikkeld in het CMS [Bolt 3.2](http://www.bolt.cm) en werkt met **.twig templates**. Om deze theme aan te passen ga je als volgt te werk:

1. Voer een [update](https://docs.bolt.cm/3.2/upgrading/updating#updating-on-the-command-line) uit van het Bolt CMS.

2. Download de database file van de live versie. Deze staat in de folder ```/app/database```.
Login voor de productiesite vraag je aan [Joris Hens](https://twitter.com/GoodBytes).

3. Maak een kopie van de ```/public/theme/gym``` folder. Pas de lijn ```theme: gym``` in de file ```/app/config/config.yml``` aan met de naam van het nieuwe theme.

4. Klaar om te themen! Terminal openen, met het ```cd```-commando naar de folder ```/public/theme/NAAM_VAN_HET_THEME/assets``` navigeren en [Sass](http://sass-lang.com/) watch opzetten met ```sass --watch sass/screen.scss:css/screen.css```.

5. **Happy theming!**
