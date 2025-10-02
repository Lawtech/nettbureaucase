<?php
require __DIR__ . '/pipedrive_integrasjon.php';

// Les testdata fra fil
$fil = __DIR__ . '/../test/test_data.json';
if (!is_file($fil)) { feil("Fant ikke test/test_data.json"); exit(1); }

$kundeinfo = json_decode(file_get_contents($fil), true);
if (!is_array($kundeinfo)) { feil("Kunne ikke parse test_data.json"); exit(1); }

// Kjør flyten (alt er DRY_RUN)
info("Starter simulering: Organisasjon -> Person -> Lead");
$ids = opprettAlt($kundeinfo);
info("Ferdig ✅ (simulert) " . json_encode($ids, JSON_UNESCAPED_UNICODE));
