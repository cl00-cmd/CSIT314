class Account {
  async login(strID, strPassword) {
    const response = await fetch("./api/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: strID, password: strPassword }),
    });

    if (response.status === 401) {
      return false;
    }

    if (!response.ok) {
      throw new Error("Login request failed");
    }

    const payload = await response.json();
    return payload?.success === true;
  }

  logout() {
    sessionStorage.removeItem(LOGIN_SESSION_KEY);
  }
}

const LOGIN_SESSION_KEY = "accountLoggedIn";

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
const account = new Account();

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
      loginView.displayError("Unable to connect to authentication server.");
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
