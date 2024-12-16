document.addEventListener('DOMContentLoaded', function() {
    // Debug function
    const debug = (message, data = null) => {
        console.log(`[Tasker Debug] ${message}`, data || '');
    };

    // Stats update function
    async function updateStats() {
        try {
            const formData = new FormData();
            formData.append('ajax', '1');
            
            const response = await fetch('../../functions/load_stats.php', {
                method: 'POST',
                body: formData
            });
            const stats = await response.json();
            
            // Update stats cards
            document.querySelector('[data-stat="total"]').textContent = stats.totalTasks;
            document.querySelector('[data-stat="completed"]').textContent = stats.completedTasks;
            document.querySelector('[data-stat="in-progress"]').textContent = stats.inProgressTasks;
            document.querySelector('[data-stat="pending"]').textContent = stats.pendingTasks;

            // Update pie chart
            const chart = Chart.getChart('taskChart');
            if (chart) {
                chart.data.datasets[0].data = [
                    stats.completedTasks,
                    stats.inProgressTasks,
                    stats.pendingTasks
                ];
                chart.update();
            }
        } catch (error) {
            debug('Error updating stats:', error);
        }
    }

    // Initialize loading state
    const setLoadingState = (element, isLoading) => {
        if (isLoading) {
            element.classList.add('loading');
            element.style.opacity = '0.5';
            element.style.pointerEvents = 'none';
        } else {
            element.classList.remove('loading');
            element.style.opacity = '1';
            element.style.pointerEvents = 'auto';
        }
    };

    // Task Status Management
    const statusSelects = document.querySelectorAll('.task-status');
    debug(`Found ${statusSelects.length} status select elements`);

    statusSelects.forEach(select => {
        // Store initial value
        select.dataset.previousValue = select.value;

        select.addEventListener('change', async function() {
            const taskRow = this.closest('.task-row');
            const taskId = taskRow.dataset.taskId;
            debug('Status change initiated', {
                taskId: taskId,
                newStatus: this.value,
                previousStatus: this.dataset.previousValue
            });

            // Prevent double submission
            if (taskRow.classList.contains('loading')) {
                debug('Preventing double submission');
                return;
            }

            const formData = new FormData();
            formData.append('task_id', taskId);
            formData.append('status', this.value);
            formData.append('update_status', '1');
            formData.append('ajax', '1');

            try {
                setLoadingState(taskRow, true);
                debug('Sending status update request');

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const responseData = await response.text();
                debug('Server response', responseData);

                if (response.ok) {
                    debug('Status update successful');
                    
                    // Visual feedback and stats update
                    taskRow.classList.add('task-updated');
                    await updateStats();
                    
                    setTimeout(() => {
                        taskRow.classList.remove('task-updated');
                        setLoadingState(taskRow, false);
                    }, 500);
                } else {
                    throw new Error(`Server returned ${response.status}: ${responseData}`);
                }
            } catch (error) {
                debug('Error during status update', error);
                alert('Failed to update task status. Please try again.');
                this.value = this.dataset.previousValue;
                setLoadingState(taskRow, false);
            }
        });

        // Store previous value on focus
        select.addEventListener('focus', function() {
            this.dataset.previousValue = this.value;
        });
    });

    // Task Date Validation
    const taskForms = document.querySelectorAll('.task-form');
    debug(`Found ${taskForms.length} task forms`);

    taskForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const startDate = new Date(this.querySelector('.task-start-date').value);
            const endDate = new Date(this.querySelector('.task-end-date').value);
            const errorDiv = this.nextElementSibling;

            // Clear previous errors
            errorDiv.textContent = '';
            errorDiv.classList.add('hidden');

            // Validate dates
            if (endDate < startDate) {
                debug('Task date validation failed', {
                    startDate: startDate,
                    endDate: endDate
                });
                e.preventDefault();
                errorDiv.textContent = 'End date cannot be earlier than start date';
                errorDiv.classList.remove('hidden');
                return false;
            }

            // Check if dates are in the past
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (startDate < today) {
                e.preventDefault();
                errorDiv.textContent = 'Start date cannot be in the past';
                errorDiv.classList.remove('hidden');
                return false;
            }

            debug('Task date validation passed');
            return true;
        });
    });

    // Project Form Validation
    const projectForm = document.getElementById('project-form');
    if (projectForm) {
        debug('Project form found');

        projectForm.addEventListener('submit', function(e) {
            const deadline = new Date(this.querySelector('#project-deadline').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const errorDiv = document.getElementById('project-error');

            // Clear previous errors
            errorDiv.textContent = '';
            errorDiv.classList.add('hidden');

            if (deadline < today) {
                debug('Project deadline validation failed', {
                    deadline: deadline,
                    today: today
                });
                e.preventDefault();
                errorDiv.textContent = 'Project deadline cannot be in the past';
                errorDiv.classList.remove('hidden');
                return false;
            }

            debug('Project deadline validation passed');
            return true;
        });
    }

    // Add visual feedback styles if not already in CSS
    if (!document.getElementById('taskerStyles')) {
        const styles = document.createElement('style');
        styles.id = 'taskerStyles';
        styles.textContent = `
            .loading {
                opacity: 0.5;
                pointer-events: none;
                transition: opacity 0.3s ease;
            }
            .task-updated {
                background-color: #f0fff4 !important;
                transition: background-color 0.3s ease;
            }
            .task-row {
                transition: all 0.3s ease;
            }
        `;
        document.head.appendChild(styles);
    }
});