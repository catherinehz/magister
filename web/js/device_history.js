var ctx1 = document.getElementById("chartContainer1");
var ctx2 = document.getElementById("chartContainer2");

window.onload = function () {
    chartInit1();
    chartInit2();
}
function chartInit1() {
    var myLineChart1 = new Chart(ctx1, {
        type: 'line',
        data: {
            datasets: [
                {
                    label: "Зміна витрати NaOH в залежності від часу",
                    data: dataPointsArray1,
                    fill: true,
                    borderColor: "rgba(75,192,192,0.8)",
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
                        suggestedMin: 900,    // minimum will be 0, unless there is a lower value.
                        suggestedMax: 1000
                        // OR //
                        //beginAtZero: true   // minimum value will be 0.
                    }
                }]
            }
        }
    });
}
function chartInit2() {
    var myLineChart2 = new Chart(ctx2, {
        type: 'line',
        data: {
            datasets: [
                {
                    label: "Зміна концентрації CO2 на виході",
                    data: dataPointsArray2,
                    fill: true,
                    borderColor: "rgba(75,192,192,0.8)",
                    pointRadius: 2,
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
                        suggestedMin: 0,    // minimum will be 0, unless there is a lower value.
                        suggestedMax: 0.1
                        // OR //
                        //beginAtZero: true   // minimum value will be 0.
                    }
                }]
            }
        }
    });
}