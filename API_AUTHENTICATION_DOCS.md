# API Authentication Documentation

This document provides information on how to use the authentication API endpoints with your React frontend application.

## API Endpoints

All API routes are prefixed with `/api`. For example, if your backend is running at `http://localhost:8000`, the login endpoint would be `http://localhost:8000/api/login`.

### Public Endpoints

These endpoints don't require authentication:

#### 1. Register a new user

```
POST /api/register
```

Request body:
```json
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response (201 Created):
```json
{
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "email_verified_at": null,
    "created_at": "2025-08-08T14:30:00.000000Z",
    "updated_at": "2025-08-08T14:30:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz123456",
  "token_type": "Bearer"
}
```

#### 2. Login

```
POST /api/login
```

Request body:
```json
{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "web"
}
```

Note: The `device_name` field is optional and defaults to "web" if not provided.

Response (200 OK):
```json
{
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "email_verified_at": null,
    "created_at": "2025-08-08T14:30:00.000000Z",
    "updated_at": "2025-08-08T14:30:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz123456",
  "token_type": "Bearer"
}
```

### Protected Endpoints

These endpoints require authentication. You must include the token in the request headers:

```
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz123456
```

#### 3. Get authenticated user information

```
GET /api/user
```

Response (200 OK):
```json
{
  "id": 1,
  "name": "User Name",
  "email": "user@example.com",
  "email_verified_at": null,
  "created_at": "2025-08-08T14:30:00.000000Z",
  "updated_at": "2025-08-08T14:30:00.000000Z"
}
```

#### 4. Logout (revoke token)

```
POST /api/logout
```

Response (200 OK):
```json
{
  "message": "Successfully logged out"
}
```

## Using Authentication in React

Here's an example of how to implement authentication in your React application:

### 1. Setting up Axios

```jsx
// src/api/axios.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Add a request interceptor to include the token in all requests
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

export default api;
```

### 2. Authentication Context

```jsx
// src/context/AuthContext.js
import React, { createContext, useState, useEffect, useContext } from 'react';
import api from '../api/axios';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Check if user is logged in on page load
    const token = localStorage.getItem('token');
    if (token) {
      fetchUser();
    } else {
      setLoading(false);
    }
  }, []);

  const fetchUser = async () => {
    try {
      const response = await api.get('/user');
      setUser(response.data);
    } catch (error) {
      console.error('Error fetching user:', error);
      localStorage.removeItem('token');
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password) => {
    try {
      const response = await api.post('/login', { email, password });
      localStorage.setItem('token', response.data.token);
      setUser(response.data.user);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'An error occurred during login' };
    }
  };

  const register = async (name, email, password, password_confirmation) => {
    try {
      const response = await api.post('/register', {
        name,
        email,
        password,
        password_confirmation
      });
      localStorage.setItem('token', response.data.token);
      setUser(response.data.user);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'An error occurred during registration' };
    }
  };

  const logout = async () => {
    try {
      await api.post('/logout');
    } catch (error) {
      console.error('Error during logout:', error);
    } finally {
      localStorage.removeItem('token');
      setUser(null);
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        loading,
        login,
        register,
        logout,
        isAuthenticated: !!user
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
```

### 3. Protected Route Component

```jsx
// src/components/ProtectedRoute.js
import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const ProtectedRoute = ({ children }) => {
  const { isAuthenticated, loading } = useAuth();

  if (loading) {
    return <div>Loading...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" />;
  }

  return children;
};

export default ProtectedRoute;
```

### 4. Login Component Example

```jsx
// src/pages/Login.js
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const Login = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await login(email, password);
      navigate('/dashboard');
    } catch (error) {
      setError(error.message || 'Failed to login');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <h1>Login</h1>
      {error && <div className="error">{error}</div>}
      <form onSubmit={handleSubmit}>
        <div>
          <label>Email:</label>
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </div>
        <div>
          <label>Password:</label>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </div>
        <button type="submit" disabled={loading}>
          {loading ? 'Logging in...' : 'Login'}
        </button>
      </form>
    </div>
  );
};

export default Login;
```

### 5. App Component with Routes

```jsx
// src/App.js
import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import Home from './pages/Home';

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route
            path="/dashboard"
            element={
              <ProtectedRoute>
                <Dashboard />
              </ProtectedRoute>
            }
          />
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default App;
```

## Security Considerations

1. **HTTPS**: Always use HTTPS in production to encrypt data in transit.
2. **Token Storage**: Storing tokens in localStorage is convenient but vulnerable to XSS attacks. For higher security, consider using HttpOnly cookies.
3. **CORS**: Ensure your backend has proper CORS configuration to allow requests from your frontend domain.
4. **Token Expiration**: Consider implementing token expiration and refresh mechanisms for better security.
5. **Validation**: Always validate user input on both frontend and backend.

## Troubleshooting

1. **CORS Issues**: If you encounter CORS errors, ensure your Laravel backend has the proper CORS configuration.
2. **Authentication Failures**: Check that you're sending the token in the correct format in the Authorization header.
3. **Token Expiration**: If authentication suddenly fails, the token might have expired. Implement a mechanism to refresh tokens or redirect to login.
