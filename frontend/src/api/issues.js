const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? "http://localhost:8000/api";

function withQuery(path, params) {
  const url = new URL(`${API_BASE_URL}${path}`);
  Object.entries(params).forEach(([key, value]) => {
    if (value !== "") {
      url.searchParams.set(key, value);
    }
  });
  return url.toString();
}

export async function fetchIssues(filters = {}) {
  const response = await fetch(withQuery("/issues", filters));
  if (!response.ok) throw new Error("Failed to load issues");
  return response.json();
}

export async function createIssue(payload) {
  const response = await fetch(`${API_BASE_URL}/issues`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });

  const result = await response.json();
  if (!response.ok) {
    throw new Error(result.message ?? "Issue creation failed");
  }

  return result;
}
