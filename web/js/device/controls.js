var baseUrl = $('meta[name=baseurl]').attr("content");
var previousCO2Target = parseFloat($('#CO2-in-C2H2-y1-target').val().trim());

$(document).ready(function(){
    $('#saveCo2Config').click(saveCo2Config);
    $('#CO2-in-C2H2-y1-target').keyup(updateCo2ConfigBtn);
});

function updateCo2ConfigBtn() {
    //Перевірка, чи є зміни у полі "Завдання"
    var currentCO2Target = parseFloat($('#CO2-in-C2H2-y1-target').val().trim());
    if (isNaN(currentCO2Target)) {
        alert('Неправильне значення! Напр: 0.06');
        return;
    }

    //Змін нема
    if (previousCO2Target == currentCO2Target) {
        $('#saveCo2Config').addClass('disabled');
        $('#saveCo2Config').removeClass('btn-success');
        $('#saveCo2Config').addClass('btn-default');
        return;
    }

    //Зміни є
    $('#saveCo2Config').removeClass('disabled');
    $('#saveCo2Config').removeClass('btn-default');
    $('#saveCo2Config').addClass('btn-success');
}

function saveCo2Config() {
    //Перевірка, чи є зміни у полі "Завдання"
    var currentCO2Target = parseFloat($('#CO2-in-C2H2-y1-target').val().trim());
    if (isNaN(currentCO2Target)) {
        alert('Неправильне значення! Напр: 0.06');
        return;
    }

    //Змін нема
    if (previousCO2Target == currentCO2Target) return;

    //Є зміни у полі "Завдання" - відсилаємо нове значення для запису у базу
    $.ajax({
	url: baseUrl+"/api/updateDeviceConfig/"+$('#deviceId').val(),
	type: "POST",
	data: {'CO2-in-C2H2-y1-target':currentCO2Target},
	success: function(response) {
            console.log(response);

            //Після запису вважаємо, що змін нема
            previousCO2Target = currentCO2Target;
            if (previousCO2Target == currentCO2Target) {
                $('#saveCo2Config').addClass('disabled');
                $('#saveCo2Config').removeClass('btn-success');
                $('#saveCo2Config').addClass('btn-default');
                return;
            }
	},
        error: function(response) {
            console.log(response);
            alert('Сталася помилка, спробуйте пізніше!');
        }
    });
}