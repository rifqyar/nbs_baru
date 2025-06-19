require('dotenv').config();

const express = require("express");
const app = express();
const cors = require("cors");
const PORT = 3001;

app.use(
    cors({
        origin: "*", // Ganti ini nanti jika perlu lebih aman
        methods: ["GET", "POST", "PUT", "DELETE"],
        allowedHeaders: ["Content-Type", "Authorization"],
    })
);
app.use(express.json());

// Import dan gunakan route
const prayaRoutes = require('./route/praya');
app.use('/praya', prayaRoutes);

// Contoh route lama (opsional)
app.get('/api/hello', (req, res) => {
    res.json({ message: 'Hello from Node.js API!' });
});

app.listen(PORT, () => {
    console.log(`Node.js server running on http://localhost:${PORT}`);
});
