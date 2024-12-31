document.addEventListener('DOMContentLoaded', function() {
    const refreshButton = document.getElementById('refresh-stats');
    const errorLogs = document.getElementById('error-logs');

    async function fetchStats() {
        try {
            const response = await fetch('/dashboard/monitoring/stats', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            updateStats(data);
        } catch (error) {
            console.error('Error fetching stats:', error);
            logErrorToPage(error.message);
        }
    }

    function updateStats(data) {
        // تحديث إحصائيات الزوار
        document.getElementById('today-visitors').textContent = data.visitors?.today || 0;
        document.getElementById('month-visitors').textContent = data.visitors?.month || 0;
        document.getElementById('year-visitors').textContent = data.visitors?.year || 0;
        document.getElementById('total-visitors').textContent = data.visitors?.total || 0;

        // تحديث إحصائيات النظام
        document.getElementById('php-version').textContent = data.system?.php_version || '-';
        document.getElementById('web-server').textContent = data.system?.web_server || '-';
        document.getElementById('memory-usage').textContent = data.system?.memory_usage || '-';

        // تحديث سجلات الأخطاء
        if (data.errors && Array.isArray(data.errors)) {
            errorLogs.innerHTML = '';
            const errorList = document.createElement('ul');
            errorList.classList.add('list-group');
            
            data.errors.forEach(error => {
                const listItem = document.createElement('li');
                listItem.classList.add('list-group-item', 'error-log-item');
                listItem.innerHTML = `
                    <div class="error-message">${error.message}</div>
                    <div class="error-details">
                        <span class="error-file">${error.file}</span>
                        <span class="error-line">Line: ${error.line}</span>
                    </div>
                `;
                errorList.appendChild(listItem);
            });
            
            errorLogs.appendChild(errorList);
        }
    }

    function logErrorToPage(message) {
        const errorList = errorLogs.querySelector('ul') || document.createElement('ul');
        errorList.classList.add('list-group');
        
        const listItem = document.createElement('li');
        listItem.classList.add('list-group-item', 'error-log-item', 'text-danger');
        listItem.innerHTML = `
            <div class="error-message">Error: ${message}</div>
            <div class="error-details">
                <span class="error-time">${new Date().toLocaleTimeString()}</span>
            </div>
        `;
        
        errorList.appendChild(listItem);
        if (!errorLogs.contains(errorList)) {
            errorLogs.appendChild(errorList);
        }
    }

    // جلب البيانات عند تحميل الصفحة
    fetchStats();

    // تحديث البيانات عند النقر على زر التحديث
    refreshButton.addEventListener('click', () => {
        refreshButton.disabled = true;
        refreshButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري التحديث...';
        
        fetchStats().finally(() => {
            refreshButton.disabled = false;
            refreshButton.textContent = 'تحديث';
        });
    });

    // تحديث تلقائي كل 5 دقائق
    setInterval(fetchStats, 5 * 60 * 1000);
});
