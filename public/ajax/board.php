<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once APPDIR . 'models/Status.php';
?>
<div class="div-board">
<?php
foreach (Status::query('SELECT * FROM status ORDER BY position') as $status):
    ?>
    <div class="div-status-column" data-id="<?php echo $status->id ?>">
        <?php $tasks = $status->tasks() ?>
        <div class="div-status-name">
            <?php echo $status->name ?> (<span class="spn-task-count"><?php echo count($tasks) ?></span>)
        </div>
        <div class="div-tasks-container">
            <?php
            foreach($tasks as $position=>$task):
                $task->update(['position' => $position * 2 + 2]);
                ?>
                <div class="div-task" data-id="<?php echo $task->id ?>" data-position="<?php echo $task->position ?>">
                    <div class="div-task-title"><?php echo $task->title ?></div>
                </div>
                <?php
            endforeach;
            ?>
        </div>
    </div>
    <?php
endforeach;
?>
</div>
