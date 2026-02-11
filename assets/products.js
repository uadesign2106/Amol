const defaultProducts = [
  {
    id: crypto.randomUUID(),
    name: 'BioShield Neem Concentrate',
    category: 'Pest Control',
    description: 'Natural neem-based concentrate for sucking and chewing pests.',
    price: '₹1,250',
    mrp: '₹1,480'
  },
  {
    id: crypto.randomUUID(),
    name: 'SoilBoost Micronutrient Mix',
    category: 'Fertilizer',
    description: 'Balanced micronutrient blend for flowering and fruit setting.',
    price: '₹980',
    mrp: '₹1,100'
  },
  {
    id: crypto.randomUUID(),
    name: 'RodentGuard Bait Station',
    category: 'Pest Control',
    description: 'Heavy-duty station for farms, warehouses and food units.',
    price: '₹450',
    mrp: '₹520'
  },
  {
    id: crypto.randomUUID(),
    name: 'ProGrow NPK 19:19:19',
    category: 'Fertilizer',
    description: 'Water-soluble NPK for vegetative and reproductive growth support.',
    price: '₹790',
    mrp: '₹880'
  }
];

const managedUser = {
  username: 'manager',
  password: 'Agri@123',
  permissions: { add: true, edit: true, delete: true }
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
  localStorage.setItem(sessionKey, JSON.stringify({ username: managedUser.username, permissions: managedUser.permissions }));
}

function clearSession() {
  localStorage.removeItem(sessionKey);
}

function renderCatalog(query = '') {
  const products = getProducts();
  const list = document.getElementById('productCatalog');
  if (!list) {
    return;
  }

  const q = query.trim().toLowerCase();
  const filtered = q
    ? products.filter((item) => item.name.toLowerCase().includes(q) || item.category.toLowerCase().includes(q))
    : products;

  list.innerHTML = '';

  filtered.forEach((item) => {
    const card = document.createElement('article');
    card.className = 'card product-card';
    card.innerHTML = `
      <span class="badge">${item.category}</span>
      <h3>${item.name}</h3>
      <p>${item.description}</p>
      <p><span class="price">${item.price}</span> <span class="mrp">${item.mrp}</span></p>
      <button class="btn" type="button">Add to Cart</button>
    `;
    list.appendChild(card);
  });

  if (!filtered.length) {
    list.innerHTML = '<article class="card"><p>No products found for your search.</p></article>';
  }

  renderAdminTable(products);
}

function renderAdminTable(products) {
  const tbody = document.getElementById('adminProductsBody');
  if (!tbody) return;

  const session = getSession();
  tbody.innerHTML = '';

  products.forEach((item) => {
    const tr = document.createElement('tr');
    const actions = session
      ? `<button class="btn" data-action="edit" data-id="${item.id}" type="button">Edit</button>
         <button class="btn danger" data-action="delete" data-id="${item.id}" type="button">Delete</button>`
      : 'Login required';

    tr.innerHTML = `
      <td>${item.name}</td>
      <td>${item.category}</td>
      <td>${item.price}</td>
      <td>${item.mrp}</td>
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

  if (!form || !info || !panel || !logoutBtn) {
    return;
  }

  function syncState() {
    const session = getSession();
    if (session) {
      info.textContent = `Logged in as ${session.username}. Permissions: add=${session.permissions.add}, edit=${session.permissions.edit}, delete=${session.permissions.delete}`;
      panel.hidden = false;
      logoutBtn.hidden = false;
      form.hidden = true;
    } else {
      info.textContent = 'Please login as manager to access add/edit/delete controls.';
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
      renderCatalog(document.getElementById('catalogSearch')?.value || '');
      form.reset();
      return;
    }

    alert('Invalid username or password.');
  });

  logoutBtn.addEventListener('click', () => {
    clearSession();
    syncState();
    renderCatalog(document.getElementById('catalogSearch')?.value || '');
  });

  syncState();
}

function setupProductManagement() {
  const form = document.getElementById('productForm');
  const tbody = document.getElementById('adminProductsBody');
  const hiddenId = document.getElementById('productId');

  if (!form || !tbody || !hiddenId) {
    return;
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const session = getSession();

    if (!session) {
      alert('Login required.');
      return;
    }

    if (!session.permissions.add && !hiddenId.value) {
      alert('You do not have add permission.');
      return;
    }

    if (!session.permissions.edit && hiddenId.value) {
      alert('You do not have edit permission.');
      return;
    }

    const products = getProducts();
    const product = {
      id: hiddenId.value || crypto.randomUUID(),
      name: document.getElementById('productName').value.trim(),
      category: document.getElementById('productCategory').value.trim(),
      description: document.getElementById('productDescription').value.trim(),
      price: document.getElementById('productPrice').value.trim(),
      mrp: document.getElementById('productMrp').value.trim()
    };

    const existingIndex = products.findIndex((item) => item.id === product.id);
    if (existingIndex >= 0) {
      products[existingIndex] = product;
    } else {
      products.push(product);
    }

    saveProducts(products);
    form.reset();
    hiddenId.value = '';
    renderCatalog(document.getElementById('catalogSearch')?.value || '');
  });

  tbody.addEventListener('click', (event) => {
    const btn = event.target.closest('button[data-action]');
    if (!btn) return;

    const session = getSession();
    if (!session) {
      alert('Login required.');
      return;
    }

    const action = btn.dataset.action;
    const id = btn.dataset.id;
    const products = getProducts();
    const selected = products.find((item) => item.id === id);
    if (!selected) return;

    if (action === 'edit') {
      if (!session.permissions.edit) {
        alert('You do not have edit permission.');
        return;
      }
      hiddenId.value = selected.id;
      document.getElementById('productName').value = selected.name;
      document.getElementById('productCategory').value = selected.category;
      document.getElementById('productDescription').value = selected.description;
      document.getElementById('productPrice').value = selected.price;
      document.getElementById('productMrp').value = selected.mrp;
    }

    if (action === 'delete') {
      if (!session.permissions.delete) {
        alert('You do not have delete permission.');
        return;
      }
      const updated = products.filter((item) => item.id !== id);
      saveProducts(updated);
      renderCatalog(document.getElementById('catalogSearch')?.value || '');
    }
  });
}

function setupSearch() {
  const search = document.getElementById('catalogSearch');
  if (!search) return;

  search.addEventListener('input', () => {
    renderCatalog(search.value);
  });
}

renderCatalog();
setupSearch();
setupLogin();
setupProductManagement();
