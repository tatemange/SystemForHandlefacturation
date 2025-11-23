// assets/js/admin.js

document.addEventListener('DOMContentLoaded', () => {

    // --- GESTION DU LOGIN ---
    const loginForm = document.querySelector('form.login');

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            submitBtn.textContent = "Chargement...";
            submitBtn.disabled = true;

            // Récupération des données via FormData pour gérer facilement les champs
            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            // Ajout de l'action pour l'API
            data.action = 'login';

            try {
                const response = await fetch('./assets/api/admin_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    // result.redirect contiendra 'dashboard.php' grâce au contrôleur
                    window.location.href = result.redirect || './dashboard.php';
                } else {
                    alert("Erreur : " + result.message);
                }

            } catch (error) {
                console.error('Erreur:', error);
                alert("Une erreur technique est survenue.");
            } finally {
                submitBtn.textContent = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    }

    // --- GESTION DE L'INSCRIPTION (REGISTER) ---
    const registerForm = document.querySelector('form.sign-in');

    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            submitBtn.textContent = "Traitement...";
            submitBtn.disabled = true;

            const formData = new FormData(registerForm);
            const data = Object.fromEntries(formData.entries());

            data.action = 'register';

            try {
                const response = await fetch('./assets/api/admin_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                // Pour déboguer si le JSON est mal formé (souvent à cause des echo PHP)
                // const text = await response.text();
                // console.log(text);
                // const result = JSON.parse(text);

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    registerForm.reset();
                    // Basculer vers le formulaire de connexion (si ton fichier form.js le gère déjà, sinon :)
                    document.querySelector('.btn.connecter').click();
                } else {
                    alert("Erreur : " + result.message);
                }

            } catch (error) {
                console.error('Erreur:', error);
                alert("Une erreur technique est survenue lors de l'inscription.");
            } finally {
                submitBtn.textContent = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    }
});