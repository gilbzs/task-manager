<?php
require_once __DIR__ . '/BaseModel.php';

class Task extends BaseModel {
    protected $fields = [
        'title' => 'STRING',
        'content' => 'TEXT',
        'position' => 'INT',
        'status_id' => 'INT',
    ];
}
