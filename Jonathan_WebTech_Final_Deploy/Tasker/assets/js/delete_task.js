document.addEventListener('DOMContentLoaded', function() {
    // Task deletion
    document.querySelectorAll('.delete-task').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this task?')) {
                return;
            }

            const taskId = this.dataset.taskId;
            const taskRow = this.closest('.task-row');

            try {
                const formData = new FormData();
                formData.append('task_id', taskId);
                formData.append('action', 'delete_task');

                const response = await fetch('../../functions/delete_task.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    taskRow.style.opacity = '0';
                    setTimeout(() => {
                        taskRow.remove();
                        window.updateTaskerStats();
                    }, 300);
                } else {
                    throw new Error('Failed to delete task');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to delete task. Please try again.');
            }
        });
    });

    // Project deletion
    document.querySelectorAll('.delete-project').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this project and all its tasks?')) {
                return;
            }

            const projectId = this.dataset.projectId;
            const projectCard = this.closest('.project-card');

            try {
                const formData = new FormData();
                formData.append('project_id', projectId);
                formData.append('action', 'delete_project');

                const response = await fetch('../../functions/delete_task.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    projectCard.style.opacity = '0';
                    setTimeout(() => {
                        projectCard.remove();
                        window.updateTaskerStats();
                    }, 300);
                } else {
                    throw new Error('Failed to delete project');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to delete project. Please try again.');
            }
        });
    });
});