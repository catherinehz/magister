//TODO: Відобразити завдання на графіку

var updateDeviceInterval = null;
$(document).ready(function(){
    updateDeviceStats();
    updateDeviceInterval = setInterval(function(){ updateDeviceStats() }, 10*1000); //кожні 30 секунд
});

function updateDeviceStats() {
    $.ajax({
	url: baseUrl+"/api/getDeviceRecords/"+$('#deviceId').val()+"/0/100",
	type: "POST",
	data: {},
	success: function(response) {
            var deviceData = jQuery.parseJSON(response);
            updateDeviceNumbers(deviceData);
            redrawDeviceCharts(deviceData);
	},
        error: function(response) {
            console.log('updateDeviceStats(): Сталася помилка, спробуйте пізніше!');
        }
    });
}

function updateDeviceNumbers(deviceData) {
    $('#C2H2-Fg').val(deviceData.records[0].data['C2H2-Fg']);
    $('#NaOH-Fr').val(deviceData.records[0].data['NaOH-Fr']);
    $('#CO2-in-C2H2-y0').val(deviceData.records[0].data['CO2-in-C2H2-y0']);
    $('#CO2-in-C2H2-y1').val(deviceData.records[0].data['CO2-in-C2H2-y1']);
    return true;
}

var charts = {};
function redrawDeviceCharts(deviceData) {
    drawChart(deviceData, 'NaOH-Fr', "Зміна витрати NaOH в залежності від часу");
    drawChart(deviceData, 'CO2-in-C2H2-y1', "Зміна конц. CO2 на виході в залежності від часу");
}
function drawChart(deviceData, recordDataKey, chartLabel) {
    //Формуємо дані для графіка
    var chartDataArray = new Array();
    var chartDataTargetArray = new Array();
    var chartMin = null;
    var chartMax = null;
    $.each(deviceData.records, function(key, record){
        //Факт. показники
        var xData = record.createdAt.date;
        var yData = parseFloat(record.data[recordDataKey]);
        chartDataArray.push({x:xData,y:yData});

        //Границі для побудови графіку
        if (chartMin === null) chartMin = yData;
        if (chartMax === null) chartMax = yData;
        if (chartMin > yData) chartMin = yData;
        if (chartMax < yData) chartMax = yData;

        //Завдання
        if (record.data.hasOwnProperty(recordDataKey+'-target')) {
            yData = parseFloat(record.data[recordDataKey+'-target']);
            chartDataTargetArray.push({x:xData,y:yData});
        }

        //Границі для побудови графіку
        if (chartMin > yData) chartMin = yData;
        if (chartMax < yData) chartMax = yData;
    });
    var chartDeviation = chartMax - chartMin;
    chartMax = chartMax + ((chartDeviation/100)*10);
    chartMin = chartMin - ((chartDeviation/100)*10);
    if (chartMin < 0 || chartMax < 1) chartMin = 0;

    var chartId = recordDataKey+'-chart';
    if (charts.hasOwnProperty(chartId)) { //Оновлюємо графік
        charts[chartId].chartObj.data.datasets[0].data = chartDataArray;
        if (chartDataTargetArray.length) charts[chartId].chartObj.data.datasets[1].data = chartDataTargetArray;
        //charts[chartId].chartObj.options.scales.yAxes.ticks.suggestedMin = chartMin;
        //charts[chartId].chartObj.options.scales.yAxes.ticks.suggestedMax = chartMax;
        charts[chartId].chartObj.update();
    } else { //Створюємо графік (якщо не було досі)
        charts[chartId] = {
            id: chartId,
            domObject: null,
            chartObj: null
        };

        //Створюємо HTML-елемент графіка
        $('#deviceCharts').append('<div class="col-sm-6"><div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title">'+chartLabel+'</h3></div><div class="panel-body"><canvas id="'+chartId+'"></canvas></div></div></div>');
        charts[chartId].domObject = document.getElementById(chartId);
        var chartOptions = {
            type: 'line',
            data: {
                datasets: [
                    {
                        label: chartLabel,
                        data: chartDataArray,
                        fill: true,
                        borderColor: "#337AB7",
                        pointRadius: 1,
                        lineTension:0.4,
                        borderWidth:1.1
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [{
                        type: 'time',
                        time: {
                            displayFormats: {
                                minute: 'HH:mm'
                            }
                        }
                    }],
                    yAxes: [{
                        display: true,
                        ticks: {
                            suggestedMin: chartMin,    // minimum will be 0, unless there is a lower value.
                            suggestedMax: chartMax
                            // OR //
                            //beginAtZero: true   // minimum value will be 0.
                        }
                    }]
                },
                legend: {
                    display: false
                }
            }
        };

        if (chartDataTargetArray.length) {
            chartOptions.data.datasets.push({
                label: 'Завдання для '+chartLabel,
                data: chartDataTargetArray,
                fill: false,
                borderColor: "rgba(255,70,70,0.8)",
                pointRadius: 0,
                lineTension:0,
                borderWidth:0.9
            });
        }

        //Ініціалізация рендеру графіка
        charts[chartId].chartObj = new Chart(
            charts[chartId].domObject,
            chartOptions
        );
    }
    //Оновлення графіка
}

/* ------------------------- */
function stopUpdating() {
    clearInterval(updateDeviceInterval);
}