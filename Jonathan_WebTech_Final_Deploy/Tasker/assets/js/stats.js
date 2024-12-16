document.addEventListener('DOMContentLoaded', function() {
    // Pie Chart
    const taskChart = document.getElementById('taskChart');
    if (taskChart && taskChart.getContext) {
        const ctx = taskChart.getContext('2d');
        window.taskerPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Completed', 'In Progress', 'Pending'],
                datasets: [{
                    data: [
                        parseInt(taskChart.dataset.completed || 0),
                        parseInt(taskChart.dataset.in_progress || 0),
                        parseInt(taskChart.dataset.pending || 0)
                    ],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(234, 179, 8, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Bar Chart
    const taskBarChart = document.getElementById('taskBarChart');
    if (taskBarChart && taskBarChart.getContext) {
        const ctxBar = taskBarChart.getContext('2d');
        window.taskerBarChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Completed', 'In Progress', 'Pending'],
                datasets: [{
                    label: 'Number of Tasks',
                    data: [
                        parseInt(taskBarChart.dataset.completed || 0),
                        parseInt(taskBarChart.dataset.in_progress || 0),
                        parseInt(taskBarChart.dataset.pending || 0)
                    ],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: [
                        'rgba(34, 197, 94, 1)',
                        'rgba(234, 179, 8, 1)',
                        'rgba(239, 68, 68, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Update both charts when stats change
    window.updateTaskerStats = async function() {
        // ... existing stats update code ...
        
        if (window.taskerPieChart && window.taskerBarChart) {
            const newData = [
                stats.completedTasks,
                stats.inProgressTasks,
                stats.pendingTasks
            ];
            
            window.taskerPieChart.data.datasets[0].data = newData;
            window.taskerPieChart.update();
            
            window.taskerBarChart.data.datasets[0].data = newData;
            window.taskerBarChart.update();
        }
    };
});