document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('forgot-password-form');
  const submitBtn = document.getElementById('submit-btn');
  const messageEl = document.getElementById('message');
  const errorEl = document.getElementById('error');

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    
    // Optional: simple empty check
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
  messageEl.textContent = data.message || 'Password reset link sent to your email! Please check your email to reset your password.';
  messageEl.style.display = 'block';

  // Do NOT redirect automatically here.
} else {
  throw new Error(data.error || 'Failed to send reset link');
}

    } catch (err) {
      errorEl.textContent = err.message || 'Failed to send reset link. Please try again.';
      errorEl.style.display = 'block';
      console.error(err);
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Send reset link';
    }
  });
});
