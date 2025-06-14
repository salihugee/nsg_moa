export const msalConfig = {
    auth: {
        clientId: import.meta.env.VITE_AZURE_CLIENT_ID || '',
        authority: `https://login.microsoftonline.com/${import.meta.env.VITE_AZURE_TENANT_ID}`,
        redirectUri: window.location.origin,
    },
    cache: {
        cacheLocation: "localStorage",
        storeAuthStateInCookie: false,
    },
};

export const loginRequest = {
    scopes: ["User.Read", "https://analysis.windows.net/powerbi/api/Report.Read.All"]
};

export const powerBiScopes = {
    scopes: ["https://analysis.windows.net/powerbi/api/Report.Read.All"]
};
