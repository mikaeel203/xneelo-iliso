</html><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap" rel="stylesheet">
  <style>
    /* Base styling */
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
    .forgot-password {
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
      position: relative;
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
      background-color: #0F4392;
    }
    .message {
      color: #4CAF50;
      margin-top: 15px;
      display: none;
    }
    .error {
      color: #FF4444;
      margin-top: 15px;
      display: none;
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
  </style>
</head>
<body>
  <div class="forgot-password">
    <img src="https://raw.githubusercontent.com/luthandodake10111/iliso--frontend-images-/main/LC%20logo.png" alt="Logo" />
    <h2>Forgot your password?</h2>
    <p class="inspirational-text">Don’t worry — we’ll get you sorted in no time.</p>
    <form id="forgot-password-form">
      <div class="input-group">
        <input type="email" id="email" placeholder="Enter your email" required />
      </div>
      <button type="submit" id="submit-btn" class="button">Send reset link</button>
    </form>
    <p id="message" class="message"></p>
    <p id="error" class="error"></p>
    <div class="redirect-login">
      <a href="login.html">Back to Login</a>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('forgot-password-form');
      const submitBtn = document.getElementById('submit-btn');
      const messageEl = document.getElementById('message');
      const errorEl = document.getElementById('error');
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('email').value.trim();
        if (!email) {
          errorEl.textContent = 'Please enter your email address.';
          errorEl.style.display = 'block';
          return;
        }
        submitBtn.disabled = true;
        submitBtn.textContent = 'Resetting...';
        messageEl.style.display = 'none';
        errorEl.style.display = 'none';
        try {
          const response = await fetch('http://127.0.0.1/xneelo-iliso/request_password_reset.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email }),
          });
          const data = await response.json();
          if (response.ok) {
            messageEl.textContent = data.message || 'Password reset link sent to your email! Please check your inbox.';
            messageEl.style.display = 'block';
          } else {
            throw new Error(data.error || 'Failed to send reset link');
          }
        } catch (err) {
          errorEl.textContent = err.message || 'Something went wrong. Please try again.';
          errorEl.style.display = 'block';
          console.error(err);
        } finally {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Send reset link';
        }
      });
    });
  </script>
</body>
</html>