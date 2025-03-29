// graph.js

// Helper function to safely get the maximum value from an array
function safeMax(arr) {
  return arr.length ? Math.max(...arr) : 5;
}

// Chart variables
let fireIncidentChart, casualtyChart, classificationChart, motiveChart;

// Global variables to store the chart images
let cachedFireIncidentImage = "";
let cachedCasualtyImage = "";
let cachedClassificationImage = "";
let cachedMotiveImage = "";

// 1. CREATE FIRE INCIDENT CHART
function createFireIncidentChart() {
  const dataArr = labels.map(label => incidentData[label] || 0);
  const maxValue = safeMax(dataArr);

  if (fireIncidentChart) {
    fireIncidentChart.destroy();
  }

  fireIncidentChart = new Chart(document.getElementById('fireIncidentChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Fire Incidents',
        data: dataArr,
        backgroundColor: 'rgba(75,192,192,0.7)',
        borderColor: 'rgba(75,192,192,1)',
        borderWidth: 1
      }]
    },
    options: {
      // Disable or reduce animation to capture quickly
      animation: {
        duration: 500,
        onComplete: function() {
          // Capture the image after chart finishes animating
          cachedFireIncidentImage = fireIncidentChart.toBase64Image();
        }
      },
      maintainAspectRatio: false,
      responsive: true,
      scales: {
        x: { ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 } },
        y: { beginAtZero: true, max: maxValue + 5, ticks: { stepSize: 1 } }
      },
      plugins: {
        legend: { position: 'top' },
        datalabels: {
          formatter: function(value) {
            return value === 0 ? "" : value;
          },
          color: '#000',
          font: { size: 10 }
        }
      }
    }
  });
  document.getElementById('fireIncidentDescription').textContent =
    'Fire Incidents from ' + startDate + ' to ' + endDate + ' (' + aggregationType + ')';
}

// 2. CREATE CASUALTY CHART
function createCasualtyChart() {
  const ri = labels.map(label => casualtyData[label] ? (casualtyData[label]['Resident Injured'] || 0) : 0);
  const rd = labels.map(label => casualtyData[label] ? (casualtyData[label]['Resident Deaths'] || 0) : 0);
  const fi = labels.map(label => casualtyData[label] ? (casualtyData[label]['Firefighter Injured'] || 0) : 0);
  const fd = labels.map(label => casualtyData[label] ? (casualtyData[label]['Firefighter Deaths'] || 0) : 0);
  const maxValue = Math.max(safeMax(ri), safeMax(rd), safeMax(fi), safeMax(fd));

  if (casualtyChart) {
    casualtyChart.destroy();
  }

  casualtyChart = new Chart(document.getElementById('casualtyChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        { label: 'Resident Injured', data: ri, backgroundColor: 'rgba(255,205,86,0.7)' },
        { label: 'Resident Deaths', data: rd, backgroundColor: 'rgba(255,99,132,0.7)' },
        { label: 'Firefighter Injured', data: fi, backgroundColor: 'rgba(54,162,235,0.7)' },
        { label: 'Firefighter Deaths', data: fd, backgroundColor: 'rgba(153,102,255,0.7)' }
      ]
    },
    options: {
      animation: {
        duration: 500,
        onComplete: function() {
          cachedCasualtyImage = casualtyChart.toBase64Image();
        }
      },
      maintainAspectRatio: false,
      responsive: true,
      scales: {
        x: { stacked: true, ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 } },
        y: { stacked: true, beginAtZero: true, max: maxValue + 5, ticks: { stepSize: 1 } }
      },
      plugins: {
        legend: { position: 'top' },
        datalabels: {
          formatter: function(value, context) {
            if (value === 0) return "";
            const chart = context.chart;
            const index = context.dataIndex;
            let sum = 0;
            chart.data.datasets.forEach(ds => { sum += ds.data[index]; });
            return sum ? ((value / sum) * 100).toFixed(1) + '%' : "";
          },
          color: '#000',
          font: { size: 10 }
        }
      }
    }
  });
  document.getElementById('casualtyDescription').textContent =
    'Casualties from ' + startDate + ' to ' + endDate + ' (' + aggregationType + ')';
}

// 3. CREATE CLASSIFICATION CHART
function createClassificationChart() {
  const labelsArr = Object.keys(classificationData);
  const valuesArr = Object.values(classificationData);
  const total = valuesArr.reduce((sum, val) => sum + val, 0);

  if (classificationChart) {
    classificationChart.destroy();
  }

  classificationChart = new Chart(document.getElementById('classificationChart').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: labelsArr,
      datasets: [{
        data: valuesArr,
        backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#E7E9ED']
      }]
    },
    options: {
      animation: {
        duration: 500,
        onComplete: function() {
          cachedClassificationImage = classificationChart.toBase64Image();
        }
      },
      maintainAspectRatio: false,
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        datalabels: {
          formatter: function(value) {
            if (value === 0) return "";
            let percentage = total ? (value / total * 100).toFixed(1) + '%' : "";
            return percentage;
          },
          color: '#fff',
          font: { size: 12 }
        }
      }
    }
  });
}

// 4. CREATE MOTIVE CHART
function createMotiveChart() {
  const labelsArr = Object.keys(motiveData);
  const valuesArr = Object.values(motiveData);
  const total = valuesArr.reduce((sum, val) => sum + val, 0);

  if (motiveChart) {
    motiveChart.destroy();
  }

  motiveChart = new Chart(document.getElementById('motiveChart').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: labelsArr,
      datasets: [{
        data: valuesArr,
        backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#E7E9ED']
      }]
    },
    options: {
      animation: {
        duration: 500,
        onComplete: function() {
          cachedMotiveImage = motiveChart.toBase64Image();
        }
      },
      maintainAspectRatio: false,
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        datalabels: {
          formatter: function(value) {
            if (value === 0) return "";
            let percentage = total ? (value / total * 100).toFixed(1) + '%' : "";
            return percentage;
          },
          color: '#fff',
          font: { size: 12 }
        }
      }
    }
  });
}

// 5. UPDATE INCIDENT COUNTER
function updateIncidentCounter() {
  let total = 0;
  labels.forEach(label => { total += incidentData[label] || 0; });
  document.getElementById('incidentCounter').textContent = total;
}

// 6. INITIALIZE CHARTS
function initCharts() {
  createFireIncidentChart();
  createCasualtyChart();
  createClassificationChart();
  createMotiveChart();
  updateIncidentCounter();
}
initCharts();

// 7. VIEW REPORT: Use cached images
document.getElementById('viewReportBtn').addEventListener('click', () => {
  // Build the HTML for the full report
  let reportHTML = `
    <div style="padding: 20px; font-family: Arial, sans-serif; width:575px; margin: auto;">
      <h2 style="text-align:center; color: #2c3e50;">Fire Incidents Dashboard Detailed Report</h2>
      <hr>
      <p>
        This report covers the period from <strong>${startDate}</strong> to <strong>${endDate}</strong>.
        A total of <strong>${document.getElementById('incidentCounter').textContent}</strong> incidents were recorded.
        Below is a detailed analysis of the incidents, casualties, classifications, and motives.
      </p>
      <hr>
    </div>
  `;
  // Fire Incident
  reportHTML += `
    <div style="margin-bottom: 30px;">
      <h3 style="color: #e74c3c;">Fire Incident Graph</h3>
      <p>This chart displays the number of fire incidents aggregated by <strong>${aggregationType}</strong>.</p>
      <div style="text-align: center;">
        <img src="${cachedFireIncidentImage}" style="width:100%; max-width:575px; border: 2px solid #e74c3c; border-radius: 5px;" alt="Fire Incident Graph">
      </div>
    </div>
  `;
  // Casualty
  reportHTML += `
    <div style="margin-bottom: 30px;">
      <h3 style="color: #e67e22;">Casualty Graph</h3>
      <p>The stacked bar chart below shows the distribution of casualties, including Resident Injured, Resident Deaths, Firefighter Injured, and Firefighter Deaths.</p>
      <div style="text-align: center;">
        <img src="${cachedCasualtyImage}" style="width:100%; max-width:575px; border: 2px solid #e67e22; border-radius: 5px;" alt="Casualty Graph">
      </div>
    </div>
  `;
  // Classification
  reportHTML += `
    <div style="margin-bottom: 30px;">
      <h3 style="color: #3498db;">Classification Chart</h3>
      <p>This doughnut chart displays the distribution of fire incident classifications.</p>
      <div style="text-align: center;">
        <img src="${cachedClassificationImage}" style="width:100%; max-width:575px; border: 2px solid #3498db; border-radius: 5px;" alt="Classification Chart">
      </div>
      <p style="margin-top: 10px; font-weight: bold;">Classification Summary:</p>
      <table style="width:100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
          <tr style="background-color: #ecf0f1;">
            <th style="border: 1px solid #bdc3c7; padding: 8px;">Classification</th>
            <th style="border: 1px solid #bdc3c7; padding: 8px;">Count</th>
          </tr>
        </thead>
        <tbody>
  `;
  for (let cls in classificationData) {
    reportHTML += `<tr>
        <td style="border: 1px solid #bdc3c7; padding: 8px;">${cls}</td>
        <td style="border: 1px solid #bdc3c7; padding: 8px;">${classificationData[cls]}</td>
      </tr>`;
  }
  reportHTML += `
        </tbody>
      </table>
    </div>
  `;
  // Motive
  reportHTML += `
    <div style="margin-bottom: 30px;">
      <h3 style="color: #9b59b6;">Motive Chart</h3>
      <p>This doughnut chart shows the breakdown of motives behind the fire incidents.</p>
      <div style="text-align: center;">
        <img src="${cachedMotiveImage}" style="width:100%; max-width:575px; border: 2px solid #9b59b6; border-radius: 5px;" alt="Motive Chart">
      </div>
    </div>
  `;
  document.getElementById('reportContent').innerHTML = reportHTML;

  // Show the modal
  const reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
  reportModal.show();
});

// 8. DOWNLOAD PDF
document.getElementById('downloadPDFBtn').addEventListener('click', () => {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF('p', 'pt', 'a4');

  // Clone the report content to preserve screen design
  const reportElement = document.getElementById('reportContent');
  const clone = reportElement.cloneNode(true);
  // Force inline styles so html2canvas can capture them
  clone.style.width = "575px";
  clone.style.boxSizing = "border-box";
  clone.style.fontFamily = "Arial, sans-serif";

  doc.html(clone, {
    callback: function (doc) {
      doc.save('FullReport.pdf');
    },
    margin: [10, 10, 10, 10],
    x: 10,
    y: 10,
    width: 575,
    html2canvas: {
      scale: 1,
      useCORS: true,
      allowTaint: true
    },
    autoPaging: 'text'
  });
});
