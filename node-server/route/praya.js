const express = require("express");
const router = express.Router();

const { sendData } = require("../controllers/prayaIntegration");

// Route: GET /praya/send-data
router.post("/send-data", sendData);

module.exports = router;
