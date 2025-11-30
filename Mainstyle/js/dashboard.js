// Sample data for the dashboard
const sampleData = {
    products: [
        {
            id: 1001,
            name: "Premium Wireless Headphones",
            category: "Electronics",
            price: 199.99,
            stock: 42,
            status: "active",
            description: "High-quality wireless headphones with noise cancellation."
        },
        {
            id: 1002,
            name: "Organic Cotton T-Shirt",
            category: "Apparel",
            price: 29.99,
            stock: 0,
            status: "out_of_stock",
            description: "Comfortable organic cotton t-shirt in various colors."
        },
        {
            id: 1003,
            name: "Stainless Steel Water Bottle",
            category: "Accessories",
            price: 24.95,
            stock: 5,
            status: "active",
            description: "Durable stainless steel water bottle with insulation."
        },
        {
            id: 1004,
            name: "Bluetooth Speaker",
            category: "Electronics",
            price: 89.99,
            stock: 17,
            status: "active",
            description: "Portable Bluetooth speaker with excellent sound quality."
        },
        {
            id: 1005,
            name: "Yoga Mat",
            category: "Fitness",
            price: 39.99,
            stock: 28,
            status: "active",
            description: "Non-slip yoga mat with carrying strap."
        }
    ],
    orders: [
        {
            id: "ORD-2023-1001",
            customer: "John Smith",
            date: "2023-06-15",
            amount: 199.99,
            status: "completed",
            products: [
                { id: 1001, name: "Premium Wireless Headphones", price: 199.99, quantity: 1 }
            ]
        },
        {
            id: "ORD-2023-1002",
            customer: "Sarah Johnson",
            date: "2023-06-16",
            amount: 89.99,
            status: "processing",
            products: [
                { id: 1004, name: "Bluetooth Speaker", price: 89.99, quantity: 1 }
            ]
        },
        {
            id: "ORD-2023-1003",
            customer: "Michael Brown",
            date: "2023-06-17",
            amount: 149.98,
            status: "pending",
            products: [
                { id: 1005, name: "Yoga Mat", price: 39.99, quantity: 2 },
                { id: 1003, name: "Stainless Steel Water Bottle", price: 24.95, quantity: 1 }
            ]
        },
        {
            id: "ORD-2023-1004",
            customer: "Emily Davis",
            date: "2023-06-18",
            amount: 59.98,
            status: "completed",
            products: [
                { id: 1002, name: "Organic Cotton T-Shirt", price: 29.99, quantity: 2 }
            ]
        },
        {
            id: "ORD-2023-1005",
            customer: "Robert Wilson",
            date: "2023-06-19",
            amount: 24.95,
            status: "cancelled",
            products: [
                { id: 1003, name: "Stainless Steel Water Bottle", price: 24.95, quantity: 1 }
            ]
        }
    ],
    activity: [
        {
            id: 1,
            title: "New order received",
            description: "Order #ORD-2023-1005 from Robert Wilson",
            time: "10 minutes ago",
            icon: "shopping-cart"
        },
        {
            id: 2,
            title: "Product updated",
            description: "Premium Wireless Headphones stock updated",
            time: "1 hour ago",
            icon: "box-open"
        },
        {
            id: 3,
            title: "New customer registered",
            description: "Michael Brown created an account",
            time: "2 hours ago",
            icon: "user-plus"
        },
        {
            id: 4,
            title: "Order completed",
            description: "Order #ORD-2023-1001 marked as completed",
            time: "5 hours ago",
            icon: "check-circle"
        }
    ]
};

// DOM Elements
const sidebarLinks = document.querySelectorAll('.nav-link');
const contentSections = document.querySelectorAll('.content-section');
const productsTable = document.getElementById('products-table');
const ordersTable = document.getElementById('orders-table');
const orderFilter = document.getElementById('order-filter');
const addProductBtn = document.getElementById('add-product-btn');
const productModal = document.getElementById('product-modal');
const orderModal = document.getElementById('order-modal');
const closeModalBtns = document.querySelectorAll('.close-modal');
const productForm = document.getElementById('product-form');
const orderDetails = document.getElementById('order-details');
const activityList = document.querySelector('.activity-list');
const logoutBtn = document.getElementById('logout');

// Current state
let currentProductId = null;

// Initialize the dashboard
function initDashboard() {
    // Load products
    renderProductsTable();
    
    // Load orders
    renderOrdersTable();
    
    // Load activity
    renderActivity();
    
    // Update stats
    updateStats();
    
    // Set up event listeners
    setupEventListeners();
}

// Render products table
function renderProductsTable() {
    const tbody = productsTable.querySelector('tbody');
    tbody.innerHTML = '';
    
    sampleData.products.forEach(product => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>#${product.id}</td>
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>$${product.price.toFixed(2)}</td>
            <td>${product.stock}</td>
            <td><span class="status status-${product.status.replace('_', '-')}">${formatStatus(product.status)}</span></td>
            <td>
                <button class="btn btn-outline btn-sm edit-product" data-id="${product.id}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-outline btn-sm delete-product" data-id="${product.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    // Add event listeners to edit and delete buttons
    document.querySelectorAll('.edit-product').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const productId = parseInt(e.currentTarget.getAttribute('data-id'));
            editProduct(productId);
        });
    });
    
    document.querySelectorAll('.delete-product').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const productId = parseInt(e.currentTarget.getAttribute('data-id'));
            deleteProduct(productId);
        });
    });
}

// Render orders table
function renderOrdersTable(filter = 'all') {
    const tbody = ordersTable.querySelector('tbody');
    tbody.innerHTML = '';
    
    let ordersToShow = sampleData.orders;
    
    if (filter !== 'all') {
        ordersToShow = sampleData.orders.filter(order => order.status === filter);
    }
    
    ordersToShow.forEach(order => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${order.id}</td>
            <td>${order.customer}</td>
            <td>${order.date}</td>
            <td>$${order.amount.toFixed(2)}</td>
            <td><span class="status status-${order.status}">${formatStatus(order.status)}</span></td>
            <td>
                <button class="btn btn-outline btn-sm view-order" data-id="${order.id}">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    // Add event listeners to view buttons
    document.querySelectorAll('.view-order').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const orderId = e.currentTarget.getAttribute('data-id');
            viewOrder(orderId);
        });
    });
}

// Render activity list
function renderActivity() {
    activityList.innerHTML = '';
    
    sampleData.activity.forEach(activity => {
        const div = document.createElement('div');
        div.className = 'activity-item';
        div.innerHTML = `
            <div class="activity-icon">
                <i class="fas fa-${activity.icon}"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">${activity.title}</div>
                <div class="activity-description">${activity.description}</div>
                <div class="activity-time">${activity.time}</div>
            </div>
        `;
        activityList.appendChild(div);
    });
}

// Update dashboard stats
function updateStats() {
    document.getElementById('total-products').textContent = sampleData.products.length;
    document.getElementById('active-orders').textContent = sampleData.orders.filter(o => o.status === 'processing' || o.status === 'pending').length;
    document.getElementById('total-customers').textContent = '542'; // Hardcoded for demo
    
    // Calculate revenue
    const revenue = sampleData.orders
        .filter(o => o.status === 'completed')
        .reduce((sum, order) => sum + order.amount, 0);
    
    document.getElementById('revenue').textContent = `$${revenue.toFixed(2)}`;
}

// Format status for display
function formatStatus(status) {
    return status.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
}

// Edit product
function editProduct(id) {
    const product = sampleData.products.find(p => p.id === id);
    if (!product) return;
    
    currentProductId = id;
    document.getElementById('modal-title').textContent = 'Edit Product';
    document.getElementById('product-id').value = product.id;
    document.getElementById('product-name').value = product.name;
    document.getElementById('product-description').value = product.description;
}
// Dropdown menu functionality
document.querySelectorAll('.nav-item.has-dropdown').forEach(item => {
    item.addEventListener('mouseenter', () => {
        item.classList.add('hover');
    });
    item.addEventListener('mouseleave', () => {
        item.classList.remove('hover');
    });
});

// Mobile dropdown toggle (if needed)
document.querySelectorAll('.nav-item.has-dropdown > .nav-link').forEach(link => {
    link.addEventListener('click', (e) => {
        if (window.innerWidth < 992) { // Mobile view
            e.preventDefault();
            const parent = link.parentElement;
            parent.classList.toggle('open');
        }
    });
});