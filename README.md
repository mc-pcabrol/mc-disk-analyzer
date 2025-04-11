# MC Disk Analyzer

Un plugin WordPress simple et léger pour visualiser les fichiers et dossiers les plus lourds de votre site.

Développé par [Pierre Cabrol – Midi Concept](https://www.midiconcept.fr)

---

## 🔎 Fonctionnalités

- Affiche les **fichiers > 5 Mo** du site
- Classe les **dossiers par taille** décroissante
- Génère deux graphiques avec **Chart.js** :
  - Vue globale des plus gros dossiers
  - Vue spécifique sur les sous-dossiers de `wp-content/uploads`
- Interface intégrée dans le back-office WordPress
- Compatible **PHP 7.4+**
- Système de **mise à jour automatique via GitHub**

---

## 🛠️ Installation

1. Télécharge le fichier ZIP depuis la [section Releases](https://github.com/mc-pcabrol/plugin-mc-disk-analyzer/releases)
2. Dans WordPress, va dans **Extensions > Ajouter > Téléverser une extension**
3. Sélectionne le fichier ZIP et clique sur **Installer**, puis **Activer**
4. Accède à l'outil via **MC Disk Analyzer** dans le menu admin

---

## 🔁 Mises à jour automatiques

Si tu as installé le plugin à partir du ZIP GitHub :
- Tu recevras les mises à jour automatiquement depuis l’administration WordPress dès qu’une nouvelle version est publiée avec un tag `vX.Y.Z`.

---

## 💡 Roadmap

- [ ] Export CSV des résultats
- [ ] Sélection personnalisée du dossier à analyser
- [ ] Nettoyage des fichiers obsolètes (optionnel)
- [ ] Compatibilité multisite

---

## 📄 Licence

Ce plugin est distribué sous la licence MIT.
