var emulationInterval = null;
$(document).ready(function(){
    if (!devMode) {
        emulationInterval = setInterval(function(){ emulateDevice() }, 2*1000); //кожні 2 секунд
    }
});

function emulateDevice() {
    $.ajax({
	url: baseUrl+"/api/emulateDevice/"+$('#deviceId').val(),
	type: "POST",
	data: {},
	success: function(response) {
            //console.log('emulateDevice:',response);
	},
        error: function(response) {
            //console.log('emulateDevice(): Сталася помилка, спробуйте пізніше!');
        }
    });
}

/* ------------------------- */
function stopEmulation() {
    clearInterval(emulationInterval);
}