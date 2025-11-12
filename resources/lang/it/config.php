<?php

return [

    'updated' => 'I valori di configurazione del tuo sito sono stati aggiornati.',
    'preferences_updated' => 'Le tue preferenze utente sono state aggiornate.',
    'save' => 'Salva',

    'managed_notice' => 'Alcuni elementi sono gestiti dall\'amministratore del sito e non possono essere modificati dal Pannello di Controllo.',

    'tab_general' => 'Generale',

    'preferences_title' => 'Le Tue Preferenze',
    'preferences_desc' => 'Queste impostazioni sono tue e ti seguiranno su ogni dispositivo da cui accedi a questo Pannello di Controllo Statamic.',

    'avatar' => 'Avatar Pannello di Controllo',
    'avatar_desc' => 'Controlla come appaiono gli autori delle submission nel Pannello di Controllo.',

    'per_page' => 'Submission per Pagina',
    'per_page_desc' => 'Controlla quante submission vengono visualizzate per pagina per impostazione predefinita.',

    'publishing_title' => 'Pubblicazione Commenti',
    'publishing_desc' => 'Le impostazioni di pubblicazione ti permettono di controllare vari aspetti automatizzati del processo di invio commenti.',

    'only_accept_comments_from_authenticated_users' => 'Accetta Solo Commenti Autenticati',
    'only_accept_comments_from_authenticated_users_desc' => 'Accetta solo commenti da sessioni utente autenticate; i commenti anonimi o degli ospiti verranno rifiutati.',

    'publish_auto' => 'Pubblica Commenti Automaticamente',
    'publish_auto_desc' => 'Tutti i commenti da utenti anonimi verranno pubblicati automaticamente quando abilitato. Disabilita questo per rivedere i commenti prima che vengano elencati sul tuo sito.',

    'publish_user_auto' => 'Pubblica Commenti Utente Automaticamente',
    'publish_user_auto_desc' => 'Qualsiasi commento lasciato da un utente Statamic autenticato verrà pubblicato automaticamente quando abilitato.',

    'close_threads' => 'Quando Chiudere i Thread di Commenti',
    'close_threads_desc' => 'Inserisci il numero di giorni dopo i quali i commenti non verranno più accettati; inserire un valore di "0" disabiliterà questa funzione.',

    'tab_email' => 'Email',
    'email_general_title' => 'Impostazioni Email',
    'email_general_desc' => 'Queste impostazioni controllano il sistema email automatizzato per le submission.',

    'email_send_mail' => 'Invia Email',
    'email_send_mail_desc' => 'Controlla se le email vengono inviate automaticamente.',
    'email_check_spam_guard' => 'Controlla Spam',
    'email_check_spam_guard_desc' => 'Se abilitato, solo i commenti non marcati come spam invieranno un\'email.',

    'email_addresses' => 'Indirizzi a cui Inviare Email',
    'email_addresses_desc' => 'L\'elenco degli indirizzi email a cui inviare le email.',
    'email_addresses_notice' => 'L\'amministratore del tuo sito ha configurato indirizzi email predefiniti.',
    'email_addresses_view_defaults' => 'Clicca qui per visualizzarli.',
    'email_addresses_default_title' => 'Indirizzi Email Predefiniti',
    'email_addresses_default_desc' => 'L\'amministratore del tuo sito ha configurato indirizzi email predefiniti che verranno utilizzati in aggiunta a quelli configurati nel Pannello di Controllo.',

    'tab_spam' => 'Spam',

    'spam_general_title' => 'Impostazioni Spam Generali',
    'spam_general_desc' => 'Il servizio spam di Meerkat aiuta a proteggere il tuo sito dallo spam ed è altamente personalizzabile. Puoi controllare automaticamente tutte le submission in arrivo per lo spam, eliminare lo spam non appena viene rilevato e molto altro.',

    'auto_check_spam' => 'Controlla Automaticamente lo Spam',
    'auto_check_spam_desc' => 'Controlla se tutte le submission vengono controllate automaticamente per lo spam.',

    'auto_delete_spam' => 'Elimina Automaticamente tutto lo Spam',
    'auto_delete_spam_desc' => 'Controlla se le submission identificate come spam vengono eliminate automaticamente.',

    'check_all_spam_guards' => 'Controlla Tutti i Guard Spam',
    'check_all_spam_guards_desc' => 'Quando abilitato, tutti i guard spam verranno controllati anche se uno ha già determinato che una submission era spam.',

    'unpublish_on_guard_failures' => 'Rimuovi dalla Pubblicazione i Commenti sui Fallimenti dei Guard',
    'unpublish_on_guard_failures_desc' => 'Controlla se i commenti vengono automaticamente rimossi dalla pubblicazione se si verifica un errore.',

    'submit_moderator_results' => 'Invia Risultati Moderatore',
    'submit_moderator_results_desc' => 'Controlla se i falsi positivi/negativi vengono inviati ai provider di terze parti.',

    'spam_guards_title' => 'Guard Spam',
    'spam_guards_desc' => 'I guard spam migliorano il servizio spam integrato permettendogli di utilizzare metodi aggiuntivi per controllare lo spam. Se non ci sono guard spam abilitati, Meerkat non sarà in grado di determinare se le submission sono spam.',

    'table_spam_guard' => 'Guard Spam',
    'table_enabled' => 'Abilitato',

    'akismet_title' => 'Configurazione Akismet',
    'akismet_desc' => 'Akismet è un servizio di terze parti e necessita di alcuni elementi di configurazione aggiuntivi per funzionare correttamente.',
    'akismet_link_text' => 'Scopri di più su Akismet sul loro sito web.',

    'akismet_api_key' => 'Chiave API',
    'akismet_api_key_desc' => 'La tua chiave API Akismet.',

    'akismet_front_page' => 'Pagina Principale',
    'akismet_front_page_desc' => 'La pagina principale Akismet da utilizzare.',

    'tab_ip_address_filter' => 'Filtro Indirizzi IP',

    'ip_filter_title' => 'Filtro Indirizzi IP',
    'ip_filter_desc' => 'Se una submission viene inviata da una rete con uno qualsiasi dei seguenti indirizzi IP, verrà marcata come spam.',
    'ip_filter_blocked' => 'Indirizzi IP Bloccati',
    'ip_filter_blocked_desc' => 'L\'elenco degli indirizzi IP da controllare per tutte le nuove submission.',
    'ip_filter_managed_notice' => 'L\'amministratore del tuo sito ha configurato indirizzi IP predefiniti.',
    'ip_filter_view_defaults' => 'Clicca qui per visualizzarli.',
    'ip_filter_default_title' => 'Indirizzi IP Predefiniti',
    'ip_filter_default_desc' => 'L\'amministratore del tuo sito ha configurato indirizzi IP predefiniti che verranno controllati in aggiunta a quelli configurati nel Pannello di Controllo.',

    'tab_word_filter' => 'Filtro Parole',
    'word_filter_title' => 'Filtro Parole',
    'word_filter_desc' => 'Se una submission contiene una qualsiasi delle parole nell\'elenco sottostante, verrà marcata come spam.',
    'word_filter_banned' => 'Parole Vietate',
    'word_filter_banned_desc' => 'L\'elenco delle parole da controllare per tutte le nuove submission.',
    'word_filter_managed_notice' => 'L\'amministratore del tuo sito ha configurato parole vietate predefinite.',
    'word_filter_view_defaults' => 'Clicca qui per visualizzarle.',
    'word_filter_default_title' => 'Filtro Parole Predefinito',
    'word_filter_default_desc' => 'L\'amministratore del tuo sito ha configurato parole predefinite che verranno controllate in aggiunta a quelle configurate nel Pannello di Controllo.',

    'tab_permissions' => 'Permessi',
    'permissions_title' => 'Permessi Gruppi Utente',
    'permissions_desc' => 'I permessi dei gruppi utente ti permettono di controllare quali azioni possono compiere gli utenti di diversi Gruppi Utente. Ad esempio, puoi creare un Gruppo Utente specificamente per i moderatori che possono solo visualizzare, approvare o rimuovere commenti. Se usi un provider di servizi spam che addebita per richiesta API, potresti anche voler limitare chi può emettere quelle richieste.',
    'table_user_group' => 'Gruppo Utente',
    'table_all' => 'Tutti',
    'table_view_comments' => 'Visualizza Commenti',
    'table_approve' => 'Approva',
    'table_unapprove' => 'Rimuovi Approvazione',
    'table_edit' => 'Modifica',
    'table_reply' => 'Rispondi',
    'table_report_ham' => 'Segnala Non Spam',
    'table_report_spam' => 'Segnala Spam',
    'table_delete' => 'Elimina',

    'validate_akismet_prompt' => 'Clicca per validare la tua configurazione Akismet.',
    'validate_akismet_validating' => 'Validazione della tua configurazione. Un momento per favore.',
    'validate_akismet_okay' => 'La configurazione API Akismet è stata validata con successo.',
    'validate_akismet_failure' => 'Qualcosa è andato storto durante la validazione della configurazione API Akismet.',
    'validate_akismet_no_params' => 'Parametri richiesti mancanti per validare la tua configurazione Akismet.',
    'validate_akismet_api_invalid' => 'L\'API Akismet ha determinato che la configurazione della tua chiave API è non valida.',

    'server_changes_warning_title' => 'Modifiche di Configurazione Rilevate',
    'server_changes_warning_message' => 'Sono state rilevate modifiche alla configurazione del tuo sito dall\'ultima volta che hai caricato questa pagina; qualsiasi modifica che salvi sovrascriverà quelle modifiche.',
    'server_changes_warning_reload_prompt' => 'Clicca qui per ricaricare le tue impostazioni.',

    'tab_privacy' => 'Privacy Utente',
    'privacy_title' => 'Privacy Dati Submission Visitatori',
    'privacy_desc' => 'Meerkat può essere configurato per raccogliere selettivamente i seguenti dati sui visitatori del tuo sito quando inviano un commento. Alcuni dati possono essere utilizzati da alcuni guard spam per migliorare l\'accuratezza del rilevamento spam.',

    'privacy_table_data' => 'Dati Utente',
    'privacy_table_enabled' => 'Raccogli',

    'privacy_store_user_agent_title' => 'User Agent Browser',
    'privacy_store_user_agent_desc' => 'Controlla se vengono raccolte informazioni su browser e sistema operativo',

    'privacy_store_user_ip_title' => 'Indirizzo IP Utente',
    'privacy_store_user_ip_desc' => 'Controlla se vengono raccolte informazioni sull\'indirizzo IP del visitatore',

    'privacy_store_referrer_title' => 'Header HTTP Referrer',
    'privacy_store_referrer_desc' => 'Controlla se vengono raccolte informazioni HTTP Referrer del visitatore',
];
