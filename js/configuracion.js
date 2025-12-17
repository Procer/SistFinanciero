$(document).ready(function() {

    // Función para verificar y mostrar el estado de la Gemini API Key
    function checkGeminiApiKeyStatus() {
        $.ajax({
            url: 'php/api/config/get_gemini_key_status.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                const statusDiv = $('#geminiApiKeyStatus');
                if (response.status === 'success') {
                    if (response.configured) {
                        statusDiv.removeClass('alert-info alert-danger').addClass('alert-success');
                        statusDiv.html('<i class="fas fa-check-circle me-2"></i>Estado: Gemini API Key configurada correctamente.');
                    } else {
                        statusDiv.removeClass('alert-success alert-danger').addClass('alert-info');
                        statusDiv.html('<i class="fas fa-exclamation-triangle me-2"></i>Estado: Gemini API Key no configurada o vacía.');
                    }
                } else {
                    statusDiv.removeClass('alert-info alert-success').addClass('alert-danger');
                    statusDiv.html('<i class="fas fa-times-circle me-2"></i>Error al verificar el estado: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                const statusDiv = $('#geminiApiKeyStatus');
                statusDiv.removeClass('alert-info alert-success').addClass('alert-danger');
                statusDiv.html('<i class="fas fa-times-circle me-2"></i>Error de comunicación al verificar el estado.');
                console.error('Error al verificar el estado de la API Key:', textStatus, errorThrown, jqXHR.responseJSON);
            }
        });
    }

    // Manejar el envío del formulario para actualizar la Gemini API Key
    $('#formGeminiApiKey').submit(function(e) {
        e.preventDefault();

        const newApiKey = $('#geminiApiKey').val();
        const feedbackDiv = $('#geminiApiKeyFeedback');
        feedbackDiv.empty();

        if (newApiKey.trim() === '') {
            feedbackDiv.html('<div class="alert alert-warning">Por favor, ingrese una clave de API válida.</div>');
            return;
        }

        $.ajax({
            url: 'php/api/config/update_gemini_key.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ api_key: newApiKey }),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    feedbackDiv.html('<div class="alert alert-success">' + response.message + '</div>');
                    $('#geminiApiKey').val(''); // Limpiar el campo después de guardar
                    checkGeminiApiKeyStatus(); // Actualizar el estado
                } else {
                    feedbackDiv.html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let errorMsg = 'Error al procesar la solicitud.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = jqXHR.responseJSON.message;
                }
                feedbackDiv.html('<div class="alert alert-danger">' + errorMsg + '</div>');
                console.error('Error en la petición AJAX:', textStatus, errorThrown, jqXHR.responseJSON);
            }
        });
    });

    // Cargar el estado de la API Key al iniciar la página
    checkGeminiApiKeyStatus();

});
