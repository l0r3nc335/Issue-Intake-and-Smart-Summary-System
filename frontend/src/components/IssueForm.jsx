import { useState } from "react";

const initialState = {
  title: "",
  description: "",
  priority: "medium",
  category: "",
  status: "open",
};

export function IssueForm({ onSubmit }) {
  const [formData, setFormData] = useState(initialState);
  const [error, setError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  const updateValue = (event) => {
    setFormData((prev) => ({ ...prev, [event.target.name]: event.target.value }));
  };

  const submit = async (event) => {
    event.preventDefault();
    setSubmitting(true);
    setError("");

    try {
      await onSubmit(formData);
      setFormData(initialState);
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <section className="panel">
      <h2>Create Issue</h2>
      <form onSubmit={submit} className="form">
        <input
          name="title"
          value={formData.title}
          onChange={updateValue}
          placeholder="Issue title"
          required
          minLength={5}
        />
        <textarea
          name="description"
          value={formData.description}
          onChange={updateValue}
          placeholder="Describe the issue"
          required
          minLength={20}
          rows={5}
        />
        <div className="grid">
          <select name="priority" value={formData.priority} onChange={updateValue}>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
          </select>
          <input
            name="category"
            value={formData.category}
            onChange={updateValue}
            placeholder="Category"
            required
          />
          <select name="status" value={formData.status} onChange={updateValue}>
            <option value="open">Open</option>
            <option value="in_progress">In progress</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
          </select>
        </div>
        <button disabled={submitting}>{submitting ? "Submitting..." : "Submit Issue"}</button>
        {error && <p className="error">{error}</p>}
      </form>
    </section>
  );
}
