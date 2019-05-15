<?php declare(strict_types=1);

error_reporting(E_ERROR);

$DOM = new DOMDocument();

const URI         = 'https://www.avv-augsburg.de/fahrtauskunft/fahrgastinfo/verkehrsmeldungen/?line=';

try {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => URI,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    $DOM->loadHTML($response);
} catch(Error $error) {
    die($error->getMessage());
}

$notifications    = $DOM->getElementById('notifications');
$notificationList = $notifications->getElementsByTagName('li');

/* @var DOMElement $notificationItem */
foreach($notificationList as $notificationItem) {
    if($notificationItem->getAttribute('class') !== 'notification-item') {
        continue;
    }

    // betroffene Linien
    $sanitizedLines = implode(', ', explode(',', substr($notificationItem->getAttribute('data-lines'), 1, -1)));

    // Überschrift
    $h3 = $notificationItem->getElementsByTagName('h3')
                           ->item(0)->textContent;

    // tatsächliche Beschreibung
    $description = get_inner_html($notificationItem->getElementsByTagName('div')
                                                   ->item(1));

    ?>
    <section>
        <header>
            <h1><?= $h3 ?></h1>
            <sub>Betroffene Linien: <?= $sanitizedLines ?></sub>
        </header>
        <article>
            <?= $description ?>
        </article>
    </section>
    <hr>
    <?php
}


function get_inner_html($node) {
    $innerHTML = '';

    foreach($node->childNodes as $child) {
        $innerHTML .= $child->ownerDocument->saveXML($child);
    }

    return $innerHTML;
}
