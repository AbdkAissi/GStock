# 🧾 GStock – Application de Gestion de Stock

GStock est une application web de gestion des achats, ventes, clients, fournisseurs et paiements. Développée avec **Symfony** et **EasyAdmin**, elle permet de suivre en temps réel les commandes et les flux financiers d'une entreprise.

## 🚀 Fonctionnalités principales

- 🔍 Gestion des **produits** (prix d’achat / vente, stock)
- 🧑‍💼 Gestion des **clients** et **fournisseurs**
- 🧾 Gestion des **commandes d’achat** et **de vente**
- 💰 Suivi des **paiements** (espèces, chèque, virement, carte bancaire)
- 📊 Tableau de bord avec **statistiques** et **graphiques**
- 🖨 Impression de factures / bons de commande
- 🛠 Interface d’administration avec **EasyAdmin**
- 🔒 Authentification sécurisée (login, mot de passe, réinitialisation)

## 🛠 Technologies utilisées

- **Symfony 6+**
- **PHP 8+**
- **Doctrine ORM**
- **EasyAdmin**
- **Webpack Encore** (JS/CSS)
- **Bootstrap** ou **Tailwind CSS** (au choix)
- **MySQL / MariaDB**

## ⚙️ Installation locale

```bash
git clone https://github.com/AbdkAissi/GStock.git
cd GStock
composer install
cp .env .env.local
# Modifier les variables d'environnement (base de données)
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start
