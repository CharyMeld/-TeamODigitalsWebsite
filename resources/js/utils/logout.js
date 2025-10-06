import { post } from "@/axiosConfig.js";

export async function logout() {
    try {
        // Call Laravel logout endpoint
        await post("/logout");

        console.log("✅ Logged out on server");
    } catch (error) {
        console.warn("⚠️ Server logout failed, clearing client state anyway:", error.message);
    } finally {
        // Always clear client state
        localStorage.removeItem("authToken");
        localStorage.removeItem("currentUser");

        // Reset CSRF state
        window.csrfInitialized = false;

        // Redirect to login
        window.location.href = "/login";
    }
}

