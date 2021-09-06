$(_=>{
    function updateTask(data, callback) {
        if (!data || Object.keys(data).length == 0) {
            return;
        }
        console.log(data);
        $.ajax({
            url:"/ajax/task-update.php",
            method: "post",
            data: data,
            success: callback,
        });
    }

    function updateStatus(data) {
        if (!data || Object.keys(data).length == 0) {
            return;
        }
        $.ajax({
            url:"/ajax/status-update.php",
            method: "post",
            data: data,
        });
    }

    function updateTaskContainerCount(container) {
        let $tasksContainer = $(container);
        $tasksContainer.parents(".div-status-column:first").find(".spn-task-count").text($tasksContainer.find(".div-task").length);            
    }

    function showTaskDetails(id, defaults) {
        if (!id) {
            id = null;
        }

        if (!defaults) {
            defaults = {};
        }

        $.ajax({
            url:"/ajax/task-details.php?id="+id+(defaults.status_id ? "&status_id=" + defaults.status_id : ""),
            success: function(response) {
                let $dialog;
                $dialog = $(response).dialog({
                    modal:true,
                    title:"Task Details",
                    width:"auto",
                    height:"auto",
                    close: function() {
                        $dialog.remove();
                    },
                    position: {
                        my: "center top",
                        at: "center top+100px",
                        of: window,
                    },
                    buttons: [
                        {
                            text: "DELETE",
                            icon: "ui-icon-trash",
                            class: "btn-task-delete "+(!id ? "hidden" : ""),
                            click: function() {
                                if(confirm("Delete this task?")) {
                                    updateTask({_delete: id}, function(){
                                        $dialog.remove();
                                        reloadBoard();
                                    });
                                }
                            },
                        },
                        {
                            text: "SAVE",
                            icon: "ui-icon-disk",
                            click: function(){
                                let data = {
                                    title: $dialog.find(".txt-task-title").val(),
                                    content: $dialog.find(".txt-task-content").val(),
                                    position: 0,
                                    status_id: $dialog.find(".sel-task-status").val(),
                                };
    
                                data = $.extend(data, defaults);
    
                                if (id) {
                                    data.id = id;
                                }
                                updateTask(data, function(){
                                    reloadBoard();
                                })
                                $dialog.remove();
                            },
                        }
                    ],
                });
            },
        });    
    }
    function reloadBoard() {
        $.ajax({
            url:"/ajax/board.php",
            success:function(response){
                $(".div-board").replaceWith(response);
                initBoard();
            },
        });    
    }

    function sortableBoard() {
        $(".div-board").sortable({
            items: ".div-status-column",
            axis: "x",
            handle: ".div-status-name",
            opacity: 0.5,
            update: function() {
                updateStatus({
                    _order:
                        $(".div-status-column")
                            .map(function(order, item) {
                                return $(item).data("id");
                            }).get()
                });
            }
        });

        $(".div-tasks-container").sortable({
            revert: true,
            helper:"clone",
            items: ".div-task",
            connectWith: ".div-tasks-container",
            opacity: 0.5,
            receive: function(evt, ui) {
                updateTask({
                    id: ui.item.data("id"),
                    status_id: ui.item.parents(".div-status-column:first").data("id"),
                });
            },
            update: function(evt, ui) {
                updateTaskContainerCount(this);
                updateTask({
                    _order:
                        $(this).find(".div-task")
                            .map(function(position, task) {
                                const $task = $(task);
                                $task.data({position: position * 2 + 2});
                                return $task.data("id");
                            }).get()
                });
            },
        });
    }

    function initBoard() {
        sortableBoard();
        $(".div-status-name, .div-task").map(function(i, target){
            $("<div class='div-add-task-helper'/>").appendTo(target);
        });
        $("#sel-task-sort").change();
    }
    initBoard();

    $("body")
        .on("click", ".div-task", function(){
            let $task = $(this);
            showTaskDetails($task.data("id"));
        })
        .on("click", ".div-add-task-helper", function(){
            const $helper = $(this);
            showTaskDetails(null, {
                status_id: $helper.parents(".div-status-column:first").data("id"),
                position: ($helper.parents(".div-task:first").data("position")+1 || 1),
            });
            return false;
        })
        .on("change", "#sel-task-sort", function(){
            let sortBy = $(this).val();
            $(".div-tasks-container").sortable(sortBy == "Priority" ? "enable" : "disable");
            function sortBasis(elem) {
                let $elem = $(elem);
                return (
                    (sortBy == "Priority")
                        ? $elem.data("position")*1
                        : $elem.text().toUpperCase()
                );
            }
            $(".div-tasks-container").each(function(){
                let $container = $(this);
                $container.find(".div-task").sort(function(a,b){
                    return sortBasis(b) < sortBasis(a) ? 1 : -1;
                }).appendTo($container);
            });
        })
    ;
});
