export function IssueList({ items }) {
  if (!items.length) {
    return <p>No issues found for this filter.</p>;
  }

  return (
    <section className="panel">
      <h2>Issues</h2>
      <ul className="list">
        {items.map((issue) => (
          <li key={issue.id}>
            <header>
              <strong>{issue.title}</strong>
              <span className={`badge ${issue.priority}`}>{issue.priority}</span>
              {issue.is_escalated ? <span className="badge escalated">Escalated</span> : null}
            </header>
            <p>{issue.summary}</p>
            <p>
              <b>Suggested next action:</b> {issue.suggested_next_action}
            </p>
            <small>
              Status: {issue.status} | Category: {issue.category}
            </small>
          </li>
        ))}
      </ul>
    </section>
  );
}
