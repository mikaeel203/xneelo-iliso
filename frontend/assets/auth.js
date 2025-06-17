// auth.js

function checkAuth() {
  const token = localStorage.getItem('token');

  if (!token) {
    alert('You must log in first');
    window.location.href = 'login.html';
    return;
  }

  fetch('http://localhost/htdocs/Backend/validate-token.php', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + token
    }
  })
  .then(res => {
    if (!res.ok) {
      throw new Error('Invalid token');
    }
  })
  .catch(() => {
    alert('Session expired or invalid. Please log in again.');
    localStorage.removeItem('token');
    window.location.href = 'login.html';
  });
}
