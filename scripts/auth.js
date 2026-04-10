class Account {
  constructor(userID, password, email) {
    this.userID = userID;
    this.password = password;
    this.email = email;
  }

  verifyLogin(strID, strPassword) {
    return this.userID === strID && this.password === strPassword;
  }
}

const DB_NAME = "UAAuthDB";
const DB_VERSION = 1;
const ACCOUNT_STORE_NAME = "Account";

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

class loginUAC {
  constructor(account) {
    this.account = account;
  }

  login(strID, strPassword) {
    return this.account.verifyLogin(strID, strPassword);
  }
}

class loginUA {
  constructor(controller) {
    this.controller = controller;
  }

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

class logoutUA {
  logout() {
    sessionStorage.removeItem("uaLoggedIn");
  }
}

const pathname = window.location.pathname;
const uaAccount = new Account("ua", "admin123", "ua@example.com");

if (pathname.endsWith("/login.html") || pathname === "/" || pathname.endsWith("/CSIT314/")) {
  const loginController = new loginUAC(uaAccount);
  const loginBoundary = new loginUA(loginController);
  const form = document.getElementById("login-form");

  form?.addEventListener("submit", async (event) => {
    event.preventDefault();

    const { strID, strPassword } = loginBoundary.getUserInput();
    const loginStatus = loginController.login(strID, strPassword);

    if (loginStatus) {
      try {
        await saveAccount(uaAccount);
      } catch (error) {
        loginBoundary.displayError("Unable to save account details.");
        return;
      }
      sessionStorage.setItem("uaLoggedIn", "true");
      window.location.href = "./dashboard.html";
      return;
    }

    loginBoundary.displayError("Invalid User ID or Password");
  });
}

if (pathname.endsWith("/dashboard.html")) {
  if (sessionStorage.getItem("uaLoggedIn") !== "true") {
    window.location.href = "./login.html";
  }
}

if (pathname.endsWith("/logout.html")) {
  const logoutBoundary = new logoutUA();
  logoutBoundary.logout();
}
