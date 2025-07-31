<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "dobu_db", 3307);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['topic-title']);
    $content = trim($_POST['topic-content']);
    $user_id = $_SESSION['user_id'];

    if ($title && $content) {
        $stmt = $conn->prepare("INSERT INTO forum_topics (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $content);
        $stmt->execute();
        $stmt->close();
    }
}

$result = $conn->query("
    SELECT ft.*, u.name AS username 
    FROM forum_topics ft
    JOIN users u ON ft.user_id = u.id
    ORDER BY ft.created_at DESC
");
$topics = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>DoBu Martial Arts - Forum</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Rajdhani:wght@300;400;500&display=swap');

h1, h2, h3, h4, h5, h6,
.hero-content h2,
.section-title,
.intro-text h2,
.why-card h4 {
  font-family: 'Bebas Neue', sans-serif;
  letter-spacing: 1px;
  text-transform: uppercase;
}


  /* Reset */
  * {
    margin: 0; padding: 0; box-sizing: border-box;
  }
  body {
    font-family: 'Rajdhani', sans-serif;
    background: #fdfdfd;
    color: #222;
    line-height: 1.5;
  }
  a {
    text-decoration: none;
    color: inherit;
  }
  ul {
    list-style: none;
  }

  /* Navbar */
  nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    padding: 1rem 3rem;
    border-bottom: 3px solid #ff6600;
    position: sticky;
    top: 0;
    z-index: 1000;
  }
  nav .logo {
    display: flex;
    align-items: center;
  }
  nav .logo img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin-right: 12px;
    object-fit: cover;
    border: 3px solid #ff6600;
  }
  nav .logo h1 {
    font-weight: 700;
    font-size: 1.5rem;
    color: #111;
    letter-spacing: 1.5px;
  }
  nav ul {
    display: flex;
    gap: 28px;
    align-items: center;
  }
  nav ul li {
    font-weight: 600;
    font-size: 1rem;
    position: relative;
  }
  nav ul li a {
    color: #111;
    padding: 6px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: color 0.3s ease;
  }
  nav ul li a:hover {
    color: #ff6600;
  }
  nav ul li.login a {
    color: #111;
    font-weight: 700;
    font-size: 1.1rem;
  }
  /* Only icon for login */
  nav ul li.login a span {
    display: none;
  }
  nav ul li.login a:hover {
    color: #ff6600;
  }
  .sr-only {
  position: absolute;
  width: 1px; height: 1px;
  padding: 0; margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* Page header */
  header {
    padding: 2rem;
    background: #f5f5f5;
    text-align: center;
    border-bottom: 3px solid #ff6600;
  }
  header h1 {
    margin: 0;
    font-size: 2.5rem;
    color: #ff6600;
  }
  header p {
    margin: 0.5rem 0 0;
    font-size: 1.1rem;
    color: #444;
  }

  /* Forum container */
  main {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
  }

  /* Forum topics list */
  .forum-topics {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 2rem;
  }
  .forum-topics thead {
    background: #ff6600;
    color: white;
  }
  .forum-topics th, .forum-topics td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
  .forum-topics tbody tr:hover {
    background: #ffe6cc;
  }
  .forum-topics td.title {
    font-weight: 600;
    font-size: 1.1rem;
  }
  .forum-topics td.posts,
  .forum-topics td.last-post {
    width: 130px;
    color: #666;
  }

  /* New topic form */
  .new-topic-form {
    background: #f9f9f9;
    border: 1px solid #ddd;
    padding: 1.5rem;
    border-radius: 6px;
    box-shadow: 0 0 10px #eee;
  }
  .new-topic-form h2 {
    margin-top: 0;
    color: #ff6600;
  }
  .new-topic-form label {
    display: block;
    margin: 1rem 0 0.3rem;
    font-weight: 600;
  }
  .new-topic-form input[type="text"],
  .new-topic-form textarea {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
    font-family: inherit;
    resize: vertical;
  }
  .new-topic-form button {
    margin-top: 1.2rem;
    background: #ff6600;
    color: white;
    border: none;
    padding: 0.8rem 1.5rem;
    font-size: 1rem;
    cursor: pointer;
    border-radius: 4px;
    transition: background 0.3s ease;
  }
  .new-topic-form button:hover,
  .new-topic-form button:focus {
    background: #e65500;
    outline: none;
  }
  
      /* Footer */
footer {
  background-color: #222222;
  color: #eee;
  padding: 2rem 1rem;
  font-family: 'Rajdhani', sans-serif;
  border-top: 3px solid #ff6600;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 1rem;
}

.footer-logo {
  text-align: left;
}

.footer-logo h4 {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 1.8rem;
  color: #ff6600;
  margin: 0;
}

.footer-logo p {
  font-size: 1rem;
  margin: 0;
  color: #ccc;
}

.social-icons {
  display: flex;
  gap: 1.2rem;
}

.social-icons a {
  font-size: 1.6rem;
  color: #eee;
  transition: color 0.3s ease, transform 0.2s ease;
}

.social-icons a:hover,
.social-icons a:focus {
  color: #ff6600;
  transform: scale(1.1);
}

.footer-bottom {
  margin-top: 1.5rem;
  text-align: center;
  font-size: 0.9rem;
  color: #ccc;
  border-top: 1px solid #333;
  padding-top: 1rem;
}

/* Responsive */
@media (max-width: 600px) {
  .footer-content {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  .footer-logo {
    text-align: center;
  }
}
  </style>
</head>
<body>
   <nav>
  <div class="logo" aria-label="DoBu Martial Arts logo">
    <img src="images/logo.jpg" alt="DoBu Martial Arts Logo" />
    <h1>DoBu Martial Arts</h1>
  </div>
  <ul>
    <li><a href="index.html" aria-current="page">Home</a></li>
    <li><a href="about.html">About</a></li>
    <li><a href="classes.html">Classes</a></li>
    <li><a href="schedule.html">Schedule</a></li>
    <li><a href="membership.html">Membership</a></li>
    <li><a href="forum.php" aria-current="page">Forum</a></li>
    <li><a href="contact.html">Contact</a></li>
    <li class="login" aria-label="Login">
  <a href="login.html" title="Login">
    <i class="fas fa-user" aria-hidden="true"></i>
    <span class="sr-only">Login</span>
  </a>
  <li><a href="logout.php">Logout</a></li>
</li>

  </ul>
</nav>

  <header>
    <h1>Community Forum</h1>
    <p>Welcome <?= htmlspecialchars($_SESSION['user_name']) ?> — discuss with fellow martial artists.</p>
  </header>

  <main>
    <table class="forum-topics">
      <thead>
        <tr>
          <th>Topic</th>
          <th>Author</th>
          <th>Posted</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($topics): ?>
          <?php foreach ($topics as $topic): ?>
            <tr>
              <td class="title">
  <?= htmlspecialchars($topic['title']) ?>
  <?php if ($_SESSION['user_id'] == $topic['user_id']): ?>
    <form method="POST" action="delete_topic.php" style="display:inline;" onsubmit="return confirm('Delete this topic?');">
      <input type="hidden" name="topic_id" value="<?= $topic['id'] ?>">
      <button type="submit" style="color:red; margin-left:10px; background:none; border:none; cursor:pointer;">🗑️</button>
    </form>
  <?php endif; ?>
</td>
              <td><?= htmlspecialchars($topic['username']) ?></td>
              <td><?= htmlspecialchars($topic['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="3">No topics yet. Be the first to post!</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <section class="new-topic-form">
      <h2>Create a New Topic</h2>
      <form method="POST" action="">
        <label for="topic-title">Topic Title</label>
        <input type="text" name="topic-title" id="topic-title" required maxlength="100" />

        <label for="topic-content">Content</label>
        <textarea name="topic-content" id="topic-content" rows="5" required></textarea>

        <button type="submit">Submit Topic</button>
      </form>
    </section>
  </main>

  <footer>
  <div class="footer-content">
    <div class="footer-logo">
      <h4>DoBu Martial Arts</h4>
      <p>Empowering strength and confidence.</p>
    </div>
    <div class="social-icons" aria-label="Social media links">
      <a href="#" aria-label="Facebook" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
      <a href="#" aria-label="Instagram" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
      <a href="#" aria-label="YouTube" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; 2025 DoBu Martial Arts. All rights reserved.</p>
  </div>
</footer>
</body>
</html>
