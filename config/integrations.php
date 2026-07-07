<?php

return [
    'custom_script_locations' => [
        'head' => 'Head',
        'body_start' => 'Body (Início)',
        'body_end' => 'Body (Final)',
    ],

    'custom_script_scopes' => [
        'sitewide' => 'Todo o site',
        'page' => 'Página específica',
    ],

    'public_pages' => [
        ['value' => 'route:home', 'label' => 'Início'],
        ['value' => 'route:properties', 'label' => 'Imóveis'],
        ['value' => 'route:property.show', 'label' => 'Detalhes do imóvel'],
        ['value' => 'route:blog', 'label' => 'Blog'],
        ['value' => 'route:blog.show', 'label' => 'Artigo'],
        ['value' => 'route:about', 'label' => 'Quem Somos'],
        ['value' => 'route:off-market', 'label' => 'Off Market'],
        ['value' => 'route:exclusive-management', 'label' => 'Gestão Exclusiva'],
        ['value' => 'route:evaluate', 'label' => 'Avalie seu Imóvel'],
        ['value' => 'route:contact', 'label' => 'Contato'],
        ['value' => 'route:calculator', 'label' => 'Calculadora'],
        ['value' => 'route:partner-agent', 'label' => 'Corretor Parceiro'],
    ],
];
