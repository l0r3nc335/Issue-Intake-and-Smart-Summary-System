import { useEffect, useState } from "react";
import { createIssue, fetchIssues } from "./api/issues";
import { IssueForm } from "./components/IssueForm";
import { IssueList } from "./components/IssueList";

const initialFilters = {
  status: "",
  category: "",
  priority: "",
};

export function App() {
  const [issues, setIssues] = useState([]);
  const [filters, setFilters] = useState(initialFilters);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  async function loadIssues(nextFilters = filters) {
    setLoading(true);
    setError("");
    try {
      const response = await fetchIssues(nextFilters);
      setIssues(response.data ?? []);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadIssues();
  }, []);

  const onSubmitIssue = async (formData) => {
    await createIssue(formData);
    await loadIssues();
  };

  const onFilterChange = async (event) => {
    const nextFilters = { ...filters, [event.target.name]: event.target.value };
    setFilters(nextFilters);
    await loadIssues(nextFilters);
  };

  return (
    <main className="container">
      <h1>Issue Intake and Smart Summary</h1>
      <IssueForm onSubmit={onSubmitIssue} />

      <section className="panel">
        <h2>Filter Issues</h2>
        <div className="grid">
          <select name="status" value={filters.status} onChange={onFilterChange}>
            <option value="">All statuses</option>
            <option value="open">Open</option>
            <option value="in_progress">In progress</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
          </select>
          <input
            name="category"
            value={filters.category}
            onChange={onFilterChange}
            placeholder="Category (e.g. payments)"
          />
          <select name="priority" value={filters.priority} onChange={onFilterChange}>
            <option value="">All priorities</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
          </select>
        </div>
      </section>

      {error && <p className="error">{error}</p>}
      {loading ? <p>Loading issues...</p> : <IssueList items={issues} />}
    </main>
  );
}
