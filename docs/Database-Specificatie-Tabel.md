# Database Specificatie Tabel

Database: `breezedemo`

Deze specificatie beschrijft de tabellen die gebruikt worden voor het magazijn van Jamin.

## Product

| Kolom | Datatype | Null | Key | Extra | Omschrijving |
| --- | --- | --- | --- | --- | --- |
| Id | INT UNSIGNED | Nee | PK | AUTO_INCREMENT | Unieke sleutel van het product |
| Naam | VARCHAR(255) | Nee |  |  | Naam van het product |
| Barcode | CHAR(13) | Nee |  |  | Barcode van het product |

## Magazijn

| Kolom | Datatype | Null | Key | Extra | Omschrijving |
| --- | --- | --- | --- | --- | --- |
| Id | INT UNSIGNED | Nee | PK | AUTO_INCREMENT | Unieke sleutel van het magazijnrecord |
| ProductId | INT UNSIGNED | Nee | FK |  | Verwijzing naar Product.Id |
| VerpakkingsEenheidInKilogram | DECIMAL(5,2) | Nee |  |  | Verpakkingseenheid in kilogram |
| AantalAanwezig | INT UNSIGNED | Ja |  |  | Aantal stuks op voorraad (NULL = geen voorraad) |

**Relatie:** Magazijn.ProductId → Product.Id

## Allergeen

| Kolom | Datatype | Null | Key | Extra | Omschrijving |
| --- | --- | --- | --- | --- | --- |
| Id | INT UNSIGNED | Nee | PK | AUTO_INCREMENT | Unieke sleutel van het allergeen |
| Naam | VARCHAR(255) | Nee |  |  | Naam van het allergeen |
| Omschrijving | VARCHAR(255) | Nee |  |  | Uitleg over het allergeen |

## ProductPerAllergeen

| Kolom | Datatype | Null | Key | Extra | Omschrijving |
| --- | --- | --- | --- | --- | --- |
| Id | INT UNSIGNED | Nee | PK | AUTO_INCREMENT | Unieke sleutel van de koppeling |
| ProductId | INT UNSIGNED | Nee | FK |  | Verwijzing naar Product.Id |
| AllergeenId | INT UNSIGNED | Nee | FK |  | Verwijzing naar Allergeen.Id |

**Relaties:**
- ProductPerAllergeen.ProductId → Product.Id
- ProductPerAllergeen.AllergeenId → Allergeen.Id

## Leverancier

| Kolom | Datatype | Null | Key | Extra | Omschrijving |
| --- | --- | --- | --- | --- | --- |
| Id | INT UNSIGNED | Nee | PK | AUTO_INCREMENT | Unieke sleutel van de leverancier |
| Naam | VARCHAR(255) | Nee |  |  | Naam van de leverancier |
| ContactPersoon | VARCHAR(255) | Nee |  |  | Naam van de contactpersoon |
| LeverancierNummer | VARCHAR(20) | Nee |  |  | Uniek leveranciernummer |
| Mobiel | VARCHAR(20) | Nee |  |  | Mobiel nummer van de contactpersoon |

## ProductPerLeverancier

| Kolom | Datatype | Null | Key | Extra | Omschrijving |
| --- | --- | --- | --- | --- | --- |
| Id | INT UNSIGNED | Nee | PK | AUTO_INCREMENT | Unieke sleutel van de levering |
| LeverancierId | INT UNSIGNED | Nee | FK |  | Verwijzing naar Leverancier.Id |
| ProductId | INT UNSIGNED | Nee | FK |  | Verwijzing naar Product.Id |
| DatumLevering | DATE | Nee |  |  | Datum waarop geleverd is |
| Aantal | INT UNSIGNED | Nee |  |  | Geleverd aantal |
| DatumEerstVolgendeLevering | DATE | Ja |  |  | Verwachte datum van de volgende levering |

**Relaties:**
- ProductPerLeverancier.LeverancierId → Leverancier.Id
- ProductPerLeverancier.ProductId → Product.Id

## users

| Kolom | Datatype | Null | Key | Extra | Omschrijving |
| --- | --- | --- | --- | --- | --- |
| id | BIGINT UNSIGNED | Nee | PK | AUTO_INCREMENT | Unieke sleutel van de gebruiker |
| name | VARCHAR(255) | Nee |  |  | Naam van de gebruiker |
| email | VARCHAR(255) | Nee | UNIQUE |  | E-mailadres voor inloggen |
| email_verified_at | TIMESTAMP | Ja |  |  | Moment van e-mailverificatie |
| password | VARCHAR(255) | Nee |  |  | Gehashed wachtwoord |
| role | VARCHAR(255) | Nee |  | Default: magazijn_medewerker | Rol van de gebruiker |
| remember_token | VARCHAR(100) | Ja |  |  | Token voor "onthoud mij" |
| created_at | TIMESTAMP | Ja |  |  | Aanmaakdatum |
| updated_at | TIMESTAMP | Ja |  |  | Wijzigingsdatum |
