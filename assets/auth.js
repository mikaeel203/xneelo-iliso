// auth.js

function checkAuth() {
  const token = localStorage.getItem('token');

  if (!token) {
    alert('You must log in first');
    window.location.href = 'https://atracker.lcstudio-incubate.co.za/';
    return;
  }

  fetch('https://atracker.lcstudio-incubate.co.za/public_html/backend/auth.php', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + token
    }
  })
  .then(res => {
    if (!res.ok) {
      throw new Error('Invalid token');
    }
     return res.json();
  })
   .then(data => {
    console.log('Token is valid:', data);
  
  })
  .catch(() => {
    alert('Session expired or invalid. Please log in again.');
    localStorage.removeItem('token');
    window.location.href = 'https://atracker.lcstudio-incubate.co.za/';
  });
}
