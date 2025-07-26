// register_script.js

document.addEventListener('DOMContentLoaded', function() {
    console.log("register_script.js cargado y ejecutado."); // Mensaje de depuración para confirmar que se carga

    const userImageInput = document.getElementById('user_image');
    const userImagePreview = document.getElementById('userImagePreview');
    const defaultUserIcon = document.getElementById('defaultUserIcon');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const registerForm = document.querySelector('.right-panel form');
    const cedulaInput = document.getElementById('cedula');
    const cedulaError = document.getElementById('cedulaError');

    // Función para previsualizar la imagen
    if (userImageInput) {
        userImageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    userImagePreview.src = e.target.result;
                    userImagePreview.style.display = 'block';
                    defaultUserIcon.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                userImagePreview.src = '';
                userImagePreview.style.display = 'none';
                defaultUserIcon.style.display = 'block';
            }
        });
    }

    // Validación de confirmación de contraseña y cédula
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            let isValid = true;

            // Validación de contraseñas
            if (passwordInput.value !== confirmPasswordInput.value) {
                alert('Las contraseñas no coinciden. Por favor, inténtalo de nuevo.');
                confirmPasswordInput.focus();
                isValid = false;
            }

            // Validación de cédula con JavaScript (adicional al pattern de HTML5)
            const cedulaPattern = /^\d-\d{4}-\d{4}$/;
            if (!cedulaPattern.test(cedulaInput.value)) {
                cedulaError.style.display = 'block';
                cedulaInput.focus();
                isValid = false;
            } else {
                cedulaError.style.display = 'none';
            }

            if (!isValid) {
                event.preventDefault(); // Evita que el formulario se envíe si hay errores
            }
        });

        // Ocultar mensaje de error de cédula al escribir
        if (cedulaInput) {
            cedulaInput.addEventListener('input', function() {
                const cedulaPattern = /^\d-\d{4}-\d{4}$/;
                if (cedulaPattern.test(this.value)) {
                    cedulaError.style.display = 'none';
                }
            });
        }
    }
});
