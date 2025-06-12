document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("reset-password-form");
  const submitBtn = document.getElementById("submit-btn");
  const messageEl = document.getElementById("message");
  const errorEl = document.getElementById("error");
  const newPasswordInput = document.getElementById("new-password");
  const confirmPasswordInput = document.getElementById("confirm-password");
  const inputGroups = document.querySelectorAll(".input-group");

  // Get token and email from URL
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get("token");
  const email = urlParams.get("email");

  if (!token && !email) {
    errorEl.textContent =
      "Invalid reset link. Please request a new password reset.";
    errorEl.style.display = "block";
    form.style.display = "none";
    return;
  }

  // Real-time password matching check
  [newPasswordInput, confirmPasswordInput].forEach((input) => {
    input.addEventListener("input", () => {
      const newPassword = newPasswordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      if (newPassword && confirmPassword && newPassword !== confirmPassword) {
        inputGroups.forEach((group) => group.classList.add("error"));
      } else {
        inputGroups.forEach((group) => group.classList.remove("error"));
      }
    });
  });

  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    // Clear previous messages
    messageEl.style.display = "none";
    errorEl.style.display = "none";
    inputGroups.forEach((group) => group.classList.remove("error"));

    // Validation checks
    if (!newPassword || !confirmPassword) {
      errorEl.textContent = "Please fill in both password fields";
      errorEl.style.display = "block";
      return;
    }

    if (newPassword.length < 8) {
      errorEl.textContent = "Password must be at least 8 characters";
      errorEl.style.display = "block";
      inputGroups.forEach((group) => group.classList.add("error"));
      return;
    }

    if (newPassword !== confirmPassword) {
      errorEl.textContent = "Passwords do not match!";
      errorEl.style.display = "block";
      inputGroups.forEach((group) => group.classList.add("error"));
      return;
    }

    // Disable button during processing
    submitBtn.disabled = true;
    submitBtn.textContent = "Processing...";

    try {
      const response = await fetch(
        "http://127.0.0.1/xneelo-iliso/reset_password.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ token, newPassword }),
        }
      );

      const data = await response.json();

      if (response.ok) {
        messageEl.textContent = data.message || "Password reset successfully!";
        messageEl.style.display = "block";

        setTimeout(() => {
          window.location.href = 'ResetPassword.html?email=' + encodeURIComponent(email);
        }, 2000);
      } else {
        throw new Error(data.error || "Failed to reset password");
      }
    } catch (err) {
      errorEl.textContent =
        err.message || "Failed to reset password. Please try again.";
      errorEl.style.display = "block";
      console.error(err);
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Reset Password";
    }
  });
});
