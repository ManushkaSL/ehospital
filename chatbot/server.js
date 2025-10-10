// server.js (CommonJS)
const express = require("express");
const cors = require("cors");
const dotenv = require("dotenv");
const fetch = require("node-fetch");
const mysql = require("mysql2/promise");

dotenv.config();

const app = express();
const PORT = 3000;

app.use(cors());
app.use(express.json());
app.use(express.static(__dirname));

// --- MySQL connection pool (reads config from env or defaults to project settings) ---
const DB_HOST = process.env.DB_HOST || 'localhost';
const DB_USER = process.env.DB_USER || 'root';
const DB_PASS = process.env.DB_PASS || '';
const DB_NAME = process.env.DB_NAME || 'ehospital';

let pool;
(async () => {
  try {
    pool = mysql.createPool({
      host: DB_HOST,
      user: DB_USER,
      password: DB_PASS,
      database: DB_NAME,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0
    });
    console.log('✅ MySQL pool created');
  } catch (err) {
    console.error('❌ Error creating MySQL pool:', err);
  }
})();

// Rate limiting variables
let requestCount = 0;
let lastReset = Date.now();

// System prompt to define the bot's scope
const SYSTEM_PROMPT = `You are a helpful medical assistant chatbot for an eHospital website. Your role is to:

1. **Answer medical-related questions**: Provide general health information, symptoms guidance, and wellness advice. Always remind users to consult with a healthcare professional for serious concerns.

2. **Guide users through the hospital website**: Help with:
   - How to book appointments
   - Finding doctors and departments
   - Understanding hospital services
   - Navigating the website features
   - Patient registration process
   - Viewing medical records
   - Contact information

**Important rules:**
- Only respond to medical questions and website guidance
- For non-medical or off-topic questions, politely redirect users back to medical topics or website help
- Always include a medical disclaimer when giving health advice
- Be professional, empathetic, and helpful
- Keep responses concise and clear
- If unsure, recommend contacting the hospital directly

If someone asks about topics unrelated to healthcare or the website (like weather, sports, general knowledge, etc.), politely say: "I'm specialized in medical assistance and hospital website guidance. Is there anything health-related or about our hospital services I can help you with?"`;

app.post("/api/chat", async (req, res) => {
  const { message, user } = req.body || {};
  // user: { email, usertype }

  if (!message) return res.json({ reply: "⚠️ Please enter a message." });

  // Reset counter every minute
  if (Date.now() - lastReset > 60000) {
    requestCount = 0;
    lastReset = Date.now();
  }

  // Check rate limit (max 10 requests per minute to be safe)
  if (requestCount >= 10) {
    return res.json({ reply: "⚠️ Too many requests. Please wait a moment." });
  }

  requestCount++;

  try {
    // Basic intent detection (DB-backed intents): doctor search, my appointments, my profile, drug lookup
    const msgLower = (message || '').toLowerCase();

    // 1) If the user asks about their appointments and they're logged in, fetch them
    if (pool && user && user.email && (msgLower.includes('my appointment') || msgLower.includes('my appointments') || msgLower.includes('upcoming appointment') || msgLower.includes('my booking') || msgLower.includes('bookings') )) {
      try {
        // Find patient id by email
        const [pRows] = await pool.execute('SELECT pid, pname, pemail FROM patient WHERE pemail = ? LIMIT 1', [user.email]);
        if (pRows && pRows.length) {
          const pid = pRows[0].pid;
          const [apps] = await pool.execute(`
            SELECT a.appoid, a.appodate, s.title, s.scheduledate, s.scheduletime, d.docname
            FROM appointment a
            LEFT JOIN schedule s ON a.scheduleid = s.scheduleid
            LEFT JOIN doctor d ON s.docid = d.docid
            WHERE a.pid = ?
            ORDER BY a.appodate DESC
            LIMIT 10
          `, [pid]);

          if (apps && apps.length) {
            const parts = apps.map(a => `• ${a.title || 'Session'} with ${a.docname || 'Doctor'} on ${a.appodate || a.scheduledate}`);
            return res.json({ reply: `Here are your recent appointments:\n${parts.join('\n')}` });
          } else {
            return res.json({ reply: "You don't have any recorded appointments." });
          }
        } else {
          return res.json({ reply: "I couldn't find your patient record. Please make sure you're logged in with the email on your patient profile." });
        }
      } catch (err) {
        console.error('DB error (appointments):', err);
        // fall through to LLM
      }
    }

    // 2) Profile lookup: "my profile" or "who am I"
    if (pool && user && user.email && (msgLower.includes('my profile') || msgLower.includes('who am i') || msgLower.includes('my details') )) {
      try {
        const [pRows] = await pool.execute('SELECT pid, pemail, pname, paddress, pdob, ptel FROM patient WHERE pemail = ? LIMIT 1', [user.email]);
        if (pRows && pRows.length) {
          const p = pRows[0];
          const reply = `Profile:\nName: ${p.pname}\nEmail: ${p.pemail}\nPhone: ${p.ptel || 'N/A'}\nAddress: ${p.paddress || 'N/A'}\nDOB: ${p.pdob || 'N/A'}`;
          return res.json({ reply });
        } else {
          return res.json({ reply: "I couldn't find your profile in our patient records." });
        }
      } catch (err) {
        console.error('DB error (profile):', err);
      }
    }

    // 3) Drug lookup: "drug" or "medicine" keywords
    if (pool && (msgLower.includes('drug') || msgLower.includes('medicine') || msgLower.includes('prescription') || msgLower.includes('pill') ) ) {
      try {
        // Extract short term after 'drug' or 'medicine' if possible
        const termMatch = message.match(/(?:drug|medicine|medication|pill)[:\s-]*([a-zA-Z0-9\s]+)/i);
        const term = termMatch ? termMatch[1].trim() : message.replace(/[^a-zA-Z0-9\s]/g, '').trim();
        const like = `%${term}%`;
        const [drugs] = await pool.execute('SELECT drug_name, drug_type, manufacturer, dosage_form, strength, quantity, price, expiry_date FROM drugs WHERE drug_name LIKE ? LIMIT 10', [like]);
        if (drugs && drugs.length) {
          const parts = drugs.map(d => `• ${d.drug_name} (${d.dosage_form || 'N/A'} ${d.strength || ''}) — ${d.quantity || 0} in stock — Price: ${d.price ? 'Rs ' + d.price : 'N/A'}`);
          return res.json({ reply: `Matching drugs:\n${parts.join('\n')}` });
        }
      } catch (err) {
        console.error('DB error (drugs):', err);
      }
    }

    // 4) Doctor search (existing logic) — try to detect doctor-related queries
    if (pool && (msgLower.includes('find doctor') || msgLower.includes('search doctor') || msgLower.includes('find doctors') || msgLower.includes('search doctors') || msgLower.includes('doctor') || msgLower.includes('specialist') || msgLower.includes('speciality') || msgLower.includes('specialty') )) {
      try {
        // Use the user's message as a broad search term for doctor name or specialty
        const searchTerm = message.replace(/[^a-zA-Z0-9\s]/g, '').trim();
        const like = `%${searchTerm || ''}%`;

        // Query doctors and their specialty name
        const q = `
          SELECT d.docid, d.docname, d.docemail, d.doctel, s.sname AS specialty
          FROM doctor d
          LEFT JOIN specialties s ON d.specialties = s.id
          WHERE d.docname LIKE ? OR s.sname LIKE ?
          LIMIT 10
        `;

  const [docs] = await pool.execute(q, [like, like]);

        if (docs && docs.length) {
          // For each doctor, fetch up to 5 upcoming schedules
          const parts = [];
          for (const d of docs) {
            const schedQ = `SELECT title, scheduledate, scheduletime, nop FROM schedule WHERE docid = ? AND scheduledate >= CURDATE() ORDER BY scheduledate, scheduletime LIMIT 5`;
            const [schedules] = await pool.execute(schedQ, [String(d.docid)]);
            const schedText = (schedules && schedules.length)
              ? schedules.map(sch => `${sch.scheduledate} ${sch.scheduletime.replace(/:\d+$/, '')} — ${sch.title || 'Session'}`).join('\n    ')
              : 'No upcoming sessions.';

            parts.push(`• ${d.docname} (${d.specialty || 'General'})\n    Contact: ${d.docemail || 'N/A'}${d.doctel ? ' — ' + d.doctel : ''}\n    Upcoming: ${schedText}`);
          }

          const replyText = `Here are matching doctors I found:\n${parts.join('\n\n')}`;
          return res.json({ reply: replyText });
        } else {
          console.log('No DB matches for doctor search, falling back to LLM');
        }
      } catch (dberr) {
        console.error('DB search error:', dberr);
        // fall through to LLM response
      }
    }

    const response = await fetch(
      `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=${process.env.GEMINI_API_KEY}`,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          contents: [
            {
              role: "user",
              parts: [{ text: SYSTEM_PROMPT }]
            },
            {
              role: "model",
              parts: [{ text: "Understood! I'm your eHospital medical assistant. I'll help with medical questions and guide you through the hospital website. I'll politely redirect any off-topic questions. How can I assist you today?" }]
            },
            {
              role: "user",
              parts: [{ text: message }]
            }
          ],
          generationConfig: {
            temperature: 0.7,
            maxOutputTokens: 500,
            topP: 0.8,
            topK: 40
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