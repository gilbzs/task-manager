<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once APPDIR . 'models/Task.php';
require_once APPDIR . 'models/Status.php';

if ($_GET['id']) {
    $task = Task::find($_GET['id']);
} else {
    $task = new Task;
}
?>
<div>
    <input type="text" class="txt-task-title" value="<?php echo htmlentities($task->title) ?>">
    <hr>
    <textarea rows="20" resizeable="false" class="txt-task-content"><?php echo $task->content ?></textarea>
    <hr>
    <label>
        Status 
        <select class="sel-task-status">
            <?php foreach(Status::all() as $status): ?>
            <option value="<?php echo $status->id ?>" <?php if($status->id == ($_GET['status_id'] ?: $task->status_id)) { ?> selected="selected" <?php } ?>><?php echo $status->name ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</div>