<?php
// Temporary frontend prototype: no database or RabbitMQ connection yet.
$anime = [
    'id' => 16498,
    'title' => 'Attack on Titan',
    'genre' => 'Action · Drama · Fantasy',
    'synopsis' => 'Humanity lives behind enormous walls to protect itself from Titans. When the walls are breached, Eren and his friends begin a journey that changes their world.'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($anime['title']) ?> | Anime Test</title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; background: #0f1420; color: #f0f3f8; font-family: Arial, Helvetica, sans-serif; line-height: 1.5; }
    header { background: #171f30; padding: 18px max(20px, calc((100vw - 960px) / 2)); font-weight: 700; color: #90b8ff; }
    main { max-width: 960px; margin: 35px auto; padding: 0 20px; }
    .anime { display: flex; gap: 25px; align-items: flex-start; margin-bottom: 38px; }
    .poster { width: 190px; min-width: 190px; height: 265px; background: linear-gradient(145deg,#33496d,#161e2e); border-radius: 12px; display: flex; align-items: center; justify-content: center; text-align: center; padding: 20px; color: #c9d8f0; }
    h1 { margin: 0 0 8px; font-size: 34px; }
    h2 { margin: 0; font-size: 24px; }
    .muted { color: #aebad0; }
    .panel { background: #192235; border: 1px solid #334159; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
    .forum-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
    button { background: #5889ed; border: none; color: white; padding: 10px 15px; border-radius: 7px; font-weight: bold; cursor: pointer; }
    button:hover { background: #3e70d7; }
    .thread-title { background: transparent; color: #e7efff; padding: 0; text-align: left; font-size: 17px; }
    .thread-title:hover { background: transparent; text-decoration: underline; }
    .thread { border-bottom: 1px solid #344058; padding: 15px 0; }
    .thread:last-child { border-bottom: 0; }
    .thread-content { margin-top: 14px; padding: 15px; background: #101a2a; border-radius: 8px; }
    .reply { border-left: 3px solid #5889ed; padding-left: 12px; margin: 14px 0; }
    .reply p { margin: 4px 0; }
    label { display: block; font-weight: bold; margin: 12px 0 5px; }
    input, textarea { width: 100%; background: #0f1726; color: white; border: 1px solid #53627c; border-radius: 7px; padding: 11px; font: inherit; }
    textarea { min-height: 85px; resize: vertical; }
    form button { margin-top: 10px; }
    .hidden { display: none; }
    @media (max-width: 620px) { .anime { flex-direction: column; } .poster { height: 160px; width: 100%; } h1 { font-size: 28px; } .forum-heading { align-items: flex-start; flex-direction: column; } }
  </style>
</head>
<body>
  <header>AnimeHub — IT490 Prototype</header>
  <main>
    <section class="anime">
      <div class="poster">Anime Poster Placeholder</div>
      <div>
        <h1><?= htmlspecialchars($anime['title']) ?></h1>
        <p class="muted"><?= htmlspecialchars($anime['genre']) ?> · Anime ID: <?= (int)$anime['id'] ?></p>
        <h3>Synopsis</h3>
        <p><?= htmlspecialchars($anime['synopsis']) ?></p>
      </div>
    </section>
    <section class="panel" aria-label="Discussions">
      <div class="forum-heading">
        <div><h2>Discussions</h2><div class="muted">Threads about <?= htmlspecialchars($anime['title']) ?></div></div>
        <button type="button" id="toggle-new">+ New Thread</button>
      </div>
      <form id="new-thread-form" class="hidden">
        <label for="thread-title">Thread title</label>
        <input id="thread-title" maxlength="120" required placeholder="What do you want to discuss?">
        <label for="thread-body">Initial post</label>
        <textarea id="thread-body" required placeholder="Start the conversation..."></textarea>
        <button type="submit">Create Thread</button>
      </form>
      <p id="thread-status" class="muted" role="status">Loading discussion threads...</p>
      <div id="thread-list"></div>
    </section>
  </main>
  <script>
    // The anime identifier is supplied by the PHP anime page.
    const animeId = <?= (int) $anime['id'] ?>;
    const list = document.getElementById('thread-list');
    const status = document.getElementById('thread-status');
    let threads = [];

    function el(tag, className, text) {
      const node = document.createElement(tag);
      if (className) node.className = className;
      if (text !== undefined) node.textContent = text;
      return node;
    }

    function renderThreads() {
      list.replaceChildren();
      for (const thread of threads) {
        const item = el('div', 'thread');
        const title = el('button', 'thread-title', thread.title);
        title.type = 'button';
        const count = Number(thread.count) || 0;
        const summary = el('div', 'muted', `Started by ${thread.author} · ${count} replies · ${thread.created || 'Just now'}`);
        const detail = el('div', 'thread-content hidden');
        title.addEventListener('click', async () => {
          if (!detail.classList.contains('hidden')) {
            detail.classList.add('hidden');
            return;
          }
          detail.classList.remove('hidden');
          detail.replaceChildren(el('p', 'muted', 'Loading thread...'));
          try {
            const url = `discussion/get_thread.php?animeid=${encodeURIComponent(animeId)}&threadid=${encodeURIComponent(thread.id)}`;
            const response = await fetch(url, {cache: 'no-store'});
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            if (!Array.isArray(data) || !data.length) {
              detail.replaceChildren(el('p', 'muted', 'No saved post found for this thread.'));
              return;
            }
            // Peldan's get_topic_contents.php returns an array containing the
            // original post and a nested replies array.
            const post = data[0];
            detail.replaceChildren();
            detail.append(
              el('h3', '', post.title),
              el('p', 'muted', `Posted by ${post.author} · ${post.created}`),
              el('p', '', post.text),
              el('h4', '', `Replies (${(post.replies || []).length})`)
            );
            for (const reply of (post.replies || [])) {
              const replyBox = el('div', 'reply');
              replyBox.append(
                el('strong', '', reply.author),
                el('span', 'muted', ` · ${reply.created}`),
                el('p', '', reply.content)
              );
              detail.appendChild(replyBox);
            }
          } catch (error) {
            detail.replaceChildren(el('p', 'muted', 'Unable to load thread. Check get_thread.php.'));
            console.error('Thread details request failed:', error);
          }
        });
        item.append(title, summary, detail);
        list.appendChild(item);
      }
    }

    async function loadThreads() {
      status.textContent = 'Loading discussion threads...';
      try {
        const response = await fetch(`discussion/get_threads.php?animeid=${encodeURIComponent(animeId)}`, {cache: 'no-store'});
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        if (!Array.isArray(data)) throw new Error('Expected a JSON array');
        threads = data.filter(thread => Number(thread.category) === animeId);
        renderThreads();
        status.textContent = threads.length ? '' : 'No threads for this anime yet.';
      } catch (error) {
        status.textContent = 'Unable to load threads. Check discussion/get_threads.php and the browser console.';
        console.error('Thread request failed:', error);
      }
    }

    // Frontend-only demonstration: new threads are not saved to the database yet.
    document.getElementById('toggle-new').addEventListener('click', () => {
      document.getElementById('new-thread-form').classList.toggle('hidden');
    });
    document.getElementById('new-thread-form').addEventListener('submit', event => {
      event.preventDefault();
      const title = document.getElementById('thread-title').value.trim();
      const body = document.getElementById('thread-body').value.trim();
      if (!title || !body) return;
      threads.unshift({category: animeId, id: Date.now(), title, author: 'TestUser (unsaved)', created: 'Just now', count: 0});
      renderThreads();
      status.textContent = 'Demo thread added locally. It will disappear when you refresh.';
      event.target.reset();
      event.target.classList.add('hidden');
    });

    loadThreads();
  </script>
</body>
</html>
