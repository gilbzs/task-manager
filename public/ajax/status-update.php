<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once APPDIR . 'models/Status.php';


if (isset($_POST['_order'])) {
    foreach($_POST['_order'] as $i => $status_id) {
        Status::upsert([
            'id' => $status_id,
            'position' => $i + 1,
        ]);
    }
    exit;
}

Status::upsert($_POST);

