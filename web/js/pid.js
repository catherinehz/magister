$(document).ready(function(){
    $('#updatePidRegulator').click(updatePidRegulator);
    getPidChartData();
});

function updatePidRegulator() {
    //Є зміни у полі "Завдання" - відсилаємо нове значення для запису у базу
    $.ajax({
	url: baseUrl+"/api/updateDeviceConfig/"+$('#deviceId').val(),
	type: "POST",
	data: {'Kp':$('#Kp').val(),'Ki':$('#Ki').val(),'Kd':$('#Kd').val()},
	success: function(response) {
            console.log(response);
            getPidChartData();
	},
        error: function(response) {
            console.log(response);
            alert('Сталася помилка, спробуйте пізніше!');
        }
    });
}

var pidChartObject = false;
function getPidChartData() {
    //Є зміни у полі "Завдання" - відсилаємо нове значення для запису у базу

    $.ajax({
	url: baseUrl+"/api/generatePidChart/"+$('#deviceId').val(),
	type: "GET",
	success: function (response) {
            //console.log(response);
            var chartData = jQuery.parseJSON(response);

            updatePidChart(chartData);

        },
        error: function(response) {
            console.log(response);
            alert('Сталася помилка, спробуйте пізніше!');
        }
    });
}

function updatePidChart(chartData) {
     if (!pidChartObject) {
        var ctxPid = document.getElementById("PidChart");
        pidChartObject = new Chart(ctxPid, {
            type: 'line',
            data: {
                datasets: [
                    {
                        label: "Зміна витрати NaOH в залежності від часу",
                        data: chartData,
                        fill: true,
                        borderColor: "rgba(75,192,192,0.8)",
                        pointRadius: 1,
                        lineTension: 0.4,
                        borderWidth: 1.1
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [{
                            type: 'linear',
                            position: 'bottom'
                        }],
                    yAxes: [{
                            display: true,
                            ticks: {
                                suggestedMin: 0, // minimum will be 0, unless there is a lower value.
                                suggestedMax: 1.5
                            }
                        }]
                }
            }
        });
    } else {
        pidChartObject.data.datasets[0].data = chartData;
        pidChartObject.update();
    }
}