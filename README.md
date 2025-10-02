# Nettbureaucase

## Om caset
PHP-script som oppretter **Organisasjon → Person → Lead** i Pipedrive v2-API, med `custom_fields`.

## Struktur
```text
/nettbureau_case
├── src/
│   ├── pipedrive_integrasjon.php
│   └── run_eksempel.php
├── test/
│   └── test_data.json
├── .env.eksempel
└── README.md
```           

## Oppsett
1. Lag `.env` basert på `.env.eksempel`:
   ```ini
   PD_DOMAIN=nettbureaucase
   PD_API_TOKEN=API_NØKKEL_HER
   DRY_RUN=true

2. Kjør:
php src/run_eksempel.php

Testdata
```json
{
  "name": "Ola Nordmann",
  "phone": "12345678",
  "email": "ola.nordmannn@online.no",
  "housing_type": "Enebolig",
  "property_size": 160,
  "deal_type": "Spotpris",
  "contact_type": "Privat",
  "comment": "Vil bytte leverandør i høst"
}
```
## Om tilnærming og verktøy

Jeg har ikke jobbet med PHP tidligere, men har løst oppgaven ved å bruke kunnskap fra Java og JavaScript, kombinert med AI verktøy som GitHub Copilot og ChatGPT. Det er slik vi har blitt rådet til å jobbe smartere i studiene, men også i jobbsammenheng.  
AI har hjulpet med forslag til struktur og kode, men jeg har selv tilpasset, kommentert og fornorsket koden for å sikre at jeg selv forstår stegene.  

Målet har vært å levere en løsning som er:
- Lesbar og enkel å følge
- Robust nok til å vise riktig bruk av Pipedrive v2 API
- Et uttrykk for hvordan jeg lærer nye teknologier nokså raskt

Dette caset viser både viljen min og evne til å lære nye språk og rammeverk, samt å bruke moderne utviklerverktøy på en smart og ansvarlig måte.

Takk for muligheten! 

Mvh
Lawrence aka Lawtech