class Account {
  async login(strID, strPassword) {
    const response = await fetch("/api/login", {
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
    if (payload?.success !== true || !payload?.account) {
      return null;
    }

    return payload.account;
  }

  async saveAccount(actorId, accountDetails) {
    const response = await fetch("/api/accounts", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        actorId,
        id: accountDetails.id,
        email: accountDetails.email,
        password: accountDetails.password,
        role: accountDetails.role,
      }),
    });

    const payload = await response.json().catch(() => null);
    if (!response.ok) {
      return {
        success: false,
        message: payload?.message || "Unable to create account",
      };
    }

    return {
      success: payload?.success === true,
      message: payload?.message || "Account Created",
    };
  }

  logout() {
    sessionStorage.removeItem(LOGIN_SESSION_KEY);
    sessionStorage.removeItem(LOGIN_ACCOUNT_KEY);
  }
}

const LOGIN_SESSION_KEY = "accountLoggedIn";
const LOGIN_ACCOUNT_KEY = "accountDetails";

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

class UACreateAccount {
  async createAccount(createAccountControl, actorAccount) {
    return createAccountControl.saveAccount(actorAccount);
  }

  getNewAccountDetails() {
    const idInput = document.getElementById("newUserId");
    const emailInput = document.getElementById("newEmail");
    const passwordInput = document.getElementById("newPassword");
    const roleInput = document.getElementById("newRole");

    return {
      id: (idInput?.value || "").trim(),
      email: (emailInput?.value || "").trim(),
      password: passwordInput?.value || "",
      role: (roleInput?.value || "user").trim(),
    };
  }

  displayMessage(message, isSuccess = false) {
    const messageElement = document.getElementById("create-account-message");
    if (!messageElement) {
      return;
    }

    messageElement.textContent = message;
    messageElement.dataset.state = isSuccess ? "success" : "error";
  }

  showSection() {
    const section = document.getElementById("create-account-section");
    if (section) {
      section.hidden = false;
    }
  }

  hideSection() {
    const section = document.getElementById("create-account-section");
    if (section) {
      section.hidden = true;
    }
  }

  resetForm() {
    const form = document.getElementById("create-account-form");
    form?.reset();
  }
}

class UACreateAccountC {
  constructor(account, createAccountView) {
    this.account = account;
    this.createAccountView = createAccountView;
  }

  async saveAccount(actorAccount) {
    const newAccountDetails = this.createAccountView.getNewAccountDetails();
    const result = await this.account.saveAccount(actorAccount.id, newAccountDetails);
    this.createAccountView.displayMessage(result.message, result.success);

    if (result.success) {
      this.createAccountView.resetForm();
    }
  }
}

const pathname = window.location.pathname;
const account = new Account();

function formatRoleLabel(role) {
  const roleLabels = {
    user: "Donor",
    donor: "Donor",
    user_admin: "User Admin",
    platform_manager: "Platform Manager",
    fund_raiser: "Fund Raiser",
  };

  return roleLabels[role] || role || "User";
}

// Login flow (login page handling)
if (pathname.endsWith("/login.html") || pathname === "/") {
  const loginView = new LoginView();
  const form = document.getElementById("login-form");

  form?.addEventListener("submit", async (event) => {
    event.preventDefault();

    const { strID, strPassword } = loginView.getUserInput();
    let accountDetails = null;
    try {
      accountDetails = await account.login(strID, strPassword);
    } catch (error) {
      loginView.displayError("Unable to connect to authentication server.");
      return;
    }

    if (accountDetails) {
      sessionStorage.setItem(LOGIN_SESSION_KEY, "true");
      sessionStorage.setItem(LOGIN_ACCOUNT_KEY, JSON.stringify(accountDetails));
      window.location.href = "./dashboard.html";
      return;
    }

    loginView.displayError("Invalid User ID or Password");
  });
}

if (pathname.endsWith("/dashboard.html")) {
  if (sessionStorage.getItem(LOGIN_SESSION_KEY) !== "true") {
    window.location.href = "./login.html";
  } else {
    const accountDetails = JSON.parse(sessionStorage.getItem(LOGIN_ACCOUNT_KEY) || "null");
    const heading = document.getElementById("dashboard-title");
    const roleTag = document.getElementById("role-tag");
    const dashboardContext = document.getElementById("dashboard-context");

    if (heading && accountDetails?.role) {
      heading.textContent = `${formatRoleLabel(accountDetails.role)} Dashboard`;
    }

    if (roleTag && accountDetails?.role) {
      roleTag.textContent = `Role: ${formatRoleLabel(accountDetails.role)}`;
    }

    if (dashboardContext && accountDetails?.id) {
      dashboardContext.textContent = `Signed in as ${accountDetails.id}. Manage account setup, profiles, and role access from this panel.`;
    }

    const createAccountView = new UACreateAccount();
    if (accountDetails?.role === "user_admin") {
      createAccountView.showSection();
      const createAccountControl = new UACreateAccountC(account, createAccountView);
      const createAccountForm = document.getElementById("create-account-form");

      createAccountForm?.addEventListener("submit", async (event) => {
        event.preventDefault();

        try {
          await createAccountView.createAccount(createAccountControl, accountDetails);
        } catch (error) {
          createAccountView.displayMessage("Unable to create account");
        }
      });
    } else {
      createAccountView.hideSection();
    }
  }
}

// Logout flow
if (pathname.endsWith("/logout.html")) {
  account.logout();
}
