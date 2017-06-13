$(document).ready(function(){
    $('#savePidConfig').click(savePidRegulatorConfig);
    $('#updatePidChart').click(getPidChartData);
    getPidChartData();

    $('#Kp').keyup(updatePidButtonForDb);
    $('#Ki').keyup(updatePidButtonForDb);
    $('#Kd').keyup(updatePidButtonForDb);

    $('#Kp').keyup(updatePidButtonForChart);
    $('#Ki').keyup(updatePidButtonForChart);
    $('#Kd').keyup(updatePidButtonForChart);
});

var oldKpForChart = parseFloat($('#Kp').val().trim());
var oldKiForChart = parseFloat($('#Ki').val().trim());
var oldKdForChart = parseFloat($('#Kd').val().trim());

var oldKpForDB = parseFloat($('#Kp').val().trim());
var oldKiForDB = parseFloat($('#Ki').val().trim());
var oldKdForDB = parseFloat($('#Kd').val().trim());

function updatePidButtonForDb() {
    //Перевірка, чи є зміни у полі "Завдання"
    var Kp = parseFloat($('#Kp').val().trim());
    if (isNaN(Kp)) {
        //TODO: Показати повідомлення: "Wrong value"
        return false;
    }
    var Ki = parseFloat($('#Ki').val().trim());
    if (isNaN(Ki)) {
        //TODO: Показати повідомлення: "Wrong value"
        return false;
    }
    var Kd = parseFloat($('#Kd').val().trim());
    if (isNaN(Kd)) {
        //TODO: Показати повідомлення: "Wrong value"
        return false;
    }

    //Змін нема
    if (oldKpForDB == Kp && oldKiForDB == Ki && oldKdForDB == Kd) {
        $('#savePidConfig').addClass('disabled');
        $('#savePidConfig').removeClass('btn-danger');
        $('#savePidConfig').addClass('btn-default');
        return false;
    } else {
        //Зміни є
        $('#savePidConfig').removeClass('disabled');
        $('#savePidConfig').removeClass('btn-default');
        $('#savePidConfig').addClass('btn-danger');
        return true;
    }
}

function updatePidButtonForChart() {
    //Перевірка, чи є зміни у полі "Завдання"
    var Kp = parseFloat($('#Kp').val().trim());
    if (isNaN(Kp)) {
        //TODO: Показати повідомлення: "Wrong value"
        return false;
    }
    var Ki = parseFloat($('#Ki').val().trim());
    if (isNaN(Ki)) {
        //TODO: Показати повідомлення: "Wrong value"
        return false;
    }
    var Kd = parseFloat($('#Kd').val().trim());
    if (isNaN(Kd)) {
        //TODO: Показати повідомлення: "Wrong value"
        return false;
    }

    if (oldKpForChart == Kp && oldKiForChart == Ki && oldKdForChart == Kd) {
        $('#updatePidChart').addClass('disabled');
        $('#updatePidChart').removeClass('btn-success');
        $('#updatePidChart').addClass('btn-default');
        return false;
    } else {
        $('#updatePidChart').removeClass('disabled');
        $('#updatePidChart').removeClass('btn-default');
        $('#updatePidChart').addClass('btn-success');
        return true;
    }
}

function savePidRegulatorConfig() {
    //Перевірка, чи є зміни у полі "Завдання"
    if (!updatePidButtonForDb()) return false;

    //Є зміни у полі "Завдання" - відсилаємо нове значення для запису у базу
    $.ajax({
	url: baseUrl+"/api/updateDeviceConfig/"+$('#deviceId').val(),
	type: "POST",
	data: {'Kp':$('#Kp').val(),'Ki':$('#Ki').val(),'Kd':$('#Kd').val()},
	success: function(response) {
            //Оновити кнопку
            oldKpForDB = parseFloat($('#Kp').val().trim());
            oldKiForDB = parseFloat($('#Ki').val().trim());
            oldKdForDB = parseFloat($('#Kd').val().trim());
            updatePidButtonForDb();

            //Оновити графік
            getPidChartData();

            //Показати повідомлення
            //TODO
	},
        error: function(response) {
            console.log('savePidRegulatorConfig: Сталася помилка, спробуйте пізніше!');
        }
    });
}

var pidChartObject = false;
var flag = false;
function getPidChartData() {
    if (flag) {
        //Перевірка, чи є зміни у полі "Завдання"
        if (!updatePidButtonForChart()) return false;
    }

    flag = true;

    //Є зміни у полі "Завдання" - відсилаємо нове значення для запису у базу
    var pidConfig = {'Kp':$('#Kp').val(),'Ki':$('#Ki').val(),'Kd':$('#Kd').val()};
    $.ajax({
	url: baseUrl+"/api/generatePidChartsNew/",
	type: "POST",
        data: pidConfig,
	success: function (response) {
            var chartData = jQuery.parseJSON(response);
            updatePidChart(chartData[0]);

            //Оновити кнопку
            oldKpForChart = parseFloat($('#Kp').val().trim());
            oldKiForChart = parseFloat($('#Ki').val().trim());
            oldKdForChart = parseFloat($('#Kd').val().trim());
            updatePidButtonForChart();
        },
        error: function(response) {
            console.log(response);
            console.log('getPidChartData: Сталася помилка, спробуйте пізніше!');
        }
    });
}

function updatePidChart(chartData) {
    var targetDataArray = [];
    var chartValues = [];
    var counter = 0;
    $.each(chartData, function(key, val){
        counter++;
        targetDataArray.push({x:counter, y:val["CO2-in-C2H2-y1-target"]});
        chartValues.push({x:counter, y:val["CO2-in-C2H2-y1"]});
    });


    if (!pidChartObject) {
        var domObject = document.getElementById("PidChart");
        pidChartObject = new Chart(domObject, {
            type: 'line',
            data: {
                datasets: [
                    {
                        label: "Зміна витрати NaOH в залежності від часу",
                        data: chartValues,
                        fill: true,
                        borderColor: "rgba(75,192,192,0.8)",
                        pointRadius: 1,
                        lineTension: 0.4,
                        borderWidth: 1.1
                    },
                    {
                        label: "Завдання",
                        data: targetDataArray,
                        fill: false,
                        borderColor: "rgba(255,70,70,0.8)",
                        pointRadius: 0,
                        lineTension:0,
                        borderWidth:0.9
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
                            /*ticks: {
                                suggestedMin: 0, // minimum will be 0, unless there is a lower value.
                                suggestedMax: 1.5
                            }*/
                        }]
                },
                legend: {
                    display: false
                }

            }
        });
    } else {
        pidChartObject.data.datasets[0].data = chartValues;
        pidChartObject.data.datasets[1].data = targetDataArray;
        pidChartObject.update();
    }
}