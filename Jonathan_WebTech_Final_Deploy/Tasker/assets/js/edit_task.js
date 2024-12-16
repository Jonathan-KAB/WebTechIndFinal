document.addEventListener('DOMContentLoaded', function() {
    // Modal handling
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.modal-close');

    function closeModals() {
        modals.forEach(modal => modal.classList.add('hidden'));
    }

    closeButtons.forEach(button => {
        button.addEventListener('click', closeModals);
    });

    // Close on outside click
    modals.forEach(modal => {
        modal.addEventListener('click', e => {
            if (e.target === modal) closeModals();
        });
    });

    // Edit Project
    document.querySelectorAll('.edit-project').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('editProjectModal');
            const form = document.getElementById('editProjectForm');
            
            form.project_id.value = this.dataset.projectId;
            form.name.value = this.dataset.projectName;
            form.deadline.value = this.dataset.projectDeadline;
            
            modal.classList.remove('hidden');
        });
    });

    // Edit Task
    document.querySelectorAll('.edit-task').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('editTaskModal');
            const form = document.getElementById('editTaskForm');
            
            form.task_id.value = this.dataset.taskId;
            form.description.value = this.dataset.taskDescription;
            form.start_date.value = this.dataset.taskStart;
            form.end_date.value = this.dataset.taskEnd;
            
            modal.classList.remove('hidden');
        });
    });

    // Handle form submissions
    document.getElementById('editProjectForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            formData.append('action', 'update_project');

            const response = await fetch('../../functions/edit_task.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                window.location.reload();
            } else {
                throw new Error('Failed to update project');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update project. Please try again.');
        }
    });

    document.getElementById('editTaskForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            formData.append('action', 'update_task');

            const response = await fetch('../../functions/edit_task.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                window.location.reload();
            } else {
                throw new Error('Failed to update task');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update task. Please try again.');
        }
    });
});