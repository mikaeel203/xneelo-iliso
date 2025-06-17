<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', 'Open Sans', sans-serif;
      background-color: #F5F5F5;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .reset-password {
      display: flex;
      justify-content: center;
      margin: 20px auto;
      flex-direction: column;
      align-items: center;
      text-align: center;
      color: #000000;
      background-color: #FFFFFF;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
      width: 100%;
      max-width: 440px;
    }
    h2 {
      margin: 20px 0;
      font-weight: 600;
    }
    .inspirational-text {
      color: grey;
      margin: 0 0 20px;
    }
    .input-group {
      width: 100%;
      margin-bottom: 20px;
    }
    .input-group input {
      width: 100%;
      padding: 15px 15px 15px 40px;
      border: 1px solid #ccc;
      border-radius: 7px;
      font-size: 16px;
      box-sizing: border-box;
    }
    .button {
      width: 200px;
      height: 46px;
      cursor: pointer;
      background-color: #0F4392;
      color: #fff;
      border: none;
      border-radius: 12px;
      font-weight: bold;
      font-size: 16px;
      transition: background-color 0.3s ease, color 0.3s ease;
      margin-top: 10px;
    }
    .button:hover {
      background-color: #7DC5F8;
      color: #000;
    }
    .button[disabled] {
      opacity: 0.6;
      cursor: not-allowed;
    }
    .message {
      color: green;
      margin-top: 15px;
      display: none;
    }
    .error {
      color: red;
      margin-top: 15px;
      display: none;
    }
    img {
      width: 70px;
      height: 50px;
      object-fit: contain;
    }
    .redirect-login {
      text-align: center;
      margin-top: 1rem;
      font-size: 16px;
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      color: #6B7280;
    }
    .redirect-login a {
      color: #000000;
      text-decoration: none;
      transition: color 0.3s ease;
      cursor: pointer;
    }
    .redirect-login a:hover,
    .redirect-login a:focus {
      color: #0F4392;
      text-decoration: underline;
      outline: none;
    }
    .input-group.error input {
      border-color: #FF4444;
    }
  </style>
</head>
<body>
  <div class="reset-password">
    <img src="https://raw.githubusercontent.com/luthandodake10111/iliso--frontend-images-/main/LC%20logo.png" alt="App Logo" />
    <h2>Reset Password</h2>
    <form id="reset-password-form">
      <p class="inspirational-text">
        Youth is 37% of South Africa... but 100% of its future
      </p>
      <div class="input-group">
        <input
          type="password"
          id="new-password"
          placeholder=":key: New password"
          required
        >
      </div>
      <div class="input-group">
        <input
          type="password"
          id="confirm-password"
          placeholder=":key: Confirm password"
          required
        >
      </div>
      <button type="submit" id="submit-btn" class="button">Reset Password</button>
    </form>
    <p id="message" class="message"></p>
    <p id="error" class="error"></p>
    <p class="redirect-login">
      <a href="Login.html">← Back to Login</a>
    </p>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const form = document.getElementById("reset-password-form");
      const submitBtn = document.getElementById("submit-btn");
      const messageEl = document.getElementById("message");
      const errorEl = document.getElementById("error");
      const newPasswordInput = document.getElementById("new-password");
      const confirmPasswordInput = document.getElementById("confirm-password");
      const inputGroups = document.querySelectorAll(".input-group");
      const urlParams = new URLSearchParams(window.location.search);
      const token = urlParams.get("token");
      const email = urlParams.get("email");
      if (!token || !email) {
        errorEl.textContent = "Invalid reset link. Please request a new password reset.";
        errorEl.style.display = "block";
        form.style.display = "none";
        return;
      }
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
        messageEl.style.display = "none";
        errorEl.style.display = "none";
        if (newPassword !== confirmPassword) {
          errorEl.textContent = "Passwords do not match.";
          errorEl.style.display = "block";
          return;
        }
        try {
          // Replace with your real backend endpoint
          const response = await fetch("https://your-backend-api.com/reset-password", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              email,
              token,
              newPassword,
            }),
          });
          const result = await response.json();
          if (response.ok) {
            messageEl.textContent = result.message || "Password reset successful!";
            messageEl.style.display = "block";
            form.reset();
          } else {
            errorEl.textContent = result.error || "Something went wrong. Please try again.";
            errorEl.style.display = "block";
          }
        } catch (error) {
          errorEl.textContent = "Network error. Please try again later.";
          errorEl.style.display = "block";
        }
      });
    });
  </script>
</body>
</html>