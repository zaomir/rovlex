# AGENTS.md

<!-- durable-agent-execution:v1 -->
## Durable execution / restart
Before long-running work or retry, read `.agent-execution/README.md` and the
existing task checkpoint. Use bounded atomic iterations; persist pending intent
before side effects and verified evidence/next action after each significant
unit. UI stream is not state. Reconcile pending operations and skip matching
verified work on restart. Run `python3 .agent-execution/check.py` before ship.
This execution adapter preserves all project, privacy, merge, sync and deploy
authorities below; it does not activate scheduling or outreach.
<!-- /durable-agent-execution:v1 -->

