<?php if (!defined('CHATBOT_INCLUDED')) {
  define('CHATBOT_INCLUDED', true);
?>
<!-- Google Material Symbols -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
<?php
// Try to inline CSS from the widget directory so includes from subfolders (eg. patient/) still load styles.
$cssPath = __DIR__ . DIRECTORY_SEPARATOR . 'style.css';
if (is_readable($cssPath)) {
  echo "<style>\n" . file_get_contents($cssPath) . "\n</style>\n";
} else {
  // fallback: use relative link (best-effort)
  echo '<link rel="stylesheet" href="chatbot/style.css">\n';
}
?>

<!-- Floating button to open chatbot -->
<!-- Floating button to open chatbot (inline style added to avoid being hidden by page CSS) -->
<button id="openChatBot" class="chatBotToggle material-symbols-rounded" aria-label="Open Chatbot" 
        style="position:fixed;bottom:30px;right:30px;z-index:99999;display:block;">
  smart_toy
</button>

<!-- Chatbot popup -->
<div class="chatbotPopup" id="chatPopup">
  <!-- Chatbot header -->
  <div class="chatHeader">
    <div class="headerInfo">
      <div class="logoWrapper">
        <div class="logo" style="display: flex; align-items: center; justify-content: center; font-size: 24px;">🤖</div>
        <div class="statusIndicator"></div>
      </div>
      <div class="headerText">
        <h2 class="logoText">AI Assistant</h2>
        <span class="statusText">Online</span>
      </div>
    </div>
    <button id="closeChatBot" class="material-symbols-rounded closeBtn" aria-label="Close Chatbot">
      expand_more
    </button>
  </div>

  <!-- Chatbot body -->
  <div class="chatBody" id="chatBody">
    <div class="messageBotMessage">
      <div class="botAvatar" style="display: flex; align-items: center; justify-content: center; font-size: 18px;">🤖</div>
      <div class="messageText">
        <div class="messageContent">
          🤖 Hello! I'm your eHospital medical assistant. 👋<br><br>
          I can help you with:<br>
          • Medical questions & health advice<br>
          • Booking appointments<br>
          • Finding doctors & departments<br>
          • Navigating our website<br><br>
          How can I assist you today?
        </div>
      </div>
    </div>
  </div>

  <!-- Typing indicator -->
  <div class="typingIndicator" id="typingIndicator">
    <div class="botAvatar" style="display: flex; align-items: center; justify-content: center; font-size: 18px;">🤖</div>
    <div class="typingDots">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>

  <!-- Chatbot footer -->
  <div class="chatFooter">
    <!-- Use a div instead of a nested form to avoid submitting any outer page form (prevents navigation to other pages) -->
    <div class="chatForm" id="chatForm" role="form">
      <textarea 
        placeholder="Type your message..." 
        class="messageInput" 
        id="messageInput"
        rows="1"
        maxlength="500"
      ></textarea>
      <div class="chatControls">
        <!-- Removed emoji and attachment buttons -->
        <!-- Make send button a regular button and give it an ID so JS can handle it without submitting forms -->
        <button type="button" id="sendBtn" class="sendBtn material-symbols-rounded" title="Send message" aria-label="Send message">
          send
        </button>
      </div>
    </div>
  </div>

<?php
// Expose minimal session info to the widget so the backend can provide personalized replies when available.
// This is intentionally tiny (email and usertype) and does not expose passwords or sensitive tokens.
if (session_status() === PHP_SESSION_NONE) {
  @session_start();
}
$chatEmail = isset($_SESSION['user']) ? addslashes($_SESSION['user']) : '';
$chatUtype = isset($_SESSION['usertype']) ? addslashes($_SESSION['usertype']) : '';
echo "<script>\nwindow.chatUser = { email: '" . $chatEmail . "', usertype: '" . $chatUtype . "' };\n</script>\n";

// Inline script the same way to avoid relative path issues when the widget is included from subfolders
$jsPath = __DIR__ . DIRECTORY_SEPARATOR . 'script.js';
if (is_readable($jsPath)) {
  echo "<script>\n" . file_get_contents($jsPath) . "\n</script>\n";
} else {
  echo '<script src="chatbot/script.js"></script>\n';
}
?>
<style>
  /* Ensure widget floats above other page elements in case page CSS sets high z-indexes */
  .chatBotToggle, .chatbotPopup { z-index: 99999 !important; }
  .chatBotToggle { display: block !important; }
</style>
<script>
// Fallback: if the page's CSS hides the button or it isn't present, create a simple fallback button
(function(){
  try {
    var popup = document.getElementById('chatPopup');
    var btn = document.getElementById('openChatBot');
    // Helper to check if element is effectively visible
    function isVisible(el){
      if(!el) return false;
      var style = window.getComputedStyle(el);
      return style && style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
    }

    if(!btn || !isVisible(btn)){
      // create fallback button
      btn = document.createElement('button');
      btn.id = 'openChatBot';
      btn.className = 'chatBotToggle material-symbols-rounded';
      btn.textContent = 'Chat';
      Object.assign(btn.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        zIndex: 100000,
        padding: '10px 14px',
        borderRadius: '8px',
        background: '#667eea',
        color: '#fff',
        border: 'none',
        fontSize: '14px',
        cursor: 'pointer'
      });
      document.body.appendChild(btn);
    }

    if(popup){
      btn.addEventListener('click', function(e){
        e.preventDefault();
        popup.style.display = 'flex';
        btn.style.display = 'none';
        setTimeout(function(){ popup.style.opacity = '1'; }, 10);
      });

      var closeBtn = document.getElementById('closeChatBot');
      if(closeBtn){
        closeBtn.addEventListener('click', function(e){
          e.preventDefault();
          popup.style.opacity = '0';
          setTimeout(function(){ popup.style.display = 'none'; btn.style.display = 'block'; }, 200);
        });
      }
    }
  } catch (err) {
    console.error('Chatbot fallback error:', err);
  }
})();
</script>
<?php } ?>