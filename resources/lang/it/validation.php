<?php

return [

    'yes' => 'Sì',
    'no' => 'No',

    'does_exists' => 'Esiste',
    'is_readable' => 'È Leggibile',
    'is_writeable' => 'È Scrivibile',

    'local_path' => 'Il tuo percorso locale:',

    'system_information' => 'Informazioni Sistema',
    'statamic_version' => 'Versione Statamic',
    'meerkat_version' => 'Versione Meerkat',
    'server_type' => 'Nome Software',
    'clear_route_cache' => 'Pulisci Cache Route',

    'route_clear_artisan_header' => 'Pulizia Cache Route con Artisan',
    'route_clear_artisan_instructions' => 'Per pulire la cache delle route utilizzando l\'utility da riga di comando Artisan di Laravel, esegui il seguente comando nella directory: :directory:',

    'route_clear_manual_header' => 'Pulizia Manuale Cache Route',
    'route_clear_manual_instructions' => 'Per pulire manualmente la cache delle route, individua e rimuovi il seguente file su tutti i server su cui gira il tuo sito:',

    'category_routes' => 'Caching Route',
    'routes_valid' => 'Nessun problema di caching route rilevato.',
    'routes_invalid' => 'Uno o più problemi di caching route sono stati rilevati; il tuo sito potrebbe non funzionare correttamente fino a quando questi problemi non saranno risolti. Questi problemi possono essere risolti pulendo la cache delle route del tuo sito. Se il tuo sito gira su più server, la cache delle route deve essere pulita su tutti i server.',
    'route_category_emissions' => 'Asset Pannello di Controllo',
    'route_category_general' => 'Route Generali',

    'route_table_header_name' => 'Panoramica',
    'route_table_header_category' => 'Categoria',
    'route_table_header_description' => 'Descrizione',

    'route_cache_cleared' => 'Cache route pulita!',
    'emissions_cpConfiguration' => 'Configurazione Pannello di Controllo',
    'emissions_cpConfiguration_desc' => 'Questo è richiesto per fornire informazioni critiche alle funzionalità di Meerkat all\'interno del Pannello di Controllo Statamic.',

    'route_category_cp_configuration' => 'Configuratore Pannello di Controllo',
    'route_category_cp_configuration_desc' => 'Fornisce e gestisce l\'interfaccia di configurazione del Pannello di Controllo di Meerkat.',

    'route_category_spam_api' => 'API Spam',
    'route_category_spam_api_desc' => 'Gestisce le richieste di moderazione spam.',

    'route_category_moderation_api' => 'API Moderazione',
    'route_category_moderation_api_desc' => 'Fornisce le funzionalità di moderazione principali come modifica, risposta e visibilità commenti.',

    'route_category_submission_api' => 'API Submission Sito',
    'route_category_submission_api_desc' => 'Gestisce l\'invio di commenti dai visitatori del sito web.',

    'category_config_dir' => 'Directory di Configurazione e Archiviazione',
    'config_supplement_name' => 'Directory Configurazione Archiviazione Supplementare',
    'config_supplement' => 'La directory di configurazione supplementare contiene le modifiche di configurazione effettuate attraverso il Pannello di Controllo Statamic.',

    'config_users_name' => 'Directory Archiviazione Configurazione Utente', 'config_users' => 'La directory di configurazione utente contiene tutte le impostazioni di configurazione specifiche dell\'utente per il Pannello di Controllo Statamic.',

    'storage_content_name' => 'Directory Archiviazione Commenti',
    'storage_content' => 'La directory di archiviazione Commenti contiene tutto il contenuto inviato dagli utenti, così come i metadati sui commenti del tuo sito.',

    'storage_meerkat_name' => 'Directory Archiviazione Sistema Meerkat',
    'storage_meerkat' => 'La directory di archiviazione Meerkat è dove Meerkat posizionerà log, file temporanei e vari altri elementi di cui ha bisogno per funzionare correttamente.',
];
