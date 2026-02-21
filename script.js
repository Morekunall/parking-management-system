// Smart Parking Management System - JavaScript

// Global variables
let currentUser = null;
let authToken = null;
let currentParkingId = null;
let providerParkings = []; // Store parking data for editing

// API Base URL (adjust for your XAMPP setup)
const API_BASE_URL = 'http://localhost/Traffic%20management/api';

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is already logged in
    const token = localStorage.getItem('authToken');
    const user = localStorage.getItem('currentUser');
    
    if (token && user) {
        authToken = token;
        currentUser = JSON.parse(user);
        showDashboard();
    } else {
        showWelcome();
    }
    
    // Set up form event listeners
    setupEventListeners();

    // Set up edit parking form listener
    document.getElementById('editParkingForm').addEventListener('submit', handleEditParking);
});

// Set up event listeners
function setupEventListeners() {
    // Login form
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    
    // Register form
    document.getElementById('registerForm').addEventListener('submit', handleRegister);
    
    // Search form
    document.getElementById('searchForm').addEventListener('submit', handleSearch);
    
    // Add parking form
    document.getElementById('addParkingForm').addEventListener('submit', handleAddParking);
}

// Navigation functions
function showWelcome() {
    hideAllSections();
    document.getElementById('welcomeSection').style.display = 'block';
}

function showLogin() {
    hideAllSections();
    document.getElementById('loginSection').style.display = 'block';
}

function showRegister() {
    hideAllSections();
    document.getElementById('registerSection').style.display = 'block';
}

function showDashboard() {
    hideAllSections();
    if (currentUser && currentUser.user_type === 'provider') {
        document.getElementById('providerDashboard').style.display = 'block';
        loadProviderData();
    } else if (currentUser && currentUser.user_type === 'customer') {
        document.getElementById('customerDashboard').style.display = 'block';
        loadCustomerData();
    }
}

function hideAllSections() {
    const sections = [
        'welcomeSection', 'loginSection', 'registerSection',
        'providerDashboard', 'customerDashboard'
    ];
    sections.forEach(section => {
        document.getElementById(section).style.display = 'none';
    });
}

// Authentication functions
async function handleLogin(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const loginData = {
        email: formData.get('email'),
        password: formData.get('password')
    };
    
    try {
        const response = await fetch(`${API_BASE_URL}/auth/login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(loginData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            authToken = result.token;
            currentUser = result.user;
            
            // Store in localStorage
            localStorage.setItem('authToken', authToken);
            localStorage.setItem('currentUser', JSON.stringify(currentUser));
            
            showMessage('Login successful!', 'success');
            showDashboard();
        } else {
            showMessage(result.message || 'Login failed', 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

async function handleRegister(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const registerData = {
        name: formData.get('name'),
        email: formData.get('email'),
        password: formData.get('password'),
        user_type: formData.get('userType')
    };
    
    try {
        const response = await fetch(`${API_BASE_URL}/auth/register.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(registerData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Registration successful! Please login.', 'success');
            showLogin();
        } else {
            showMessage(result.message || 'Registration failed', 'error');
        }
    } catch (error) {
        console.error('Registration error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

function logout() {
    authToken = null;
    currentUser = null;
    localStorage.removeItem('authToken');
    localStorage.removeItem('currentUser');
    showWelcome();
}

// Provider functions
function showProviderTab(tabName) {
    // Update nav buttons
    document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Show/hide tab content
    document.getElementById('myParkingsTab').style.display = tabName === 'myParkings' ? 'block' : 'none';
    document.getElementById('bookingsTab').style.display = tabName === 'bookings' ? 'block' : 'none';
    
    if (tabName === 'bookings') {
        loadBookings();
    }
}

function showAddParking() {
    document.getElementById('addParkingModal').style.display = 'flex';
}

function closeAddParking() {
    document.getElementById('addParkingModal').style.display = 'none';
    document.getElementById('addParkingForm').reset();
}

async function handleAddParking(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const parkingData = {
        name: formData.get('name'),
        location: formData.get('location'),
        available_slots: parseInt(formData.get('availableSlots')),
        price_per_hour: parseFloat(formData.get('pricePerHour')),
        available_date: formData.get('availableDate'),
        available_time: formData.get('availableTime'),
        image_url: formData.get('imageUrl') || null
    };
    
    try {
        const response = await fetch(`${API_BASE_URL}/parking/add.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify(parkingData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Parking space added successfully!', 'success');
            closeAddParking();
            loadProviderData();
        } else {
            showMessage(result.message || 'Failed to add parking space', 'error');
        }
    } catch (error) {
        console.error('Add parking error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

async function loadProviderData() {
    try {
        const response = await fetch(`${API_BASE_URL}/parking/my-parkings.php`, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });

        const result = await response.json();

        if (result.success) {
            providerParkings = result.data; // Store for editing
            displayParkingSpaces(result.data);
        } else {
            showMessage('Failed to load parking spaces', 'error');
        }
    } catch (error) {
        console.error('Load parking error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

function displayParkingSpaces(parkings) {
    const container = document.getElementById('parkingList');
    
    if (parkings.length === 0) {
        container.innerHTML = '<p>No parking spaces found. Add your first parking space!</p>';
        return;
    }
    
    container.innerHTML = parkings.map(parking => `
        <div class="parking-card">
            <h3>${parking.name}</h3>
            <p><i class="fas fa-map-marker-alt"></i> ${parking.location}</p>
            <p><i class="fas fa-calendar"></i> ${parking.available_date}</p>
            <p><i class="fas fa-clock"></i> ${parking.available_time}</p>
            <p><i class="fas fa-car"></i> ${parking.available_slots} slots available</p>
            <div class="parking-price">₹${parking.price_per_hour}/hour</div>
            <div class="parking-actions">
                <button class="btn btn-primary btn-small" onclick="editParking(${parking.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-secondary btn-small" onclick="deleteParking(${parking.id})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    `).join('');
}

async function loadBookings() {
    try {
        const response = await fetch(`${API_BASE_URL}/bookings/provider-bookings.php`, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayBookings(result.data, 'bookingsList');
        } else {
            showMessage('Failed to load bookings', 'error');
        }
    } catch (error) {
        console.error('Load bookings error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

// Customer functions
function showCustomerTab(tabName) {
    // Update nav buttons
    document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Show/hide tab content
    document.getElementById('searchTab').style.display = tabName === 'search' ? 'block' : 'none';
    document.getElementById('myBookingsTab').style.display = tabName === 'myBookings' ? 'block' : 'none';
    
    if (tabName === 'myBookings') {
        loadCustomerBookings();
    }
}

async function handleSearch(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const searchParams = {
        location: formData.get('location'),
        date: formData.get('date'),
        time: formData.get('time'),
        max_price: formData.get('maxPrice') || null
    };
    
    try {
        const response = await fetch(`${API_BASE_URL}/parking/search.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(searchParams)
        });
        
        const result = await response.json();
        
        if (result.success) {
            displaySearchResults(result.data);
        } else {
            showMessage('No parking spaces found', 'info');
        }
    } catch (error) {
        console.error('Search error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

function displaySearchResults(parkings) {
    const container = document.getElementById('searchResults');
    
    if (parkings.length === 0) {
        container.innerHTML = '<p>No parking spaces found matching your criteria.</p>';
        return;
    }
    
    container.innerHTML = parkings.map(parking => `
        <div class="parking-card">
            <h3>${parking.name}</h3>
            <p><i class="fas fa-map-marker-alt"></i> ${parking.location}</p>
            <p><i class="fas fa-calendar"></i> ${parking.available_date}</p>
            <p><i class="fas fa-clock"></i> ${parking.available_time}</p>
            <p><i class="fas fa-car"></i> 
                <span class="slot-info">
                    ${parking.remaining_slots || parking.available_slots} slots available
                    ${parking.remaining_slots !== undefined ? `(${parking.available_slots} total)` : ''}
                </span>
            </p>
            <div class="parking-price">₹${parking.price_per_hour}/hour</div>
            <div class="parking-actions">
                <button class="btn btn-primary btn-small" onclick="bookParking(${parking.id})" 
                        ${(parking.remaining_slots || parking.available_slots) <= 0 ? 'disabled' : ''}>
                    <i class="fas fa-bookmark"></i> 
                    ${(parking.remaining_slots || parking.available_slots) <= 0 ? 'Fully Booked' : 'Book Now'}
                </button>
            </div>
        </div>
    `).join('');
}

function bookParking(parkingId) {
    currentParkingId = parkingId;
    document.getElementById('bookingModal').style.display = 'flex';
    
    // Load booking form
    document.getElementById('bookingContent').innerHTML = `
        <form id="bookingForm">
            <div class="form-group">
                <label for="bookingDate">Booking Date</label>
                <input type="date" id="bookingDate" name="bookingDate" required>
            </div>
            <div class="form-group">
                <label for="startTime">Start Time</label>
                <input type="time" id="startTime" name="startTime" required>
            </div>
            <div class="form-group">
                <label for="endTime">End Time</label>
                <input type="time" id="endTime" name="endTime" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeBookingModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Booking</button>
            </div>
        </form>
    `;
    
    // Add event listener for booking form
    document.getElementById('bookingForm').addEventListener('submit', handleBooking);
}

function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
    currentParkingId = null;
}

async function handleBooking(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const bookingData = {
        parking_id: currentParkingId,
        booking_date: formData.get('bookingDate'),
        start_time: formData.get('startTime'),
        end_time: formData.get('endTime')
    };
    
    try {
        const response = await fetch(`${API_BASE_URL}/bookings/create.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify(bookingData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage(`Booking created successfully! Remaining slots: ${result.remaining_slots}/${result.total_slots}`, 'success');
            closeBookingModal();
            loadCustomerBookings();
            
            // Refresh search results to show updated slot counts
            if (currentUser && currentUser.user_type === 'customer') {
                const searchForm = document.getElementById('searchForm');
                if (searchForm) {
                    searchForm.dispatchEvent(new Event('submit'));
                }
            }
        } else {
            showMessage(result.message || 'Failed to create booking', 'error');
        }
    } catch (error) {
        console.error('Booking error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

async function loadCustomerBookings() {
    try {
        const response = await fetch(`${API_BASE_URL}/bookings/my-bookings.php`, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayBookings(result.data, 'customerBookingsList');
        } else {
            showMessage('Failed to load bookings', 'error');
        }
    } catch (error) {
        console.error('Load bookings error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

function displayBookings(bookings, containerId) {
    const container = document.getElementById(containerId);
    
    if (bookings.length === 0) {
        container.innerHTML = '<p>No bookings found.</p>';
        return;
    }
    
    container.innerHTML = bookings.map(booking => `
        <div class="booking-card">
            <h3>${booking.parking_name}</h3>
            <p><i class="fas fa-map-marker-alt"></i> ${booking.location}</p>
            <p><i class="fas fa-calendar"></i> ${booking.booking_date}</p>
            <p><i class="fas fa-clock"></i> ${booking.start_time} - ${booking.end_time}</p>
            <p><i class="fas fa-rupee-sign"></i> Total: ₹${booking.total_cost}</p>
            <span class="status-badge status-${booking.status}">${booking.status}</span>
            <div class="booking-actions">
                ${booking.status === 'pending' ? `
                    <button class="btn btn-primary btn-small" onclick="updateBookingStatus(${booking.id}, 'confirmed')">
                        <i class="fas fa-check"></i> Confirm
                    </button>
                    <button class="btn btn-secondary btn-small" onclick="updateBookingStatus(${booking.id}, 'cancelled')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                ` : ''}
                ${currentUser.user_type === 'customer' && booking.status === 'pending' ? `
                    <button class="btn btn-secondary btn-small" onclick="cancelBooking(${booking.id})">
                        <i class="fas fa-times"></i> Cancel Booking
                    </button>
                ` : ''}
            </div>
        </div>
    `).join('');
}

async function updateBookingStatus(bookingId, status) {
    try {
        const response = await fetch(`${API_BASE_URL}/bookings/update-status.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify({
                booking_id: bookingId,
                status: status
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const slotMessage = result.remaining_slots !== undefined ? 
                ` Remaining slots: ${result.remaining_slots}/${result.total_slots}` : '';
            showMessage(`Booking ${status} successfully!${slotMessage}`, 'success');
            
            if (currentUser.user_type === 'provider') {
                loadBookings();
            } else {
                loadCustomerBookings();
            }
            
            // Refresh search results to show updated slot counts
            if (currentUser.user_type === 'customer') {
                const searchForm = document.getElementById('searchForm');
                if (searchForm) {
                    searchForm.dispatchEvent(new Event('submit'));
                }
            }
        } else {
            showMessage(result.message || 'Failed to update booking', 'error');
        }
    } catch (error) {
        console.error('Update booking error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

async function cancelBooking(bookingId) {
    await updateBookingStatus(bookingId, 'cancelled');
}

async function deleteParking(parkingId) {
    if (!confirm('Are you sure you want to delete this parking space?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/parking/delete.php`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify({ parking_id: parkingId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Parking space deleted successfully!', 'success');
            loadProviderData();
        } else {
            showMessage(result.message || 'Failed to delete parking space', 'error');
        }
    } catch (error) {
        console.error('Delete parking error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

function editParking(parkingId) {
    // Find the parking data
    const parking = providerParkings.find(p => p.id == parkingId);
    if (!parking) {
        showMessage('Parking space not found', 'error');
        return;
    }

    // Populate the edit form
    document.getElementById('editParkingId').value = parking.id;
    document.getElementById('editParkingName').value = parking.name;
    document.getElementById('editParkingLocation').value = parking.location;
    document.getElementById('editAvailableSlots').value = parking.available_slots;
    document.getElementById('editPricePerHour').value = parking.price_per_hour;
    document.getElementById('editAvailableDate').value = parking.available_date;
    document.getElementById('editAvailableTime').value = parking.available_time;
    document.getElementById('editParkingImage').value = parking.image_url || '';

    // Show the modal
    document.getElementById('editParkingModal').style.display = 'flex';
}

function closeEditParking() {
    document.getElementById('editParkingModal').style.display = 'none';
    document.getElementById('editParkingForm').reset();
}

async function handleEditParking(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const parkingData = {
        parking_id: formData.get('parkingId'),
        name: formData.get('name'),
        location: formData.get('location'),
        available_slots: parseInt(formData.get('availableSlots')),
        price_per_hour: parseFloat(formData.get('pricePerHour')),
        available_date: formData.get('availableDate'),
        available_time: formData.get('availableTime'),
        image_url: formData.get('imageUrl') || null
    };

    try {
        const response = await fetch(`${API_BASE_URL}/parking/update.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify(parkingData)
        });

        const result = await response.json();

        if (result.success) {
            showMessage('Parking space updated successfully!', 'success');
            closeEditParking();
            loadProviderData(); // Refresh the list
        } else {
            showMessage(result.message || 'Failed to update parking space', 'error');
        }
    } catch (error) {
        console.error('Edit parking error:', error);
        showMessage('Network error. Please try again.', 'error');
    }
}

// Utility functions
function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    messageDiv.textContent = message;
    
    // Insert at the top of main content
    const mainContent = document.querySelector('.main-content');
    mainContent.insertBefore(messageDiv, mainContent.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

// Set default date to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            input.value = today;
        }
    });
});
