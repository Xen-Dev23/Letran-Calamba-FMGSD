const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');

togglePassword.addEventListener('click', function() {
// Toggle the type attribute
const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
password.setAttribute('type', type);
            
// Toggle the eye icon
this.src = type === 'password' ? 'assets/icons/eye-off.png' : 'assets/icons/eye-on.png';
});
