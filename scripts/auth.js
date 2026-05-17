// Entity class that handles account-related API requests.
class Account {
  // Sends login details to the backend for authentication.
  async login(strID, strPassword) {
    const response = await fetch("/api/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: strID, password: strPassword }),
    });

    // Returns false when login is unauthorized.
    if (response.status === 401) {
      return false;
    }

    // Stops when the login request fails.
    if (!response.ok) {
      throw new Error("Login request failed");
    }

    // Reads the login response from the backend.
    const payload = await response.json();

    if (payload?.success !== true || !payload?.account) {
      return null;
    }

    return payload.account;
  }

  // Sends new account details to the backend.
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

    // Reads the backend response.
    const payload = await response.json().catch(() => null);

    // Returns error message when account creation fails.
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

  // Clears the stored login session.
  logout() {
    sessionStorage.removeItem(LOGIN_SESSION_KEY);
    sessionStorage.removeItem(LOGIN_ACCOUNT_KEY);
  }
}

// Session storage keys.
const LOGIN_SESSION_KEY = "accountLoggedIn";
const LOGIN_ACCOUNT_KEY = "accountDetails";

// Boundary class for the login page.
class LoginView {
  // Gets login input from the form.
  getUserInput() {
    const idInput = document.getElementById("userId");
    const passwordInput = document.getElementById("password");

    return {
      strID: (idInput?.value || "").trim(),
      strPassword: passwordInput?.value || "",
    };
  }

  // Displays login error message.
  displayError(message) {
    const messageElement = document.getElementById("message");

    if (messageElement) {
      messageElement.textContent = message;
    }
  }
}

// Boundary class for the create account section.
class UACreateAccount {
  // Sends account creation request to the Control class.
  async createAccount(createAccountControl, actorAccount) {
    return createAccountControl.saveAccount(actorAccount);
  }

  // Gets new account details from the form.
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

  // Displays account creation success or error message.
  displayMessage(message, isSuccess = false) {
    const messageElement = document.getElementById("create-account-message");

    if (!messageElement) {
      return;
    }

    messageElement.textContent = message;
    messageElement.dataset.state = isSuccess ? "success" : "error";
  }

  // Shows the create account section.
  showSection() {
    const section = document.getElementById("create-account-section");

    if (section) {
      section.hidden = false;
    }
  }

  // Hides the create account section.
  hideSection() {
    const section = document.getElementById("create-account-section");

    if (section) {
      section.hidden = true;
    }
  }

  // Clears the create account form.
  resetForm() {
    const form = document.getElementById("create-account-form");
    form?.reset();
  }
}

// Control class for creating user accounts.
class UACreateAccountC {
  constructor(account, createAccountView) {
    this.account = account;
    this.createAccountView = createAccountView;
  }

  // Gets form data from Boundary and asks Entity to save the account.
  async saveAccount(actorAccount) {
    const newAccountDetails = this.createAccountView.getNewAccountDetails();
    const result = await this.account.saveAccount(actorAccount.id, newAccountDetails);

    this.createAccountView.displayMessage(result.message, result.success);

    if (result.success) {
      this.createAccountView.resetForm();
    }
  }
}

// Gets current page path and creates Account Entity object.
const pathname = window.location.pathname;
const account = new Account();

// Converts role code into a readable role label.
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

// Login page flow.
if (pathname.endsWith("/login.html") || pathname === "/") {
  const loginView = new LoginView();
  const form = document.getElementById("login-form");

  form?.addEventListener("submit", async (event) => {
    event.preventDefault();

    // Boundary gets user input.
    const { strID, strPassword } = loginView.getUserInput();

    let accountDetails = null;

    try {
      // Boundary calls Entity login function.
      accountDetails = await account.login(strID, strPassword);
    } catch (error) {
      loginView.displayError("Unable to connect to authentication server.");
      return;
    }

    // Stores login session and redirects to dashboard.
    if (accountDetails) {
      sessionStorage.setItem(LOGIN_SESSION_KEY, "true");
      sessionStorage.setItem(LOGIN_ACCOUNT_KEY, JSON.stringify(accountDetails));
      window.location.href = "./dashboard.html";
      return;
    }

    loginView.displayError("Invalid User ID or Password");
  });
}

// Dashboard page flow.
if (pathname.endsWith("/dashboard.html")) {
  // Redirects user to login page if not logged in.
  if (sessionStorage.getItem(LOGIN_SESSION_KEY) !== "true") {
    window.location.href = "./login.html";
  } else {
    // Gets logged-in account details.
    const accountDetails = JSON.parse(sessionStorage.getItem(LOGIN_ACCOUNT_KEY) || "null");

    const heading = document.getElementById("dashboard-title");
    const roleTag = document.getElementById("role-tag");
    const dashboardContext = document.getElementById("dashboard-context");

    // Displays dashboard title based on user role.
    if (heading && accountDetails?.role) {
      heading.textContent = `${formatRoleLabel(accountDetails.role)} Dashboard`;
    }

    // Displays role label.
    if (roleTag && accountDetails?.role) {
      roleTag.textContent = `Role: ${formatRoleLabel(accountDetails.role)}`;
    }

    // Displays signed-in user information.
    if (dashboardContext && accountDetails?.id) {
      dashboardContext.textContent = `Signed in as ${accountDetails.id}. Manage account setup, profiles, and role access from this panel.`;
    }

    // Shows create account function only for User Admin.
    const createAccountView = new UACreateAccount();

    if (accountDetails?.role === "user_admin") {
      createAccountView.showSection();

      const createAccountControl = new UACreateAccountC(account, createAccountView);
      const createAccountForm = document.getElementById("create-account-form");

      createAccountForm?.addEventListener("submit", async (event) => {
        event.preventDefault();

        try {
          // Boundary calls Control to create account.
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

// Logout page flow.
if (pathname.endsWith("/logout.html")) {
  account.logout();
}