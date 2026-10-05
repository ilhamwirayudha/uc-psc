import json

transcript_path = r'C:\Users\Ilham Fadhilah\.gemini\antigravity-ide\brain\6dcb8d7a-515a-4c46-9908-4cc38406c6c5\.system_generated\logs\transcript.jsonl'
with open(transcript_path, 'r', encoding='utf-8') as f:
    for line in f:
        data = json.loads(line)
        if data.get('type') == 'USER_INPUT' and data.get('step_index', 0) < 287:
            content = data.get('content', '')
            if isinstance(content, list):
                text = ' '.join([c.get('text', '') for c in content if isinstance(c, dict)])
            else:
                text = str(content)
            idx = data.get('step_index')
            print(f"Step {idx}: {text[:250]}\n")
