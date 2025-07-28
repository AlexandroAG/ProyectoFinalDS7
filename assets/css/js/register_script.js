// register_script.js

document.addEventListener('DOMContentLoaded', function() {
    console.log("register_script.js: Script cargado y el DOM está listo."); // Confirma que el script se carga

    const userImageInput = document.getElementById('user_image');
    const userImagePreview = document.getElementById('userImagePreview');
    const defaultUserIcon = document.getElementById('defaultUserIcon');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const registerForm = document.querySelector('.right-panel form');
    const cedulaInput = document.getElementById('cedula');
    const cedulaError = document.getElementById('cedulaError');

    // *VERIFICACIÓN DE ELEMENTOS HTML*
    console.log("Elementos HTML encontrados:");
    console.log("userImageInput:", userImageInput);
    console.log("userImagePreview:", userImagePreview);
    console.log("defaultUserIcon:", defaultUserIcon);
    console.log("passwordInput:", passwordInput);
    console.log("confirmPasswordInput:", confirmPasswordInput);
    console.log("registerForm:", registerForm);
    console.log("cedulaInput:", cedulaInput);
    console.log("cedulaError:", cedulaError);

    // Función para previsualizar la imagen
    if (userImageInput) {
        console.log("register_script.js: 'user_image' input encontrado. Añadiendo event listener.");
        userImageInput.addEventListener('change', function(event) {
            console.log("register_script.js: Evento 'change' disparado en 'user_image'.");
            const file = event.target.files[0];
            if (file) {
                console.log("register_script.js: Archivo seleccionado:", file.name, "Tipo:", file.type, "Tamaño:", file.size, "bytes.");
                const reader = new FileReader();
                reader.onload = function(e) {
                    console.log("register_script.js: FileReader cargó el archivo. Actualizando vista previa.");
                    userImagePreview.src = e.target.result;
                    userImagePreview.style.display = 'block';
                    if (defaultUserIcon) { // Verificación adicional si defaultUserIcon existe
                        defaultUserIcon.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            } else {
                console.log("register_script.js: No se seleccionó ningún archivo.");
                userImagePreview.src = '';
                userImagePreview.style.display = 'none';
                if (defaultUserIcon) { // Verificación adicional si defaultUserIcon existe
                    defaultUserIcon.style.display = 'block';
                }
            }
        });
    } else {
        console.log("register_script.js: ADVERTENCIA: 'user_image' input NO encontrado.");
    }

    // Validación de confirmación de contraseña y cédula
    if (registerForm) {
        console.log("register_script.js: Formulario de registro encontrado. Añadiendo event listener para 'submit'.");
        registerForm.addEventListener('submit', function(event) {
            console.log("register_script.js: Evento 'submit' del formulario disparado.");
            let isValid = true;

            // Validación de contraseñas
            if (passwordInput && confirmPasswordInput) { // Asegúrate de que los inputs de contraseña existan
                if (passwordInput.value !== confirmPasswordInput.value) {
                    console.log("register_script.js: ERROR - Las contraseñas no coinciden.");
                    alert('Las contraseñas no coinciden. Por favor, inténtalo de nuevo.');
                    confirmPasswordInput.focus();
                    isValid = false;
                }
            } else {
                 console.log("register_script.js: ADVERTENCIA: Inputs de contraseña NO encontrados o incompletos.");
            }


            // Validación de cédula con JavaScript (adicional al pattern de HTML5)
            if (cedulaInput) { // Asegúrate de que el input de cédula exista
                const cedulaPattern = /^\d-\d{4}-\d{4}$/;
                if (!cedulaPattern.test(cedulaInput.value)) {
                    console.log("register_script.js: ERROR - Formato de cédula incorrecto.");
                    if (cedulaError) { // Asegúrate de que el elemento de error exista
                        cedulaError.style.display = 'block';
                    }
                    cedulaInput.focus();
                    isValid = false;
                } else {
                    console.log("register_script.js: Formato de cédula correcto.");
                    if (cedulaError) {
                        cedulaError.style.display = 'none';
                    }
                }
            } else {
                console.log("register_script.js: ADVERTENCIA: 'cedula' input NO encontrado.");
            }


            if (!isValid) {
                event.preventDefault(); // Evita que el formulario se envíe si hay errores
                console.log("register_script.js: Envío del formulario PREVENIDO debido a errores de validación.");
            } else {
                console.log("register_script.js: Formulario VÁLIDO. El envío continuará.");
            }
        });

        // Ocultar mensaje de error de cédula al escribir
        if (cedulaInput) {
            cedulaInput.addEventListener('input', function() {
                const cedulaPattern = /^\d-\d{4}-\d{4}$/;
                if (cedulaPattern.test(this.value)) {
                    if (cedulaError) {
                        cedulaError.style.display = 'none';
                    }
                }
            });
        }
    } else {
        console.log("register_script.js: ADVERTENCIA: Formulario de registro NO encontrado.");
    }
});