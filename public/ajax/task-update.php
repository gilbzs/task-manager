<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once APPDIR . 'models/Task.php';


if (isset($_POST['_order'])) {
    foreach($_POST['_order'] as $i => $task_id) {
        Task::upsert([
            'id' => $task_id,
            'position' => $i * 2 + 2,
        ]);
    }
    exit;
}

if (isset($_POST['_delete'])) {
    Task::find($_POST['_delete'])->delete();
    exit;
}

Task::upsert($_POST);
