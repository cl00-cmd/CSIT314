class Account {
  constructor(userID, password, email) {
    this.userID = userID;
    this.password = password;
    this.email = email;
  }

  verifyLogin(strID, strPassword) {
    return this.userID === strID && this.password === strPassword;
  }

  async login(strID, strPassword) {
    const isValid = this.verifyLogin(strID, strPassword);
    if (!isValid) {
      return false;
    }

    await saveAccount(this);
    return true;
  }

  logout() {
    sessionStorage.removeItem(LOGIN_SESSION_KEY);
  }
}

const DB_NAME = "AccountAuthDB";
const DB_VERSION = 1;
const ACCOUNT_STORE_NAME = "Account";
const LOGIN_SESSION_KEY = "accountLoggedIn";

function openAccountDatabase() {
  return new Promise((resolve, reject) => {
    const request = window.indexedDB.open(DB_NAME, DB_VERSION);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains(ACCOUNT_STORE_NAME)) {
        db.createObjectStore(ACCOUNT_STORE_NAME, { keyPath: "ID" });
      }
    };

    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

async function hashPassword(password) {
  const iterations = 100000;
  const encoder = new TextEncoder();
  const salt = window.crypto.getRandomValues(new Uint8Array(16));
  const keyMaterial = await window.crypto.subtle.importKey(
    "raw",
    encoder.encode(password),
    { name: "PBKDF2" },
    false,
    ["deriveBits"],
  );
  const derivedBits = await window.crypto.subtle.deriveBits(
    {
      name: "PBKDF2",
      salt,
      iterations,
      hash: "SHA-256",
    },
    keyMaterial,
    256,
  );

  const saltHex = Array.from(salt)
    .map((byte) => byte.toString(16).padStart(2, "0"))
    .join("");
  const hashHex = Array.from(new Uint8Array(derivedBits))
    .map((byte) => byte.toString(16).padStart(2, "0"))
    .join("");
  return `pbkdf2$${iterations}$${saltHex}$${hashHex}`;
}

async function saveAccount(account) {
  const hashedPassword = await hashPassword(account.password);
  const db = await openAccountDatabase();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(ACCOUNT_STORE_NAME, "readwrite");
    const store = transaction.objectStore(ACCOUNT_STORE_NAME);

    store.put({
      ID: account.userID,
      Password: hashedPassword,
      Email: account.email,
    });

    transaction.oncomplete = () => {
      db.close();
      resolve();
    };
    transaction.onerror = () => {
      db.close();
      reject(transaction.error);
    };
  });
}

class LoginView {
  getUserInput() {
    const idInput = document.getElementById("userId");
    const passwordInput = document.getElementById("password");
    return {
      strID: (idInput?.value || "").trim(),
      strPassword: passwordInput?.value || "",
    };
  }

  displayError(message) {
    const messageElement = document.getElementById("message");
    if (messageElement) {
      messageElement.textContent = message;
    }
  }
}

const pathname = window.location.pathname;
const account = new Account("ua", "admin123", "ua@example.com");

// Login flow (login page handling)
if (pathname.endsWith("/login.html") || pathname === "/" || pathname.endsWith("/CSIT314/")) {
  const loginView = new LoginView();
  const form = document.getElementById("login-form");

  form?.addEventListener("submit", async (event) => {
    event.preventDefault();

    const { strID, strPassword } = loginView.getUserInput();
    let loginStatus = false;
    try {
      loginStatus = await account.login(strID, strPassword);
    } catch (error) {
      loginView.displayError("Unable to save account details.");
      return;
    }

    if (loginStatus) {
      sessionStorage.setItem(LOGIN_SESSION_KEY, "true");
      window.location.href = "./dashboard.html";
      return;
    }

    loginView.displayError("Invalid User ID or Password");
  });
}

if (pathname.endsWith("/dashboard.html")) {
  if (sessionStorage.getItem(LOGIN_SESSION_KEY) !== "true") {
    window.location.href = "./login.html";
  }
}

// Logout flow
if (pathname.endsWith("/logout.html")) {
  account.logout();
}
