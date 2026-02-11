const defaultProducts = [
  {
    id: crypto.randomUUID(),
    name: 'BioShield Neem Concentrate',
    category: 'Pest Control',
    description: 'Natural neem-based concentrate for chewing and sucking pests.',
    price: '₹1,250 / L'
  },
  {
    id: crypto.randomUUID(),
    name: 'SoilBoost Micronutrient Mix',
    category: 'Agri Input',
    description: 'Balanced micronutrients for improved flowering and fruiting.',
    price: '₹980 / 5kg'
  },
  {
    id: crypto.randomUUID(),
    name: 'RodentGuard Bait Station',
    category: 'Pest Control',
    description: 'Durable bait station for warehouses and farm boundaries.',
    price: '₹450 / Unit'
  }
];

const managedUser = {
  username: 'manager',
  password: 'Agri@123',
  permissions: {
    add: true,
    edit: true,
    delete: true
  }
};

const storageKey = 'agreeculture_products';
const sessionKey = 'agreeculture_manager_session';

function getProducts() {
  const saved = localStorage.getItem(storageKey);
  if (!saved) {
    localStorage.setItem(storageKey, JSON.stringify(defaultProducts));
    return [...defaultProducts];
  }
  return JSON.parse(saved);
}

function saveProducts(products) {
  localStorage.setItem(storageKey, JSON.stringify(products));
}

function getSession() {
  const saved = localStorage.getItem(sessionKey);
  return saved ? JSON.parse(saved) : null;
}

function setSession() {
  localStorage.setItem(sessionKey, JSON.stringify({
    username: managedUser.username,
    permissions: managedUser.permissions
  }));
}

function clearSession() {
  localStorage.removeItem(sessionKey);
}

function renderCatalog() {
  const products = getProducts();
  const list = document.getElementById('productCatalog');
  list.innerHTML = '';

  products.forEach((item) => {
    const card = document.createElement('article');
    card.className = 'card';
    card.innerHTML = `
      <h3>${item.name}</h3>
      <p><strong>Category:</strong> ${item.category}</p>
      <p>${item.description}</p>
      <p><strong>Price:</strong> ${item.price}</p>
    `;
    list.appendChild(card);
  });

  renderAdminTable(products);
}

function renderAdminTable(products) {
  const tbody = document.getElementById('adminProductsBody');
  const session = getSession();
  tbody.innerHTML = '';

  products.forEach((item) => {
    const tr = document.createElement('tr');
    const actions = session ? `
      <button class="btn" data-action="edit" data-id="${item.id}">Edit</button>
      <button class="btn secondary" data-action="delete" data-id="${item.id}">Delete</button>
    ` : 'Login required';

    tr.innerHTML = `
      <td>${item.name}</td>
      <td>${item.category}</td>
      <td>${item.price}</td>
      <td>${actions}</td>
    `;
    tbody.appendChild(tr);
  });
}

function setupLogin() {
  const form = document.getElementById('loginForm');
  const info = document.getElementById('loginInfo');
  const panel = document.getElementById('adminPanel');
  const logoutBtn = document.getElementById('logoutBtn');

  function syncState() {
    const session = getSession();
    if (session) {
      info.textContent = `Logged in as ${session.username}. Permissions: add=${session.permissions.add}, edit=${session.permissions.edit}, delete=${session.permissions.delete}`;
      panel.hidden = false;
      logoutBtn.hidden = false;
      form.hidden = true;
    } else {
      info.textContent = 'Demo manager user available below to manage products.';
      panel.hidden = true;
      logoutBtn.hidden = true;
      form.hidden = false;
    }
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    if (username === managedUser.username && password === managedUser.password) {
      setSession();
      syncState();
      renderCatalog();
      form.reset();
      return;
    }

    alert('Invalid username or password.');
  });

  logoutBtn.addEventListener('click', () => {
    clearSession();
    syncState();
    renderCatalog();
  });

  syncState();
}

function setupProductManagement() {
  const form = document.getElementById('productForm');
  const tbody = document.getElementById('adminProductsBody');
  const hiddenId = document.getElementById('productId');

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const session = getSession();
    if (!session?.permissions.add && !hiddenId.value) {
      alert('You do not have add permission.');
      return;
    }
    if (!session?.permissions.edit && hiddenId.value) {
      alert('You do not have edit permission.');
      return;
    }

    const products = getProducts();
    const product = {
      id: hiddenId.value || crypto.randomUUID(),
      name: document.getElementById('productName').value.trim(),
      category: document.getElementById('productCategory').value.trim(),
      description: document.getElementById('productDescription').value.trim(),
      price: document.getElementById('productPrice').value.trim()
    };

    const index = products.findIndex((item) => item.id === product.id);
    if (index >= 0) {
      products[index] = product;
    } else {
      products.push(product);
    }

    saveProducts(products);
    form.reset();
    hiddenId.value = '';
    renderCatalog();
  });

  tbody.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-action]');
    if (!button) return;

    const session = getSession();
    const action = button.dataset.action;
    const id = button.dataset.id;
    const products = getProducts();
    const selected = products.find((item) => item.id === id);

    if (action === 'edit') {
      if (!session?.permissions.edit) {
        alert('You do not have edit permission.');
        return;
      }
      document.getElementById('productId').value = selected.id;
      document.getElementById('productName').value = selected.name;
      document.getElementById('productCategory').value = selected.category;
      document.getElementById('productDescription').value = selected.description;
      document.getElementById('productPrice').value = selected.price;
      return;
    }

    if (action === 'delete') {
      if (!session?.permissions.delete) {
        alert('You do not have delete permission.');
        return;
      }
      const filtered = products.filter((item) => item.id !== id);
      saveProducts(filtered);
      renderCatalog();
    }
  });
}

renderCatalog();
setupLogin();
setupProductManagement();
