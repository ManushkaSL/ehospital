// -----------------------------
// Elements
// -----------------------------
const chatPopup = document.getElementById("chatPopup");
const openBtn = document.getElementById("openChatBot");
const closeBtn = document.getElementById("closeChatBot");
const chatForm = document.getElementById("chatForm");
const chatBody = document.getElementById("chatBody");
const messageInput = document.getElementById("messageInput");
const typingIndicator = document.getElementById("typingIndicator");

// -----------------------------
// Open chatbot with animation
// -----------------------------
openBtn.addEventListener("click", () => {
  chatPopup.style.display = "flex";
  openBtn.style.display = "none";
  setTimeout(() => chatPopup.style.opacity = "1", 10);
});

// -----------------------------
// Close chatbot with animation
// -----------------------------
closeBtn.addEventListener("click", () => {
  chatPopup.style.opacity = "0";
  setTimeout(() => {
    chatPopup.style.display = "none";
    openBtn.style.display = "block";
  }, 200);
});

// -----------------------------
// Auto-resize textarea
// -----------------------------
messageInput.addEventListener("input", () => {
  messageInput.style.height = "auto";
  messageInput.style.height = messageInput.scrollHeight + "px";
});

// -----------------------------
// Enter key handling
// -----------------------------
messageInput.addEventListener("keydown", (e) => {
  if (e.key === "Enter" && !e.shiftKey) {
    e.preventDefault();
    chatForm.dispatchEvent(new Event("submit"));
  }
});

// -----------------------------
// Scroll chat to bottom
// -----------------------------
function scrollToBottom() {
  chatBody.scrollTo({
    top: chatBody.scrollHeight,
    behavior: "smooth"
  });
}

// -----------------------------
// Typing indicator
// -----------------------------
function showTypingIndicator() {
  if (typingIndicator) typingIndicator.style.display = "flex";
  scrollToBottom();
}

function hideTypingIndicator() {
  if (typingIndicator) typingIndicator.style.display = "none";
}

// -----------------------------
// Create message element
// -----------------------------
function createMessageElement(text, isUser = false) {
  const messageDiv = document.createElement("div");
  messageDiv.classList.add(isUser ? "messageUserMessage" : "messageBotMessage");

  if (isUser) {
    messageDiv.innerHTML = `
      <div class="messageText">
        <div class="messageContent">${escapeHtml(text)}</div>
      </div>
    `;
  } else {
    messageDiv.innerHTML = `
      <img class="botAvatar" src="assets/botLogoDark.png" alt="Bot Avatar">
      <div class="messageText">
        <div class="messageContent">${formatBotMessage(text)}</div>
      </div>
    `;
  }

  return messageDiv;
}

// -----------------------------
// Format bot message (preserve formatting)
// -----------------------------
function formatBotMessage(text) {
  // Escape HTML first
  const escaped = escapeHtml(text);
  // Preserve line breaks
  return escaped.replace(/\n/g, '<br>');
}

// -----------------------------
// Escape HTML
// -----------------------------
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// -----------------------------
// AI Backend call
// -----------------------------
async function generateBotResponse(userMessage) {
  try {
    console.log("Sending message to backend:", userMessage);
    const res = await fetch("http://localhost:3000/api/chat", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message: userMessage })
    });
    console.log("Backend response status:", res.status);
    
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    
    const data = await res.json();
    console.log("Backend JSON:", data);
    return data.reply || "⚠️ Sorry, I couldn't generate a response.";
  } catch (err) {
    console.error("Error fetching backend:", err);
    return "⚠️ Error contacting AI service. Make sure the server is running.";
  }
}

// -----------------------------
// Handle form submit
// -----------------------------
chatForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const msg = messageInput.value.trim();
  if (!msg) return;

  // Add user message
  const userMsg = createMessageElement(msg, true);
  chatBody.appendChild(userMsg);
  scrollToBottom();

  // Clear input
  messageInput.value = "";
  messageInput.style.height = "auto";

  // Show typing indicator
  showTypingIndicator();

  // Generate bot response
  const botReply = await generateBotResponse(msg);

  hideTypingIndicator();
  const botMsg = createMessageElement(botReply, false);
  chatBody.appendChild(botMsg);
  scrollToBottom();
});

// -----------------------------
// Initialization
// -----------------------------
document.addEventListener("DOMContentLoaded", () => {
  console.log("Chatbot initialized and ready!");
});