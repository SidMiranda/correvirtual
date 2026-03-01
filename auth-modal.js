const modal = document.getElementById("authModal");

function openLogin() {
  modal.style.display = "flex";
  showLogin();
}

function closeModal() {
  modal.style.display = "none";
}

function showLogin() {
  document.getElementById("loginForm").classList.remove("hidden");
  document.getElementById("registerForm").classList.add("hidden");
  document.getElementById("resetForm").classList.add("hidden");
}

function showRegister() {
  document.getElementById("loginForm").classList.add("hidden");
  document.getElementById("registerForm").classList.remove("hidden");
  document.getElementById("resetForm").classList.add("hidden");
}

function showReset() {
  document.getElementById("loginForm").classList.add("hidden");
  document.getElementById("registerForm").classList.add("hidden");
  document.getElementById("resetForm").classList.remove("hidden");
}

/* fechar clicando fora */
window.onclick = function(e) {
  if (e.target === modal) {
    closeModal();
  }
};