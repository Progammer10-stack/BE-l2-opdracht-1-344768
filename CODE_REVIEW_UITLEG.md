# Uitleg voor de code-review

Dit bestand legt in gewoon Nederlands uit hoe de Jamin-applicatie werkt. De codeblokken komen uit dit project.

## 1. Wat de applicatie doet

De applicatie is het magazijn van Jamin.

- Een gast kan inloggen of een account aanmaken.
- Een nieuw account wordt altijd een **klant**. Een klant komt op een eigen startpagina.
- Een **magazijnmedewerker** of **admin** ziet twee overzichten:
  - producten in het magazijn, met leveringen en allergenen;
  - leveranciers, gesorteerd op het aantal verschillende producten.
- Bij een leverancier zie je de geleverde producten. Het plusje opent een formulier voor een nieuwe levering.
- Opslaan zet de levering van vandaag in de database en telt het aantal op bij de voorraad.
- Winegums wordt niet meer gemaakt (`IsActief` is 0). Dan komt er een melding en wordt er niets opgeslagen.

## 2. Hoe MVC werkt in dit project

MVC betekent Model, View, Controller.

- **Route:** het webadres, bijvoorbeeld `/leveranciers`.
- **Controller:** haalt gegevens op of slaat ze op, en kiest de pagina.
- **Model:** de PHP-klasse die bij een databasetabel hoort, plus de relaties.
- **View:** het Blade-bestand, de HTML die de browser toont.

Voorbeeld van het leveranciersoverzicht:

```php
public function index(): View
{
    $leveranciers = (new LeverancierGegevens)->overzicht();

    return view('leveranciers.index', [
        'leveranciers' => $leveranciers,
    ]);
}
```

Regel voor regel:

1. `public function index(): View` is de functie die bij de route `/leveranciers` hoort. Ze geeft een pagina terug.
2. `(new LeverancierGegevens)` maakt het object dat de database bevraagt.
3. `->overzicht()` haalt alle leveranciers op, met het aantal producten.
4. `return view(...)` opent `resources/views/leveranciers/index.blade.php`.
5. `'leveranciers' => $leveranciers` geeft die lijst door aan de Blade-pagina. In de view heet die variabele `$leveranciers`.

## 3. Controllers en hun functies

### ProductController

| Functie | Wat hij doet |
| --- | --- |
| `index` | Alle producten, gesorteerd op barcode, met de magazijnrij. |
| `show` | Leveringen van één product, van oud naar nieuw, plus de leverancier. |
| `allergenen` | Allergenen van één product, gesorteerd op naam. |

```php
$producten = Product::with('magazijn')
    ->orderBy('Barcode')
    ->get();
```

1. `Product::` begint een query op de tabel `Product`.
2. `with('magazijn')` haalt meteen de bijbehorende magazijnrij op, zodat de pagina niet per product opnieuw naar de database gaat.
3. `orderBy('Barcode')` sorteert oplopend op barcode.
4. `get()` voert de query uit en geeft een lijst terug.

### LeverancierController

| Functie | Wat hij doet |
| --- | --- |
| `index` | Alle leveranciers, aflopend op aantal verschillende producten. |
| `producten` | Eén leverancier en zijn geleverde producten. Bestaat de leverancier niet, dan een 404. |

### LeveringController

| Functie | Wat hij doet |
| --- | --- |
| `create` | Toont het formulier. Alleen als deze leverancier dit product al levert. |
| `store` | Controleert het formulier, weigert een inactief product, of slaat de levering op. |

De opbouw van `store` is expres in vier stappen:

1. Gegevens ophalen. Bestaat de combinatie niet, dan `abort(404)`.
2. Formulier controleren met `$request->validate(...)`.
3. Controleren of het product nog gemaakt wordt.
4. Opslaan en terugsturen naar de producten van die leverancier.

### Inloggen en registreren

`AuthenticatedSessionController@store` logt de gebruiker in. Een klant gaat naar `/klant`. Een medewerker gaat naar de homepage.

`RegisteredUserController@store` maakt een gebruiker en zet de rol op klant:

```php
$user->role = UserRole::Klant;
$user->save();
```

1. De rol wordt apart gezet, niet via het formulier. Een bezoeker kan dus niet zelf admin worden.
2. `save()` schrijft de rij naar de tabel `users`.

De andere auth-controllers (wachtwoord vergeten, e-mailbevestiging) komen uit Laravel Breeze. Die schermen worden in deze opdracht niet gebruikt. De views daarvoor staan niet in het project.

## 4. Models en relaties

De schooltabellen heten `Product`, `Leverancier` en `Id`. Laravel verwacht normaal `products` en `id`. Daarom staat in het model:

```php
protected $table = 'Product';
protected $primaryKey = 'Id';
public $timestamps = false;
```

1. `$table` wijst naar de echte tabelnaam.
2. `$primaryKey` zegt dat de sleutel `Id` heet, niet `id`.
3. `$timestamps = false` betekent: deze tabel heeft geen `created_at` en `updated_at`. De schooltabellen gebruiken `DatumAangemaakt` en `DatumGewijzigd`.

Relaties op `Product`:

```php
public function magazijn(): HasOne
{
    return $this->hasOne(Magazijn::class, 'ProductId', 'Id');
}

public function leveringen(): HasMany
{
    return $this->hasMany(ProductPerLeverancier::class, 'ProductId', 'Id')
        ->orderBy('DatumLevering');
}

public function allergenen(): BelongsToMany
{
    return $this->belongsToMany(
        Allergeen::class,
        'ProductPerAllergeen',
        'ProductId',
        'AllergeenId',
        'Id',
        'Id',
    )->orderBy('Allergeen.Naam');
}
```

1. `hasOne` : één product heeft één magazijnrij. `Magazijn.ProductId` hoort bij `Product.Id`.
2. `hasMany` : één product heeft meerdere leveringen. Die staan op datum, van oud naar nieuw.
3. `belongsToMany` : een product kan meerdere allergenen hebben, en een allergeen zit in meerdere producten. De koppeltabel is `ProductPerAllergeen`.
4. De vier id-argumenten betekenen: kolom in de koppeltabel naar Product, kolom in de koppeltabel naar Allergeen, sleutel van Product, sleutel van Allergeen.
5. `orderBy('Allergeen.Naam')` sorteert de allergenen op naam.

Verder:

- `Leverancier` hasMany `ProductPerLeverancier`.
- `ProductPerLeverancier` belongsTo `Leverancier` en belongsTo `Product`.
- `Magazijn` belongsTo `Product`.
- `Allergeen` belongsToMany `Product`.

`User` hoort bij de tabel `users`. De rol is een enum:

```php
enum UserRole: string
{
    case Admin = 'admin';
    case MagazijnMedewerker = 'magazijn_medewerker';
    case Klant = 'klant';
}
```

In de database staat dus de tekst `admin`, `magazijn_medewerker` of `klant`. In PHP is dat een `UserRole`.

## 5. Hoe de routes werken

```php
Route::middleware(['auth', 'can:access-medewerker-dashboard'])->group(function () {
    Route::get('/leveranciers', [LeverancierController::class, 'index'])
        ->name('leveranciers.index');
    // ...
});
```

1. `middleware(['auth', ...])` betekent: je moet ingelogd zijn.
2. `can:access-medewerker-dashboard` betekent: alleen admin of magazijnmedewerker. Een klant krijgt 403.
3. `Route::get` is een pagina openen. `Route::post` is een formulier versturen.
4. `{leverancier}` en `{product}` zijn getallen uit de URL. `whereNumber` weigert tekst.
5. `->name(...)` geeft de route een naam. In Blade gebruik je `route('leveranciers.index')` in plaats van het pad hard te typen.

Belangrijke adressen:

| Adres | Naam | Wie |
| --- | --- | --- |
| `/` | `home` | Iedereen. Een klant wordt doorgestuurd naar `/klant`. |
| `/login` en `/register` | `login`, `register` | Alleen gasten. |
| `/klant` | `klant.home` | Ingelogde gebruikers. |
| `/leveranciers` | `leveranciers.index` | Medewerker of admin. |
| `/leveranciers/{id}/producten` | `leveranciers.producten` | Medewerker of admin. |
| `/leveranciers/{id}/producten/{product}/levering` | `leveranciers.levering.create` en `.store` | Medewerker of admin. GET toont het formulier, POST slaat op. |
| `/products` | `products.index` | Medewerker of admin. |
| `/products/{product}` | `products.show` | Medewerker of admin. |
| `/products/{product}/allergenen` | `products.allergenen` | Medewerker of admin. |

De allergenen-route staat vóór `/products/{product}`. Anders zou Laravel het woord `allergenen` als productnummer zien.

## 6. Gegevens ophalen en opslaan

Producten, leveringen op de productpagina en allergenen gaan via Eloquent (`Product::with(...)`, `$product->leveringen()`).

Het leveranciersdeel gaat via `app/Database/LeverancierGegevens.php`.

- Op **MySQL** roept die klasse stored procedures aan met `CALL sp_...`.
- Op **SQLite** (de automatische tests) kan dat niet. Daar staat dezelfde logica als gewone query.

Een procedure aanroepen:

```php
$statement = DB::connection()->getPdo()->prepare($sql);
$statement->execute($parameters);
$rijen = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();
```

1. `prepare` maakt de SQL klaar. De vraagtekens zijn placeholders, geen geplakte tekst. Dat beschermt tegen SQL-injectie.
2. `execute` vult die vraagtekens met de echte waarden, bijvoorbeeld het leveranciernummer.
3. `fetchAll` leest alle resultaatrijen als associatieve arrays. De sleutels zijn de kolomnamen.
4. `closeCursor()` maakt de verbinding vrij. Zonder die aanroep kan de volgende `CALL` vastlopen.

Een nieuwe levering op MySQL gaat door `sp_registreer_levering`. Die procedure:

1. zet een rij in `ProductPerLeverancier` (vandaag als leverdatum, het ingevulde aantal, de volgende datum);
2. telt dat aantal op bij `Magazijn.AantalAanwezig`. `IFNULL` maakt van een lege voorraad eerst 0.

Op SQLite doet `registreerLevering` die twee stappen zelf, binnen een transactie. `commit` bewaart beide. Gaat er iets mis, dan maakt `rollBack` beide ongedaan.

## 7. Hoe validatie werkt

In `LeveringController::store`:

```php
$gecontroleerd = $request->validate([
    'aantal' => ['required', 'integer', 'min:1'],
    'datum_eerstvolgende_levering' => ['required', 'date', 'after_or_equal:today'],
], [
    'aantal.required' => 'Vul het aantal producteenheden in.',
    // ...
]);
```

1. `required` : het veld moet ingevuld zijn.
2. `integer` en `min:1` : een geheel getal van minstens 1.
3. `date` : een geldige datum.
4. `after_or_equal:today` : vandaag of later. Gisteren mag niet.
5. Het tweede array zijn de Nederlandse foutteksten.
6. Faalt de controle, dan stuurt Laravel de gebruiker terug naar het formulier. In de view toont `@error('aantal')` de melding.
7. Lukt het, dan zitten alleen de gecontroleerde velden in `$gecontroleerd`. Die waarden worden opgeslagen, niet de ruwe request.

Registratie controleert naam, e-mail (uniek) en wachtwoord (bevestigd, standaardregels van Laravel).

## 8. Middleware en rollen

Middleware is een controle vóór de controller.

- `guest` : alleen als je nog niet bent ingelogd.
- `auth` : alleen als je wel bent ingelogd. Anders ga je naar de loginpagina.
- `can:access-medewerker-dashboard` : de Gate hieronder.

```php
Gate::define('access-medewerker-dashboard', function (User $user): bool {
    return $user->hasRole(UserRole::Admin, UserRole::MagazijnMedewerker);
});
```

1. `Gate::define` maakt een rechtenregel met een naam.
2. De functie krijgt de ingelogde gebruiker.
3. `hasRole` kijkt of `users.role` admin of magazijnmedewerker is.
4. `false` geeft HTTP 403. De pagina wordt niet getoond.

`@csrf` in elk formulier zet een verborgen token. Laravel weigert het formulier als dat token ontbreekt. Dat blokkeert een aanval waarbij een andere site een formulier namens jou verstuurt.

Wachtwoorden worden gehasht via de cast `'password' => 'hashed'` op het User-model. In de database staat dus niet het echte wachtwoord.

## 9. Stored procedures

Ze staan in `database/create_script.sql` en in de migraties. De PHP-code verandert hun werking niet.

| Procedure | Doel |
| --- | --- |
| `sp_leverancier_overzicht` | Alle leveranciers, met `COUNT(DISTINCT ProductId)`, aflopend op dat aantal en daarna op naam. |
| `sp_leverancier_gegevens` | Naam, contactpersoon, nummer en mobiel van één leverancier. |
| `sp_geleverde_producten` | Producten van die leverancier. Voorraad van hoog naar laag. Lege voorraad onderaan. Per product de laatste leverdatum. |
| `sp_levering_gegevens` | Productnaam, leverancier, contact en mobiel, plus `IsActief`, voor het formulier. |
| `sp_registreer_levering` | Nieuwe levering invoegen en de voorraad verhogen. |

Het voorraadstuk uit de procedure:

```sql
UPDATE Magazijn
SET AantalAanwezig = IFNULL(AantalAanwezig, 0) + p_aantal
WHERE ProductId = p_product_id;
```

1. `UPDATE Magazijn` past de voorraadrij aan.
2. `IFNULL(AantalAanwezig, 0)` gebruikt 0 als er nog geen voorraad is.
3. Daar komt het geleverde aantal bij.
4. `WHERE ProductId = ...` past alleen het product aan dat zojuist geleverd is.

## 10. Methoden die je moet kunnen uitleggen

| Methode | Betekenis |
| --- | --- |
| `route('naam')` | Maakt een URL bij een routenaam. |
| `view('map.bestand', $data)` | Kiest een Blade-pagina en geeft data mee. |
| `redirect()->route(...)` | Stuurt de browser naar een andere pagina. |
| `abort(404)` | Stopt en toont "niet gevonden". |
| `compact('product')` | Maakt `['product' => $product]`. |
| `Product::with('magazijn')` | Eager loading: relatie meteen meeladen. |
| `orderBy` / `get` | Sorteren en de query uitvoeren. |
| `$request->validate([...])` | Controleert formulierinvoer. |
| `now()->toDateString()` | De datum van vandaag, als `2026-10-29`. |
| `@csrf` | Beveiligingstoken in een formulier. |
| `@error('veld')` | Toont de fout van dat veld. |
| `@forelse` / `@empty` | Lus over een lijst, met een tekst als de lijst leeg is. |
| `hasOne`, `hasMany`, `belongsTo`, `belongsToMany` | De relaties tussen tabellen. |
| `DB::beginTransaction()` / `commit` / `rollBack` | Meerdere schrijfacties als één geheel. |
| `prepare` + `execute` | Veilige SQL met placeholders. |

## 20 vragen die de docent kan stellen

1. **Wat is een controller?**  
   De klasse die een verzoek afhandelt: gegevens ophalen of opslaan, en daarna een view of een redirect teruggeven.

2. **Waarom staat `$table = 'Product'` in het model?**  
   Omdat de tabel in de database `Product` heet. Laravel zou anders de tabel `products` zoeken.

3. **Wat doet `hasMany`?**  
   Eén rij hoort bij meerdere rijen in een andere tabel. Eén product heeft meerdere leveringen.

4. **Wat doet `belongsToMany`?**  
   Een veel-op-veelrelatie via een koppeltabel. Producten en allergenen zitten aan elkaar via `ProductPerAllergeen`.

5. **Waarom `with('magazijn')`?**  
   Dan laadt Laravel de magazijngegevens in één extra query, niet opnieuw voor elk product.

6. **Wat doet middleware `auth`?**  
   Alleen ingelogde gebruikers mogen verder. Een gast gaat naar de loginpagina.

7. **Waarom mag een klant het leveranciersoverzicht niet zien?**  
   Die routes zitten in de groep `can:access-medewerker-dashboard`. Die Gate laat alleen admin en magazijnmedewerker door.

8. **Hoe voorkom je dat iemand zich als admin registreert?**  
   Het registratieformulier heeft geen rolveld. De code zet daarna zelf `UserRole::Klant`.

9. **Wat doet `@csrf`?**  
   Het zet een token in het formulier. Laravel weigert het formulier als dat token niet klopt.

10. **Wat is SQL-injectie en hoe voorkom je die hier?**  
    Iemand probeert SQL in een invoerveld te stoppen. `prepare` en `execute` sturen de invoer als waarde mee, niet als stuk SQL.

11. **Wat controleert de validatie van een levering?**  
    Aantal is verplicht, een geheel getal en minstens 1. De volgende leverdatum is verplicht, een datum, en niet in het verleden.

12. **Wat gebeurt er als Winegums wordt opgeslagen?**  
    `IsActief` is 0. De pagina toont: "Het product Winegums van de leverancier Basset wordt niet meer geproduceerd". Er komt geen nieuwe rij en de voorraad blijft NULL. Na 4 seconden ga je terug naar de producten van Basset.

13. **Waarom twee manieren om leveranciers op te halen?**  
    MySQL gebruikt de verplichte stored procedures. De tests draaien op SQLite, en die kan die procedures niet uitvoeren. De queries doen hetzelfde.

14. **Wat is een stored procedure?**  
    Een query met een naam, opgeslagen in MySQL. PHP roept hem aan met `CALL sp_leverancier_overzicht()`.

15. **Hoe wordt de voorraad verhoogd?**  
    `AantalAanwezig` wordt de oude voorraad plus het nieuwe aantal. Is de voorraad NULL, dan wordt hij gelijk aan het geleverde aantal.

16. **Wat doet `closeCursor()`?**  
    Het ruimt het resultaat van een procedure op, zodat de volgende `CALL` op dezelfde verbinding kan draaien.

17. **Wat is een transactie?**  
    De insert van de levering en de update van de voorraad horen bij elkaar. Mislukt de tweede, dan wordt de eerste ook teruggedraaid.

18. **Waarom staat de allergenen-route boven de product-route?**  
    `/products/{product}/allergenen` moet eerder matchen dan `/products/{product}`. Anders behandelt Laravel `allergenen` als een product-id.

19. **Wat doet `abort(404)`?**  
    De leverancier of de combinatie leverancier-product bestaat niet. De gebruiker krijgt "niet gevonden", geen lege of verkeerde pagina.

20. **Wat is het verschil tussen GET en POST bij een levering?**  
    GET toont het formulier. POST verwerkt "Sla op": valideren, eventueel weigeren, of opslaan en terugsturen.
