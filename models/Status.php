<?php
require_once APPDIR . 'models/BaseModel.php';
require_once APPDIR . 'models/Task.php';

class Status extends BaseModel {
    protected $fields = [
        'name' => 'STRING',
        'position' => 'INT',
    ];

    // retrieve tasks within a status ordered by priority
    public function tasks()
    {
        return Task::query(
            "SELECT task.* FROM task JOIN status ON status.id = task.status_id WHERE status.id = :id ORDER BY task.position",
            [':id' => $this->id]
        );
    }
}
