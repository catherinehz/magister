window.onload = function () {
    chartInit();
}
function chartInit() {
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: dataPointsArray1,
        options: {}
    });
    
}