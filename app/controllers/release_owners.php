<?php

declare(strict_types=1);

$owners = require_once MODELS . 'release_owners.php';

(new ReleaseInsights\Template(
    'release_owners.html.twig',
    [
        'page_title'   => 'Major releases per owner since version 27',
        'css_page_id'  => 'release_owners',
        'owners'       => $owners,
    ]
))->render();
