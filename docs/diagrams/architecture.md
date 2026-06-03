# Dijagram arhitekture - NoteApp

```mermaid
flowchart TD
    Browser[Web preglednik]
    Public[public/index.php]
    Router[Router]
    WebControllers[Web kontroleri]
    ApiControllers[API kontroleri]
    Middleware[AuthMiddleware]
    Services[Servisi: JwtService i Security]
    Models[Modeli: UserModel, NoteModel, CategoryModel]
    Views[HTML predlošci]
    Database[(MySQL baza)]

    Browser -->|HTTP zahtjev| Public
    Public --> Router
    Router -->|web rute| WebControllers
    Router -->|API rute| ApiControllers
    WebControllers --> Views
    Views -->|HTML odgovor| Browser
    ApiControllers -->|JSON odgovor| Browser
    ApiControllers --> Middleware
    Middleware --> Services
    WebControllers --> Services
    ApiControllers --> Services
    WebControllers --> Models
    ApiControllers --> Models
    Middleware --> Models
    Models -->|PDO prepared statements| Database
```

## Slojevi

- `public/index.php` učitava konfiguraciju, postavlja sigurnosna zaglavlja i registrira rute.
- `Router` mapira HTTP metodu i putanju na metodu odgovarajućeg kontrolera.
- Web kontroleri vraćaju HTML predloške iz mape `views`.
- API kontroleri vraćaju JSON odgovore i HTTP statusne kodove.
- `AuthMiddleware` provjerava JWT Bearer token i administratorsku ulogu.
- Modeli rade s MySQL bazom kroz PDO prepared statements.
- `JwtService` i `Security` izdvajaju zajedničku logiku za tokene, CSRF, escaping i sigurnu CSS boju.
