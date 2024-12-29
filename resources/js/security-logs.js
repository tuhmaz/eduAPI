document.addEventListener('DOMContentLoaded', function() {
    // Handle export button click
    const exportButton = document.querySelector('a[href*="security.logs.export"]');
    if (exportButton) {
        exportButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            const originalContent = exportButton.innerHTML;
            exportButton.innerHTML = '<i class="ri-loader-4-line me-1 animate-spin"></i>جاري التصدير...';
            exportButton.classList.add('disabled');
            
            // Fetch the logs data
            fetch(exportButton.href)
                .then(response => response.json())
                .then(data => {
                    // Convert data to CSV
                    const csvContent = convertToCSV(data);
                    
                    // Create and trigger download
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    const url = URL.createObjectURL(blob);
                    
                    link.setAttribute('href', url);
                    link.setAttribute('download', `security-logs-${new Date().toISOString().split('T')[0]}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Reset button state
                    exportButton.innerHTML = originalContent;
                    exportButton.classList.remove('disabled');
                })
                .catch(error => {
                    console.error('Error exporting logs:', error);
                    // Reset button state and show error
                    exportButton.innerHTML = originalContent;
                    exportButton.classList.remove('disabled');
                    alert('حدث خطأ أثناء تصدير السجلات. يرجى المحاولة مرة أخرى.');
                });
        });
    }
});

// Helper function to convert JSON data to CSV
function convertToCSV(data) {
    if (!data || !data.length) return '';
    
    // Get headers from first item
    const headers = Object.keys(data[0]);
    
    // Create CSV rows
    const csvRows = [
        // Add BOM for proper UTF-8 encoding in Excel
        '\uFEFF' + headers.join(','),
        // Add data rows
        ...data.map(row => 
            headers.map(header => {
                let cell = row[header] || '';
                // Escape quotes and wrap in quotes if contains comma or newline
                cell = cell.toString().replace(/"/g, '""');
                if (cell.includes(',') || cell.includes('\n')) {
                    cell = `"${cell}"`;
                }
                return cell;
            }).join(',')
        )
    ];
    
    return csvRows.join('\n');
}
