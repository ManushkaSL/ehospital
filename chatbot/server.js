// server.js (CommonJS)
const express = require("express");
const cors = require("cors");
const dotenv = require("dotenv");
const fetch = require("node-fetch");

dotenv.config();

const app = express();
const PORT = 3000;

app.use(cors());
app.use(express.json());
app.use(express.static(__dirname));

app.post("/api/chat", async (req, res) => {
  const { message } = req.body;

  if (!message) return res.json({ reply: "⚠️ Please enter a message." });

  try {
    // ✅ CORRECT: Use gemini-2.5-flash (stable, latest model)
    const response = await fetch(
      `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=${process.env.GEMINI_API_KEY}`,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          contents: [{
            parts: [{
              text: message
            }]
          }],
          generationConfig: {
            temperature: 0.7,
            maxOutputTokens: 2000
          }
        })
      }
    );

    const data = await response.json();
    console.log("Gemini response:", JSON.stringify(data, null, 2));

    // Check for errors
    if (data.error) {
      console.error("Gemini API error:", data.error);
      return res.json({ reply: `⚠️ API Error: ${data.error.message}` });
    }

    // Extract the response text
    const reply = data?.candidates?.[0]?.content?.parts?.[0]?.text || "⚠️ Sorry, I couldn't generate a response.";
    res.json({ reply });
  } catch (err) {
    console.error("Error fetching Gemini:", err);
    res.json({ reply: "⚠️ Error contacting AI service." });
  }
});

app.listen(PORT, () => {
  console.log(`🚀 Server running at http://localhost:${PORT}`);
});