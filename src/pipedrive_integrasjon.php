<?php
/**
 * Pipedrive-integrasjon (DRY_RUN versjon)
 * Jeg har bevisst laget koden lesbar og delt opp i små funksjoner. 
 * Jeg kunne skrevet alt rett i en fil, men jeg synes det er bedre å vise 
 * at jeg tenker på struktur og robusthet – selv i et lite case som dette
 * - Ingen .env, ingen ekte API-kall
 * - Viser payloadene som VILLE blitt sendt til v2 API-et
 * - Oppretter Organisasjon -> Person -> Lead med custom_fields
 */


declare(strict_types=1); //For å få bedre feilmeldinger under utvikling

/* -----------------------------
 * 1) Dummy verdier for tørrkjøring
 * --------------------------- */

$PD_DOMAIN    = "nettbureaucase";  
$PD_API_TOKEN = "24eaceaa89c83e18fd4aadd3dbab7a3b01ddffc8"; // placeholder
$DRY_RUN      = true;

// Enkel logg-funksjon for statusbeskjeder
function info(string $m): void { 
    echo "[INFO]  $m\n"; 
}

// Enkel logg-funksjon for feilmeldinger
function feil(string $m): void { 
    echo "[FEIL]  $m\n"; 
}

/* -----------------------------
 * 2) Hjelpefunksjon: POST til Pipedrive v2 APIet
 *    - Printer URL og payload
 *    - Returnerer "fake" id for å simulere respons
 * --------------------------- */

function pd_post(string $path, array $payload): array {
    global $PD_DOMAIN, $DRY_RUN;

    $url = "https://{$PD_DOMAIN}.pipedrive.com/api/v2/" . ltrim($path, '/');

    // Tørrkjøring: vis hva vi ville sendt
    if ($DRY_RUN) {
        info("DRY_RUN POST $url\n");
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        return ['data' => ['id' => random_int(1000, 9999)]];
    }

    // (Live-kall brukes ikke i case-versjonen)
    feil("Live-kall er av i case-versjonen.");
    exit(1);
}

/* -----------------------------
 * 3) Mapping med oppslagstabeller
 * --------------------------- */

function mapHousingType(?string $txt): ?int {
    $map = [
        "Enebolig"     => 30,
        "Leilighet"    => 31,
        "Tomannsbolig" => 32,
        "Rekkehus"     => 33,
        "Hytte"        => 34,
        "Annet"        => 35,
    ];
    return $map[$txt] ?? null;
}

function mapDealType(?string $txt): ?int {
    $map = [
        "Alle strømavtaler er aktuelle" => 42,
        "Fastpris"                      => 43,
        "Spotpris"                      => 44,
        "Kraftforvaltning"              => 45, 
        "Annen avtale/vet ikke"         => 46,
    ];
    return $map[$txt] ?? null;
}

function mapContactType(?string $txt): ?int {
    $map = [
        "Privat"     => 27,
        "Borettslag" => 28,
        "Bedrift"    => 29,
    ];
    return $map[$txt] ?? null;
}

/* -----------------------------
 * 4) Opprett ORGANISASJON
 * --------------------------- */

function opprettOrganisasjon(array $kundeinfo): int {
    $organisasjonsnavn = !empty($kundeinfo['company'])
        ? $kundeinfo['company']
        : (($kundeinfo['contact_type'] ?? '') === 'Bedrift' ? 'Ukjent bedrift' : 'Privatkunde');

    $payload = ["name" => $organisasjonsnavn];

    $svar  = pd_post("organizations", $payload);
    $orgId = $svar['data']['id'] ?? null;
    if (!$orgId) { feil("Manglet organizations.id i respons"); exit(1); }

    info("Organisasjon opprettet (simulert): id=$orgId, navn='{$organisasjonsnavn}'");
    return (int)$orgId;
}

/* -----------------------------
 * 5) Opprett PERSON (knyttet til org) + custom_fields(contact_type)
 * --------------------------- */

function opprettPerson(array $kundeinfo, int $orgId): int {
    $kontaktTypeId = mapContactType($kundeinfo['contact_type'] ?? null);

    $payload = [
        "name"          => $kundeinfo["name"]  ?? "Uten navn",
        "email"         => $kundeinfo["email"] ?? null,
        "phone"         => $kundeinfo["phone"] ?? null,
        "org_id"        => $orgId,
        "custom_fields" => [
            "contact_type" => $kontaktTypeId
        ]
    ];

    $svar     = pd_post("persons", $payload);
    $personId = $svar['data']['id'] ?? null;
    if (!$personId) { feil("Manglet persons.id i respons"); exit(1); }

    info("Person opprettet (simulert): id=$personId, navn='{$payload['name']}', org_id=$orgId");
    return (int)$personId;
}

/* -----------------------------
 * 6) Opprett LEAD (knyttes til person + org) + custom_fields
 * --------------------------- */

function opprettLead(array $kundeinfo, int $personId, int $orgId): int {
    $boligtypeId  = mapHousingType($kundeinfo['housing_type'] ?? null);
    $avtaletypeId = mapDealType($kundeinfo['deal_type'] ?? null);

    $payload = [
        "title"           => "Lead fra Strøm.no – " . ($kundeinfo["name"] ?? "ukjent"),
        "person_id"       => $personId,
        "organization_id" => $orgId,
        "custom_fields"   => [
            "housing_type"  => $boligtypeId,
            "property_size" => isset($kundeinfo["property_size"]) ? (int)$kundeinfo["property_size"] : null,
            "comment"       => $kundeinfo["comment"] ?? null,
            "deal_type"     => $avtaletypeId
        ]
    ];

    $svar   = pd_post("leads", $payload);
    $leadId = $svar['data']['id'] ?? null;
    if (!$leadId) { feil("Manglet leads.id i respons"); exit(1); }

    info("Lead opprettet (simulert): id=$leadId, title='{$payload['title']}'");
    return (int)$leadId;
}

/* -----------------------------
 * 7) Hovedflyt: Opprett alt (org, person, lead)
 * --------------------------- */

function opprettAlt(array $kundeinfo): array {
    $orgId    = opprettOrganisasjon($kundeinfo);
    $personId = opprettPerson($kundeinfo, $orgId);
    $leadId   = opprettLead($kundeinfo, $personId, $orgId);
    return compact('orgId', 'personId', 'leadId');
}
