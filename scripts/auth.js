class Account {
  constructor(userID, password) {
    this.userID = userID;
    this.password = password;
  }

  verifyLogin(strID, strPassword) {
    return this.userID === strID && this.password === strPassword;
  }
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
const uaAccount = new Account("ua", "admin123");

if (pathname.endsWith("/login.html") || pathname === "/" || pathname.endsWith("/CSIT314/")) {
  const loginController = new loginUAC(uaAccount);
  const loginBoundary = new loginUA(loginController);
  const form = document.getElementById("login-form");

  form?.addEventListener("submit", (event) => {
    event.preventDefault();

    const { strID, strPassword } = loginBoundary.getUserInput();
    const loginStatus = loginController.login(strID, strPassword);

    if (loginStatus) {
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
